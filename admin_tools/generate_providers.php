<?php
// Database configuration
$db_config = [
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'kholley_appointment_system',
    'charset'  => 'utf8mb4',
];

// Provider generation configuration
$num_providers = 100;                       // Number of providers to generate
$default_password = 'Provider123@';       // Default password for all providers
$services_per_provider = [3, 8];          // Min and max number of services to assign to each provider

// Lists for random provider data generation
$first_names = [
    'Elizabeth', 'Richard', 'Sophia', 'Marcus', 'Olivia', 
    'Jamal', 'Alexandra', 'Raj', 'Zoe', 'Tyler',
    'Emma', 'James', 'Ava', 'William', 'Isabella', 
    'Michael', 'Sophia', 'Alexander', 'Charlotte', 'Benjamin',
    'Amelia', 'Ethan', 'Mia', 'Daniel', 'Harper',
    'Matthew', 'Evelyn', 'Joseph', 'Abigail', 'David',
    'Emily', 'John', 'Ella', 'Jacob', 'Madison',
    'Samuel', 'Scarlett', 'Logan', 'Victoria', 'Henry',
    'Grace', 'Owen', 'Chloe', 'Sebastian', 'Lily',
    'Gabriel', 'Hannah', 'Carter', 'Layla', 'Wyatt'
];

$last_names = [
    'Chen', 'Patel', 'Washington', 'Rodriguez', 'Kim', 
    'Wilson', 'Garcia', 'Sharma', 'Mitchell', 'Robinson',
    'Smith', 'Johnson', 'Williams', 'Jones', 'Brown',
    'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor',
    'Anderson', 'Thomas', 'Jackson', 'White', 'Harris',
    'Martin', 'Thompson', 'Young', 'Clark', 'Walker',
    'Hall', 'Allen', 'Wright', 'King', 'Scott',
    'Green', 'Baker', 'Adams', 'Nelson', 'Hill',
    'Nguyen', 'Lee', 'Gupta', 'Lopez', 'Perez',
    'Evans', 'Collins', 'Stewart', 'Morris', 'Reed'
];

$specializations = [
    'Dermatology', 'Cardiology', 'Family Medicine', 'Orthopedics', 'Neurology',
    'Pediatrics', 'Obstetrics', 'Psychiatry', 'Physical Therapy', 'Optometry',
    'Oncology', 'Endocrinology', 'Rheumatology', 'Urology', 'Gastroenterology',
    'Pulmonology', 'Nephrology', 'Hematology', 'Infectious Disease', 'Immunology'
];

$titles = ['MD', 'DO', 'MD, PhD', 'MD, FACC', 'DPT', 'OD', 'MD, FACOG', 'PhD', 'PA-C', 'NP'];

$bio_templates = [
    'Specializes in %s with over %d years of experience.',
    'Board-certified %s focused on preventive care and patient education.',
    'Experienced %s providing comprehensive care for patients of all ages.',
    'Specializes in %s with emphasis on minimally invasive techniques.',
    'Award-winning %s with expertise in complex cases and patient-centered care.',
    'Dedicated to providing compassionate %s care with a holistic approach.',
    'Fellowship-trained in %s with additional expertise in research and clinical trials.',
    'Offers advanced %s treatments with state-of-the-art technology.',
    'Provides evidence-based %s care with a focus on quality outcomes.',
    'Combines traditional and innovative approaches to %s for optimal results.'
];

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
    echo "Connected to database successfully.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start the process
echo "Starting provider generation and service assignment...\n";
$start_process_time = microtime(true);

try {
    // Hash the password once for all providers
    $password_hash = password_hash($default_password, PASSWORD_BCRYPT);
    
    // Get existing services
    $stmt = $pdo->query("SELECT service_id, name, duration FROM services WHERE is_active = 1");
    $services = $stmt->fetchAll();
    
    if (empty($services)) {
        die("No active services found in the database. Please add services first.\n");
    }
    
    // Prepare statements
    $addUserStmt = $pdo->prepare("
        INSERT INTO users 
        (email, password_hash, first_name, last_name, phone, role, is_active, is_verified)
        VALUES 
        (?, ?, ?, ?, ?, 'provider', 1, 1)
    ");
    
    $addProviderProfileStmt = $pdo->prepare("
        INSERT INTO provider_profiles 
        (provider_id, specialization, title, bio, accepting_new_patients, max_patients_per_day)
        VALUES 
        (?, ?, ?, ?, 1, 10)
    ");
    
    // Check if provider_services table exists and get its structure
    $stmt = $pdo->query("SHOW TABLES LIKE 'provider_services'");
    $hasProviderServicesTable = $stmt->rowCount() > 0;
    
    if ($hasProviderServicesTable) {
        // Create prepare statement for provider_services
        $addServiceStmt = $pdo->prepare("
            INSERT INTO provider_services 
            (provider_id, service_id, created_at)
            VALUES 
            (?, ?, NOW())
        ");
    }
    
    // Keep track of created providers for reporting
    $created_providers = [];
    
    // Create providers
    for ($i = 0; $i < $num_providers; $i++) {
        // Begin transaction
        $pdo->beginTransaction();
        
        try {
            // Generate random provider data
            $first_name = $first_names[array_rand($first_names)];
            $last_name = $last_names[array_rand($last_names)];
            $specialization = $specializations[array_rand($specializations)];
            $title = $titles[array_rand($titles)];
            
            // Generate bio
            $bio_template = $bio_templates[array_rand($bio_templates)];
            $years = rand(3, 25);
            $bio = sprintf($bio_template, $specialization, $years);
            
            // Generate phone
            $phone = '(555) ' . rand(100, 999) . '-' . rand(1000, 9999);
            
            // Generate email if not provided
            $email = strtolower(str_replace(' ', '.',
                $first_name . '.' . $last_name .
                random_int(100, 999) . '@example.com'));
            
            // Create user record
            $addUserStmt->execute([
                $email,
                $password_hash,
                $first_name,
                $last_name,
                $phone,
            ]);
            
            $provider_id = $pdo->lastInsertId();
            
            // Create provider profile
            $addProviderProfileStmt->execute([
                $provider_id,
                $specialization,
                $title,
                $bio
            ]);
            
            // Assign services if provider_services table exists
            if ($hasProviderServicesTable) {
                // Determine how many services to assign
                $num_services = random_int($services_per_provider[0], $services_per_provider[1]);
                $num_services = min($num_services, count($services));
                
                // Randomly select services
                $selected_service_keys = array_rand($services, $num_services);
                if (!is_array($selected_service_keys)) {
                    $selected_service_keys = [$selected_service_keys];
                }
                
                // Assign each selected service
                foreach ($selected_service_keys as $key) {
                    $service = $services[$key];
                    $addServiceStmt->execute([
                        $provider_id,
                        $service['service_id']
                    ]);
                }
                
                $service_names = array_map(function($key) use ($services) {
                    return $services[$key]['name'];
                }, $selected_service_keys);
                
                $created_providers[] = [
                    'id' => $provider_id,
                    'name' => $first_name . ' ' . $last_name,
                    'email' => $email,
                    'services' => $service_names
                ];
            } else {
                $created_providers[] = [
                    'id' => $provider_id,
                    'name' => $first_name . ' ' . $last_name,
                    'email' => $email,
                    'services' => ['Service assignment skipped - provider_services table not found']
                ];
            }
            
            // Commit transaction
            $pdo->commit();
            
        } catch (Exception $e) {
            // Rollback on error
            $pdo->rollBack();
            echo "Error creating provider #{$i}: " . $e->getMessage() . "\n";
        }
    }
    
    // Report results
    $duration = round(microtime(true) - $start_process_time, 2);
    echo "Process completed in {$duration} seconds.\n";
    echo "Created " . count($created_providers) . " providers with the password: $default_password\n\n";
    
    // Output details of created providers
    foreach ($created_providers as $idx => $provider) {
        echo "PROVIDER #" . ($idx + 1) . ":\n";
        echo "ID: " . $provider['id'] . "\n";
        echo "Name: " . $provider['name'] . "\n";
        echo "Email: " . $provider['email'] . "\n";
        echo "Services: " . implode(", ", $provider['services']) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    exit(1);
}
?>
