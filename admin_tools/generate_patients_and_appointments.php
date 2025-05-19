<?php
// Database configuration
$db_config = [
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'kholley_appointment_system',
    'charset'  => 'utf8mb4',
];

// Patient generation configuration
$num_patients = 100;                     // Number of patients to generate
$default_password = 'Patient123@';       // Default password for all patients
$appointments_per_patient = [6, 8];      // Min and max number of appointments per patient

// Lists for random patient data generation
$first_names = [
    'Jennifer', 'Robert', 'Maria', 'David', 'Sarah',
    'Carlos', 'Michelle', 'Kevin', 'Lisa', 'Andrew',
    'Jessica', 'Christopher', 'Amanda', 'Daniel', 'Ashley',
    'Brian', 'Stephanie', 'Jason', 'Nicole', 'Ryan',
    'Laura', 'Brandon', 'Melissa', 'Mark', 'Emily',
    'Steven', 'Rachel', 'Justin', 'Megan', 'Joshua',
    'Angela', 'Eric', 'Heather', 'Timothy', 'Rebecca',
    'Jeffrey', 'Christina', 'Jonathan', 'Katherine', 'Adam',
    'Amber', 'Charles', 'Kelly', 'Gregory', 'Lauren',
    'Patrick', 'Brittany', 'Thomas', 'Shannon', 'Jeremy'
];

$last_names = [
    'Thompson', 'Martinez', 'Johnson', 'Powell', 'Rivera',
    'Edwards', 'Turner', 'Peterson', 'Bailey', 'Cooper',
    'Diaz', 'Morgan', 'Phillips', 'Sullivan', 'Hernandez',
    'Rogers', 'Reed', 'Russell', 'Cook', 'Bell',
    'Murphy', 'Bailey', 'Richardson', 'Cox', 'Howard',
    'Ward', 'Torres', 'Peterson', 'Gray', 'Ramirez',
    'James', 'Watson', 'Brooks', 'Kelly', 'Sanders',
    'Price', 'Bennett', 'Wood', 'Barnes', 'Ross',
    'Henderson', 'Coleman', 'Jenkins', 'Perry', 'Powell',
    'Long', 'Patterson', 'Hughes', 'Flores', 'Washington'
];

// Medical conditions and history for random generation
$medical_conditions = [
    'Hypertension', 'Type 2 Diabetes', 'Asthma', 'Arthritis', 
    'Anxiety', 'Depression', 'Migraine', 'GERD', 
    'Allergies (seasonal)', 'Hypothyroidism', 'None', 
    'High Cholesterol', 'Sleep Apnea', 'Vitamin D Deficiency'
];

$medical_histories = [
    'Appendectomy (2015)', 'Tonsillectomy (childhood)', 'Knee surgery (2018)',
    'No significant surgical history', 'Childhood asthma', 'Fractured wrist (2016)',
    'Annual wellness exams', 'Family history of heart disease', 'No notable history',
    'Hospitalized for pneumonia (2019)', 'Previous back injury', 'Gallbladder removal (2017)'
];

// Insurance info formats
$insurance_info_templates = [
    '%s - Policy #%s - Group #%s',
    '%s Insurance - Member ID: %s',
    '%s Health Plan - Policy: %s',
    '%s - Contract #%s - Group #%s'
];

// Medical notes templates
$appointment_notes_templates = [
    'Patient reports %s. Follow-up in %d weeks.',
    'Consultation for %s. Treatment plan discussed.',
    'Assessment of %s. Referred to specialist for further evaluation.',
    'Regular check-up. Patient experiencing %s.',
    'Evaluation of %s. Lab work ordered. Results pending.',
    'Follow-up for %s. Showing improvement with current treatment.',
    'Initial consultation for %s. Treatment options discussed.',
    'Patient seeking advice regarding %s. Provided recommendations.',
    'Review of recent test results. Addressing concerns about %s.',
    'Preventive care visit. Screening for %s completed.'
];

// Possible symptoms or conditions
$symptoms = [
    'persistent headaches', 'lower back pain', 'joint stiffness', 'seasonal allergies',
    'mild hypertension', 'insomnia', 'anxiety symptoms', 'chronic fatigue',
    'digestive issues', 'respiratory concerns', 'skin rash', 'dizziness',
    'vision changes', 'ear infection', 'sinus congestion', 'muscle weakness',
    'weight management', 'nutritional deficiencies', 'depression symptoms', 'medication review'
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
echo "Starting patient generation and appointment booking...\n";
$start_process_time = microtime(true);

try {
    // Hash the password once for all patients
    $password_hash = password_hash($default_password, PASSWORD_BCRYPT);
    
    // Get providers and their services
    $stmt = $pdo->query("
        SELECT p.provider_id, u.first_name, u.last_name, ps.service_id, s.name AS service_name, s.duration
        FROM provider_profiles p
        JOIN users u ON p.provider_id = u.user_id
        JOIN provider_services ps ON p.provider_id = ps.provider_id
        JOIN services s ON ps.service_id = s.service_id
        WHERE u.is_active = 1
    ");
    
    $providers_with_services = $stmt->fetchAll();
    
    if (empty($providers_with_services)) {
        die("No active providers with services found in the database.\n");
    }
    
    // Group providers by service_id to make selection easier
    $services_by_provider = [];
    $provider_info = [];
    
    foreach ($providers_with_services as $row) {
        if (!isset($services_by_provider[$row['service_id']])) {
            $services_by_provider[$row['service_id']] = [];
        }
        $services_by_provider[$row['service_id']][] = $row['provider_id'];
        
        if (!isset($provider_info[$row['provider_id']])) {
            $provider_info[$row['provider_id']] = [
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'services' => []
            ];
        }
        $provider_info[$row['provider_id']]['services'][$row['service_id']] = [
            'name' => $row['service_name'],
            'duration' => $row['duration']
        ];
    }
    
    // Get all services
    $stmt = $pdo->query("SELECT service_id, name, duration FROM services WHERE is_active = 1");
    $services = $stmt->fetchAll();
    
    // Prepare statements
    $addUserStmt = $pdo->prepare("
        INSERT INTO users 
        (email, password_hash, first_name, last_name, phone, role, is_active, is_verified)
        VALUES 
        (?, ?, ?, ?, ?, 'patient', 1, 1)
    ");
    
    $addPatientProfileStmt = $pdo->prepare("
        INSERT INTO patient_profiles 
        (user_id, phone, date_of_birth, address, emergency_contact, 
         emergency_contact_phone, medical_conditions, medical_history, insurance_info, created_at)
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $addAppointmentStmt = $pdo->prepare("
        INSERT INTO appointments 
        (patient_id, provider_id, service_id, appointment_date, start_time, end_time, notes, status, created_at)
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())
    ");
    
    // Insurance providers for random generation
    $insurance_providers = ['Blue Cross', 'Aetna', 'UnitedHealthcare', 'Cigna', 'Humana', 'Kaiser', 'Medicare', 'Medicaid', 'TriCare', 'Health Net'];
    
    // Keep track of created patients for reporting
    $created_patients = [];
    
    // Create patients and book appointments
    for ($i = 0; $i < $num_patients; $i++) {
        // Begin transaction
        $pdo->beginTransaction();
        
        try {
            // Generate random patient data
            $first_name = $first_names[array_rand($first_names)];
            $last_name = $last_names[array_rand($last_names)];
            
            // Generate phone
            $phone = '(555) ' . rand(100, 999) . '-' . rand(1000, 9999);
            
            // Generate email
            $email = strtolower(str_replace(' ', '.', 
                $first_name . '.' . $last_name . 
                random_int(100, 999) . '@example.com'));
            
            // Generate date of birth (18-80 years old)
            $birth_year = date('Y') - rand(18, 80);
            $birth_month = rand(1, 12);
            $birth_day = rand(1, 28); // simplified to avoid month-end issues
            $dob = "$birth_year-$birth_month-$birth_day";
            
            // Generate address
            $address = rand(100, 9999) . ' ' . ['Main', 'Oak', 'Maple', 'Cedar', 'Pine', 'Elm', 'Washington', 'Park', 'Lake'][array_rand(['Main', 'Oak', 'Maple', 'Cedar', 'Pine', 'Elm', 'Washington', 'Park', 'Lake'])] . ' ' .
                       ['St', 'Ave', 'Blvd', 'Dr', 'Ln', 'Rd', 'Way', 'Court', 'Place'][array_rand(['St', 'Ave', 'Blvd', 'Dr', 'Ln', 'Rd', 'Way', 'Court', 'Place'])] . ', ' .
                       ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia'][array_rand(['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia'])] . ', ' .
                       ['NY', 'CA', 'IL', 'TX', 'AZ', 'PA'][array_rand(['NY', 'CA', 'IL', 'TX', 'AZ', 'PA'])] . ' ' .
                       rand(10000, 99999);
            
            // Generate emergency contact
            $emergency_contact = $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
            $emergency_contact_phone = '(555) ' . rand(100, 999) . '-' . rand(1000, 9999);
            
            // Generate medical information
            $patient_medical_conditions = $medical_conditions[array_rand($medical_conditions)];
            $patient_medical_history = $medical_histories[array_rand($medical_histories)];
            
            // Generate insurance info
            $insurance_provider = $insurance_providers[array_rand($insurance_providers)];
            $policy_number = strtoupper(substr(md5(rand()), 0, 10));
            $group_number = strtoupper(substr(md5(rand()), 0, 6));
            
            $insurance_template = $insurance_info_templates[array_rand($insurance_info_templates)];
            $insurance_info = sprintf($insurance_template, $insurance_provider, $policy_number, $group_number);
            
            // Create user record
            $addUserStmt->execute([
                $email,
                $password_hash,
                $first_name,
                $last_name,
                $phone,
            ]);
            
            $patient_id = $pdo->lastInsertId();
            
            // Create patient profile
            $addPatientProfileStmt->execute([
                $patient_id,
                $phone,
                $dob,
                $address,
                $emergency_contact,
                $emergency_contact_phone,
                $patient_medical_conditions,
                $patient_medical_history,
                $insurance_info
            ]);
            
            // Book 1-2 appointments for this patient
            $num_appointments = rand($appointments_per_patient[0], $appointments_per_patient[1]);
            $booked_appointments = [];
            
            for ($j = 0; $j < $num_appointments; $j++) {
                // Randomly select a service
                $service = $services[array_rand($services)];
                $service_id = $service['service_id'];
                $duration = $service['duration']; // in minutes
                
                // Find providers who offer this service
                if (isset($services_by_provider[$service_id]) && !empty($services_by_provider[$service_id])) {
                    $available_providers = $services_by_provider[$service_id];
                    $provider_id = $available_providers[array_rand($available_providers)];
                    
                    // Generate appointment date (between today and 3 months in future)
                    $days_in_future = rand(1, 90);
                    $appointment_date = date('Y-m-d', strtotime("+$days_in_future days"));
                    
                    // Generate appointment time (9 AM - 4 PM to ensure end time doesn't go beyond business hours)
                    $hour = rand(9, 16);
                    $minute = [0, 15, 30, 45][array_rand([0, 15, 30, 45])]; // 15-minute intervals
                    $start_time = sprintf("%02d:%02d:00", $hour, $minute);
                    
                                        // Calculate end time
                    $end_time_timestamp = strtotime("$appointment_date $start_time") + ($duration * 60);
                    $end_time = date("H:i:s", $end_time_timestamp);
                    
                    // Generate notes
                    $notes_template = $appointment_notes_templates[array_rand($appointment_notes_templates)];
                    $symptom = $symptoms[array_rand($symptoms)];
                    $follow_up_weeks = rand(2, 12);
                    $notes = sprintf($notes_template, $symptom, $follow_up_weeks);
                    
                    // Book the appointment
                    $addAppointmentStmt->execute([
                        $patient_id,
                        $provider_id,
                        $service_id,
                        $appointment_date,
                        $start_time,
                        $end_time,
                        $notes
                    ]);
                    
                    $appointment_id = $pdo->lastInsertId();
                    
                    $booked_appointments[] = [
                        'id' => $appointment_id,
                        'date' => $appointment_date,
                        'time' => $start_time,
                        'provider' => $provider_info[$provider_id]['name'],
                        'service' => $service['name']
                    ];
                }
            }
            
            $created_patients[] = [
                'id' => $patient_id,
                'name' => $first_name . ' ' . $last_name,
                'email' => $email,
                'appointments' => $booked_appointments
            ];
            
            // Commit transaction
            $pdo->commit();
            
        } catch (Exception $e) {
            // Rollback on error
            $pdo->rollBack();
            echo "Error creating patient #{$i}: " . $e->getMessage() . "\n";
        }
    }
    
    // Report results
    $duration = round(microtime(true) - $start_process_time, 2);
    echo "Process completed in {$duration} seconds.\n";
    echo "Created " . count($created_patients) . " patients with the password: $default_password\n\n";
    
    // Count total appointments
    $total_appointments = 0;
    foreach ($created_patients as $patient) {
        $total_appointments += count($patient['appointments']);
    }
    echo "Booked a total of {$total_appointments} confirmed appointments.\n\n";
    
    // Output details of created patients
    foreach ($created_patients as $idx => $patient) {
        echo "PATIENT #" . ($idx + 1) . ":\n";
        echo "ID: " . $patient['id'] . "\n";
        echo "Name: " . $patient['name'] . "\n";
        echo "Email: " . $patient['email'] . "\n";
        
        if (empty($patient['appointments'])) {
            echo "Appointments: None\n";
        } else {
            echo "Appointments (" . count($patient['appointments']) . "):\n";
            foreach ($patient['appointments'] as $appt_idx => $appointment) {
                echo "  " . ($appt_idx + 1) . ") " . 
                     "Date: " . $appointment['date'] . " at " . substr($appointment['time'], 0, 5) . ", " .
                     "Service: " . $appointment['service'] . ", " .
                     "Provider: " . $appointment['provider'] . "\n";
            }
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    exit(1);
}
?>
