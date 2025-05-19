<?php
// Database configuration
$db_config = [
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'kholley_appointment_system',
    'charset'  => 'utf8mb4',
];
// Configuration for availability generation
$weeks = 4;                                // Number of weeks to generate slots for
$start_time = '09:00:00';                  // Default start time (24-hour format)
$end_time = '17:00:00';                    // Default end time (24-hour format)
$working_days = [1, 2, 3, 4, 5];           // Working days (1=Monday, 7=Sunday)
$base_interval = 15;                       // Base interval for alignment (all services must be multiples of this)
$clear_existing = true;                    // Whether to clear existing data before generating new slots
$max_slots_per_day = 10;                   // Maximum slots to create per day to prevent overloading

// Basic authentication for web access
if (!isset($argv) && (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== 'admin' || $_SERVER['PHP_AUTH_PW'] !== 'your_secure_password')) {
    header('WWW-Authenticate: Basic realm="Admin Access"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentication required';
    exit;
}

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start the process
echo "Starting mass availability generation...\n";
$start_process_time = microtime(true);
try {
    // If clearing existing data
    if ($clear_existing) {
        echo "Clearing existing schedules and availability...\n";
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        $pdo->exec("TRUNCATE TABLE recurring_schedules");
        $pdo->exec("TRUNCATE TABLE provider_availability");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    }
    
    // Get all active providers
    $stmt = $pdo->query("SELECT user_id, first_name, last_name FROM users WHERE role = 'provider' AND is_active = 1");
    $providers = $stmt->fetchAll();
    
    if (empty($providers)) {
        echo "No active providers found.\n";
        exit;
    }
    
    // Get all active services with their duration times
    $stmt = $pdo->query("
        SELECT service_id, name, duration 
        FROM services 
        WHERE is_active = 1 
        ORDER BY CASE 
            WHEN duration <= 30 THEN 1
            WHEN duration <= 45 THEN 2
            WHEN duration <= 60 THEN 3
            ELSE 4
        END, service_id
    ");
    $services = $stmt->fetchAll();
    
    if (empty($services)) {
        echo "No active services found.\n";
        exit;
    }
    
    echo "Found " . count($providers) . " providers and " . count($services) . " services.\n";
    $total_slots = 0;
    
    // Prepare statements for recurring schedules and availability
    $addRecurringStmt = $pdo->prepare("
        INSERT INTO recurring_schedules 
        (provider_id, day_of_week, start_time, end_time, is_active, schedule_type, effective_from, effective_until)
        VALUES
        (?, ?, ?, ?, 1, 'availability', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 MONTH))
    ");
    
    $addAvailabilityStmt = $pdo->prepare("
        INSERT INTO provider_availability
        (provider_id, availability_date, start_time, end_time, is_available, schedule_type, service_id, max_appointments)
        VALUES
        (?, ?, ?, ?, 1, 'availability', ?, 1)
    ");
    
    foreach ($providers as $provider) {
        $provider_id = $provider['user_id'];
        echo "Processing provider ID: {$provider_id} - {$provider['first_name']} {$provider['last_name']}...\n";
        
        // Add recurring schedules
        foreach ($working_days as $day) {
            $addRecurringStmt->execute([
                $provider_id,
                $day,
                $start_time,
                $end_time
            ]);
        }
        
        // Generate dates for the specified number of weeks
        $dates = [];
        $current_date = new DateTime();
        $end_date = clone $current_date;
        $end_date->modify("+{$weeks} weeks");
        
        while ($current_date <= $end_date) {
            $day_of_week = (int)$current_date->format('N'); // 1-7 (Mon-Sun)
            if (in_array($day_of_week, $working_days)) {
                $dates[] = $current_date->format('Y-m-d');
            }
            $current_date->modify('+1 day'); // FIXED: Changed from $current_time to $current_date
        }
        
        // Generate availability slots
        $provider_slots = 0;
        
        // Create a specific slot sequence for the provider
        // This mimics your real system's pattern of appointments
        $slot_sequence = [
            ['start' => '09:00:00', 'end' => '09:45:00', 'duration' => 45],  // 45 min
            ['start' => '09:45:00', 'end' => '11:15:00', 'duration' => 90],  // 90 min
            ['start' => '11:15:00', 'end' => '11:45:00', 'duration' => 30],  // 30 min
            ['start' => '11:45:00', 'end' => '12:30:00', 'duration' => 45],  // 45 min
            ['start' => '12:30:00', 'end' => '13:15:00', 'duration' => 45],  // 45 min
            ['start' => '13:15:00', 'end' => '14:15:00', 'duration' => 60],  // 60 min
            ['start' => '14:15:00', 'end' => '15:00:00', 'duration' => 45],  // 45 min
            ['start' => '15:00:00', 'end' => '16:30:00', 'duration' => 90],  // 90 min
            ['start' => '16:30:00', 'end' => '17:00:00', 'duration' => 30],  // 30 min
        ];
        
        // Create a mapping between duration and service_id
        $duration_to_service = [];
        foreach ($services as $service) {
            $duration = (int)$service['duration'];
            if (!isset($duration_to_service[$duration])) {
                $duration_to_service[$duration] = [];
            }
            $duration_to_service[$duration][] = $service['service_id'];
        }
        
        foreach ($dates as $date) {
            echo "Creating slots for {$date}...\n";
            $day_slots = 0;
            
            foreach ($slot_sequence as $slot) {
                // Find a service that matches this duration
                $duration = $slot['duration'];
                if (isset($duration_to_service[$duration]) && !empty($duration_to_service[$duration])) {
                    // Get a service ID for this duration - rotate through available services
                    $service_ids = $duration_to_service[$duration];
                    $service_id = $service_ids[array_rand($service_ids)];
                    
                    // Create the slot
                    $addAvailabilityStmt->execute([
                        $provider_id,
                        $date,
                        $slot['start'],
                        $slot['end'],
                        $service_id
                    ]);
                    
                    $day_slots++;
                    $provider_slots++;
                    $total_slots++;
                }
            }
            
            echo "Created {$day_slots} slots for {$date}\n";
        }
        
        echo "Created {$provider_slots} total slots for provider ID: {$provider_id}\n";
    }
    
    $duration = round(microtime(true) - $start_process_time, 2);
    echo "Process completed successfully in {$duration} seconds.\n";
    echo "Total slots created: {$total_slots}\n";
} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    exit(1);
}
