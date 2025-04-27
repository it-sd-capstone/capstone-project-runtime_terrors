<?php
// Fix the path to bootstrap.php
require_once __DIR__ . '/../public_html/bootstrap.php';

// Now bootstrap.php has defined all constants and loaded get_db()
// Start your environment tests
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
    // Get database connection
    $conn = get_db();
    echo "Connection: <span style='color:green'>✓ Success</span><br>";
    
    // Test query
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    echo "Tables found:<br>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Test users table
    $result = $conn->query("SELECT COUNT(*) FROM users");
    $row = $result->fetch_row();
    $user_count = $row[0];
    echo "Users in database: $user_count<br>";
    
    // Test appointments table
    $result = $conn->query("SELECT COUNT(*) FROM appointments");
    $row = $result->fetch_row();
    $appt_count = $row[0];
    echo "Appointments in database: $appt_count<br>";
    
} catch(Exception $e) {
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
