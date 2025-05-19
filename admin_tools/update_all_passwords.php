<?php
// Database configuration
$db_config = [
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'kholley_appointment_system',
    'charset'  => 'utf8mb4',
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
try {
    // Define passwords for each role
    $passwords = [
        'admin' => 'Admin123@',
        'provider' => 'Provider123@',
        'patient' => 'Patient123@'
    ];
    
    $total_updated = 0;
    
    foreach ($passwords as $role => $password) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Update passwords for this role
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password_hash = ?, 
                password_change_required = 0
            WHERE role = ?
        ");
        
        $stmt->execute([$password_hash, $role]);
        
        $affected_rows = $stmt->rowCount();
        $total_updated += $affected_rows;
        echo "Updated {$affected_rows} {$role} accounts with password: {$password}\n";
    }
    
    echo "Total accounts updated: {$total_updated}\n";
    
} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    exit(1);
}
?>
