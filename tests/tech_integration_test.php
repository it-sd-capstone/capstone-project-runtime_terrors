<?php
/**
 * Simplified Technology Integration Test
 */

// Define base path if not already defined
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Check if the database config file exists
$database_file = APP_ROOT . '/config/database.php';
if (!file_exists($database_file)) {
    die("Database configuration file not found at: $database_file");
}

// Include database configuration
require_once $database_file;

// If $db_config is not defined, check for constants-based configuration
if (!isset($db_config)) {
    $db_config = [
        'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
        'dbname' => defined('DB_NAME') ? DB_NAME : 'kholley_appointment_system',
        'username' => defined('DB_USER') ? DB_USER : 'root',
        'password' => defined('DB_PASS') ? DB_PASS : '',
        'charset' => 'utf8mb4'
    ];
}

// Set up test results array
$test_results = [
    'database_connection' => ['status' => false, 'message' => ''],
    'database_schema' => ['status' => false, 'message' => ''],
    'fullcalendar_integration' => ['status' => false, 'message' => ''],
    'bootstrap_available' => ['status' => false, 'message' => ''],
    'data_retrieval' => ['status' => false, 'message' => '']
];

// Test 1: Database Connection
try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5
    ];
    
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
    $test_results['database_connection']['status'] = true;
    $test_results['database_connection']['message'] = "Successfully connected to database {$db_config['dbname']}";
} catch (PDOException $e) {
    $test_results['database_connection']['message'] = "Connection failed: " . $e->getMessage();
}

// Test 2: Database Schema
if ($test_results['database_connection']['status']) {
    try {
        $required_tables = ['appointments', 'provider_availability', 'services', 'users'];
        $existing_tables = [];
        
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $existing_tables[] = $row[0];
        }
        
        $missing_tables = array_diff($required_tables, $existing_tables);
        
        if (empty($missing_tables)) {
            $test_results['database_schema']['status'] = true;
            $test_results['database_schema']['message'] = "All required tables exist";
        } else {
            $test_results['database_schema']['message'] = "Missing tables: " . implode(', ', $missing_tables);
        }
    } catch (PDOException $e) {
        $test_results['database_schema']['message'] = "Schema test failed: " . $e->getMessage();
    }
} else {
    $test_results['database_schema']['message'] = "Skipped due to database connection failure";
}

// Test 3: FullCalendar.js Availability
$fullcalendar_paths = [
    APP_ROOT . '/public_html/js/fullcalendar.min.js',
    APP_ROOT . '/public_html/js/fullcalendar.js',
    APP_ROOT . '/public_html/js/fullcalendar/main.min.js',
    APP_ROOT . '/public_html/assets/js/fullcalendar.min.js',
    APP_ROOT . '/public_html/assets/js/fullcalendar/main.min.js'
];

$fullcalendar_found = false;
foreach ($fullcalendar_paths as $path) {
    if (file_exists($path)) {
        $fullcalendar_found = true;
        break;
    }
}

// If not found locally, check if CDN is referenced in any view files
if (!$fullcalendar_found) {
    $view_files = glob(APP_ROOT . '/views/**/*.php');
    foreach ($view_files as $view) {
        if (file_exists($view)) {
            $contents = file_get_contents($view);
            if ((strpos($contents, 'fullcalendar') !== false && 
                 strpos($contents, 'cdn') !== false) ||
                strpos($contents, 'fullcalendar@') !== false) {
                $fullcalendar_found = true;
                break;
            }
        }
    }
}

$test_results['fullcalendar_integration']['status'] = $fullcalendar_found;
$test_results['fullcalendar_integration']['message'] = $fullcalendar_found
    ? "FullCalendar.js is included in the project"
    : "FullCalendar.js not found";

// Test 4: Bootstrap Availability
$bootstrap_paths = [
    APP_ROOT . '/public_html/css/bootstrap.min.css',
    APP_ROOT . '/public_html/css/bootstrap.css',
    APP_ROOT . '/public_html/assets/css/bootstrap.min.css',
    APP_ROOT . '/public_html/assets/css/bootstrap.css'
];

$bootstrap_found = false;
foreach ($bootstrap_paths as $path) {
    if (file_exists($path)) {
        $bootstrap_found = true;
        break;
    }
}

// If not found locally, check if CDN is referenced in views
if (!$bootstrap_found) {
    $view_files = glob(APP_ROOT . '/views/**/*.php');
    foreach ($view_files as $view) {
        if (file_exists($view)) {
            $contents = file_get_contents($view);
            if ((strpos($contents, 'bootstrap') !== false && 
                 strpos($contents, 'cdn') !== false) ||
                strpos($contents, 'bootstrap@') !== false) {
                $bootstrap_found = true;
                break;
            }
        }
    }
}

$test_results['bootstrap_available']['status'] = $bootstrap_found;
$test_results['bootstrap_available']['message'] = $bootstrap_found
    ? "Bootstrap is included in the project"
    : "Bootstrap not found";

// Test 5: Data Retrieval
if ($test_results['database_connection']['status']) {
    try {
        // Test retrieving appointment data
        $stmt = $pdo->query("SELECT * FROM appointments LIMIT 5");
        $appointments = $stmt->fetchAll();
        
        // Updated to use provider_availability instead of availability
        $stmt = $pdo->query("SELECT * FROM provider_availability LIMIT 5");
        $availability = $stmt->fetchAll();
        
        $test_results['data_retrieval']['status'] = true;
        $test_results['data_retrieval']['message'] = "Successfully retrieved data from database";
        $test_results['data_retrieval']['appointments'] = $appointments;
        $test_results['data_retrieval']['availability'] = $availability;
    } catch (PDOException $e) {
        $test_results['data_retrieval']['message'] = "Data retrieval failed: " . $e->getMessage();
    }
} else {
    $test_results['data_retrieval']['message'] = "Skipped due to database connection failure";
}

// Calculate overall test result
$all_tests_passed = true;
foreach ($test_results as $test) {
    if (!$test['status']) {
        $all_tests_passed = false;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technology Integration Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .check-icon { color: #198754; }
        .x-icon { color: #dc3545; }
    </style>
</head>
<body class="container mt-4 mb-4">
    <h1>Technology Integration Test Results</h1>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2 class="h4 mb-0">Test Summary</h2>
        </div>
        <div class="card-body">
            <div class="alert <?= $all_tests_passed ? 'alert-success' : 'alert-danger' ?>">
                <strong>Overall Result:</strong> 
                <?php if ($all_tests_passed): ?>
                    <span class="check-icon">✓</span> PASS
                <?php else: ?>
                    <span class="x-icon">✗</span> FAIL
                <?php endif; ?>
            </div>
            
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Test</th>
                        <th width="80">Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($test_results as $name => $result): ?>
                    <tr>
                        <td><strong><?= ucwords(str_replace('_', ' ', $name)) ?></strong></td>
                        <td class="text-center">
                            <?php if ($result['status']): ?>
                                <span class="check-icon">✓</span>
                            <?php else: ?>
                                <span class="x-icon">✗</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($result['message']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($test_results['data_retrieval']['status']): ?>
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h2 class="h4 mb-0">Data Verification</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Appointments Table</h5>
                    <?php if (!empty($test_results['data_retrieval']['appointments'])): ?>
                        <span class="check-icon">✓</span> Data found (<?= count($test_results['data_retrieval']['appointments']) ?> records)
                    <?php else: ?>
                        <span class="x-icon">✗</span> No appointment data found
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h5>Provider Availability Table</h5>
                    <?php if (!empty($test_results['data_retrieval']['availability'])): ?>
                        <span class="check-icon">✓</span> Data found (<?= count($test_results['data_retrieval']['availability']) ?> records)
                    <?php else: ?>
                        <span class="x-icon">✗</span> No availability data found
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h2 class="h4 mb-0">Frontend Technology Tests</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Bootstrap Integration</h5>
                    <?php if ($test_results['bootstrap_available']['status']): ?>
                        <span class="check-icon">✓</span> Bootstrap is available
                    <?php else: ?>
                        <span class="x-icon">✗</span> Bootstrap not found
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h5>FullCalendar Integration</h5>
                    <?php if ($test_results['fullcalendar_integration']['status']): ?>
                        <span class="check-icon">✓</span> FullCalendar is available
                    <?php else: ?>
                        <span class="x-icon">✗</span> FullCalendar not found
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h2 class="h4 mb-0">Environment Information</h2>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <li class="list-group-item"><strong>PHP Version:</strong> <?= phpversion() ?></li>
                <li class="list-group-item"><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></li>
                <li class="list-group-item"><strong>Database:</strong> MySQL</li>
                <li class="list-group-item"><strong>Test Run Date:</strong> <?= date('Y-m-d H:i:s') ?></li>
            </ul>
        </div>
    </div>
</body>
</html>
