<?php
// Load bootstrap
require_once 'bootstrap.php';

// Display configuration information
echo "<h1>Bootstrap Test</h1>";
echo "<pre>";

echo "Environment: " . get_environment() . "\n";
echo "Paths:\n";
echo "- APP_ROOT: " . APP_ROOT . "\n";
echo "- CONFIG_PATH: " . CONFIG_PATH . "\n";
echo "- CONTROLLER_PATH: " . CONTROLLER_PATH . "\n";
echo "- MODEL_PATH: " . MODEL_PATH . "\n";
echo "- VIEW_PATH: " . VIEW_PATH . "\n";
echo "- CORE_PATH: " . CORE_PATH . "\n";
echo "- ROUTES_PATH: " . ROUTES_PATH . "\n";
echo "- SQL_PATH: " . SQL_PATH . "\n";
echo "- TESTS_PATH: " . TESTS_PATH . "\n\n";

// Test database connection
echo "Database Connection Test:\n";
try {
    $db = get_db();
    echo "Connection successful!\n";
    
    $result = $db->query("SHOW TABLES");
    if ($result) {
        echo "Tables found: " . $result->num_rows . "\n";
        while ($row = $result->fetch_row()) {
            echo " - " . $row[0] . "\n";
        }
    } else {
        echo "Error querying tables: " . $db->error . "\n";
    }
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
}


// Test including a file
echo "\nTesting file inclusion:\n";
$test_env_path = TESTS_PATH . '/test_env.php';
echo "Looking for file at: " . $test_env_path . "\n";
if (file_exists($test_env_path)) {
    echo "File exists!\n";
    require_once $test_env_path;
    echo "Successfully included test_env.php\n";
} else {
    echo "Error: File not found: " . $test_env_path . "\n";
}

echo "</pre>";
