<?php
// Load the Database class
require_once 'models/Database.php';

echo "<h1>Environment Test</h1>";

// PHP Version
echo "<h2>PHP Version</h2>";
echo "Version: " . phpversion() . "<br>";

// Extensions
echo "<h2>Required Extensions</h2>";
$required = ['mysqli', 'pdo_mysql', 'json'];
$all_pass = true;

foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "$ext: <span style='color:green'>✓</span><br>";
    } else {
        echo "$ext: <span style='color:red'>✗</span><br>";
        $all_pass = false;
    }
}

// Database
echo "<h2>Database Connection</h2>";
try {
    $database = new Database();
    $conn = $database->getConnection();
    echo "Connection: <span style='color:green'>✓ Success</span><br>";
    
    // Test query
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables found:<br>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Test users table
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $user_count = $stmt->fetchColumn();
    echo "Users in database: $user_count<br>";
    
    // Test appointments table
    $stmt = $conn->query("SELECT COUNT(*) FROM appointments");
    $appt_count = $stmt->fetchColumn();
    echo "Appointments in database: $appt_count<br>";
    
} catch(PDOException $e) {
    echo "Connection: <span style='color:red'>✗ Failed</span><br>";
    echo "Error: " . $e->getMessage();
    $all_pass = false;
}

// Server Information
echo "<h2>Web Server</h2>";
echo "Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Summary
echo "<h2>Environment Test Results</h2>";
if ($all_pass) {
    echo "<p style='color:green; font-weight:bold;'>✓ All tests passed! Your environment is correctly configured.</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>✗ Some tests failed. Please check the errors above.</p>";
}
?>
