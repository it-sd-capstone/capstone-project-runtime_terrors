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
    // Set the new password
    $new_password = 'Admin123@';
    
    // Hash the password
    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
    
    // Update all admin passwords
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password_hash = ?, 
            password_change_required = 0
        WHERE role = 'admin'
    ");
    
    $stmt->execute([$password_hash]);
    
    $affected_rows = $stmt->rowCount();
    echo "Successfully updated passwords for {$affected_rows} admin accounts.\n";
    echo "New password for all admins: {$new_password}\n";
    
} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    exit(1);
}
?>
