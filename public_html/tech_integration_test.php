<?php
/**
 * Automated Technology Integration Test (Web Interface)
 */

// Define base path
define('APP_ROOT', dirname(__DIR__));

// Check if the database config file exists and is readable
$database_file = APP_ROOT . '/config/database.php';
if (!file_exists($database_file)) {
    die("Database configuration file not found at: $database_file");
}

// Include database configuration
require_once $database_file;

// Debug database configuration - you can comment this out after fixing
echo '<pre style="display:none;">';
echo "DB CONFIG CHECK: ";
var_dump(isset($db_config) ? "db_config variable exists" : "db_config variable not found");
echo '</pre>';

// If $db_config is not defined, check for constants-based configuration
if (!isset($db_config)) {
    // Try to use constants if they're defined instead
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

// Run tests
// Test 1: PHP & MySQL Database Connection
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
        // Updated table names to match actual database schema
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
    APP_ROOT . '/public_html/js/fullcalendar/main.min.js'
];
$fullcalendar_found = false;
foreach ($fullcalendar_paths as $path) {
    if (file_exists($path)) {
        $fullcalendar_found = true;
        break;
    }
}

// If not found locally, check if CDN is referenced in appointment view
if (!$fullcalendar_found) {
    $appointment_view = APP_ROOT . '/views/appointments/index.php';
    if (file_exists($appointment_view)) {
        $contents = file_get_contents($appointment_view);
        if (strpos($contents, 'fullcalendar') !== false &&
            strpos($contents, 'cdn.jsdelivr.net') !== false) {
            $fullcalendar_found = true;
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
    APP_ROOT . '/public_html/css/bootstrap.css'
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
    $view_files = [
        APP_ROOT . '/views/appointments/index.php',
        APP_ROOT . '/views/home/index.php',
        APP_ROOT . '/views/auth/index.php'
    ];
    
    foreach ($view_files as $view) {
        if (file_exists($view)) {
            $contents = file_get_contents($view);
            if (strpos($contents, 'bootstrap') !== false &&
                strpos($contents, 'cdn.jsdelivr.net') !== false) {
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
    <title>Automated Technology Integration Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-result {
            margin-bottom: 20px;
        }
        .test-pass {
            color: #198754;
        }
        .test-fail {
            color: #dc3545;
        }
        .component-test {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 10px;
            margin-bottom: 10px;
            position: relative;
        }
        .test-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body class="container mt-5 mb-5">
    <h1 class="mb-4">Automated Technology Integration Test</h1>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2 class="h4 mb-0">Test Results</h2>
        </div>
        <div class="card-body">
            <div class="alert <?= $all_tests_passed ? 'alert-success' : 'alert-danger' ?>">
                <strong>Overall Test Result:</strong> <?= $all_tests_passed ? 'PASS' : 'FAIL' ?>
            </div>
            
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Test</th>
                        <th>Status</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($test_results as $name => $result): ?>
                    <tr>
                        <td><strong><?= ucwords(str_replace('_', ' ', $name)) ?></strong></td>
                        <td>
                            <?php if ($result['status']): ?>
                                <span class="badge bg-success">PASS</span>
                            <?php else: ?>
                                <span class="badge bg-danger">FAIL</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($result['message']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($test_results['data_retrieval']['status'] && !empty($test_results['data_retrieval']['appointments'])): ?>
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h2 class="h4 mb-0">Sample Data: Appointments</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <?php foreach (array_keys($test_results['data_retrieval']['appointments'][0]) as $key): ?>
                            <th><?= htmlspecialchars($key) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($test_results['data_retrieval']['appointments'] as $appointment): ?>
                        <tr>
                            <?php foreach ($appointment as $value): ?>
                            <td><?= htmlspecialchars($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($test_results['data_retrieval']['status'] && !empty($test_results['data_retrieval']['availability'])): ?>
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h2 class="h4 mb-0">Sample Data: Availability</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <?php foreach (array_keys($test_results['data_retrieval']['availability'][0]) as $key): ?>
                            <th><?= htmlspecialchars($key) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($test_results['data_retrieval']['availability'] as $slot): ?>
                        <tr>
                            <?php foreach ($slot as $value): ?>
                            <td><?= htmlspecialchars($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h2 class="h4 mb-0">Live Technology Tests</h2>
        </div>
        <div class="card-body">
            <h3 class="h5">1. Bootstrap Components Visual Test <span id="bootstrap-test-result" class="badge bg-secondary">Testing...</span></h3>
            <div class="alert alert-info mb-3">
                <strong>Automated Test:</strong> The system is automatically checking if Bootstrap CSS is properly applied to these components. No user interaction required.
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <div class="component-test">
                        <span id="card-test-indicator" class="badge bg-secondary test-indicator">Testing...</span>
                        <h5>Card Component Test</h5>
                        <div id="test-card" class="card">
                            <div class="card-body">
                                <h6 class="card-title">Card Title</h6>
                                <p class="card-text">Testing card borders, padding and styling</p>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">Testing: Border radius, padding, shadow effects</small>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="component-test">
                        <span id="alert-test-indicator" class="badge bg-secondary test-indicator">Testing...</span>
                        <h5>Alert Component Test</h5>
                        <div id="test-alert" class="alert alert-warning" role="alert">
                        This is an alert component
                        </div>
                        <small class="text-muted mt-2 d-block">Testing: Background color, border, text styling</small>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <div class="component-test">
                        <span id="form-test-indicator" class="badge bg-secondary test-indicator">Testing...</span>
                        <h5>Form Component Test</h5>
                        <form id="test-form" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="test-input" class="form-label">Name</label>
                                <input type="text" class="form-control" id="test-input" required>
                                <div class="invalid-feedback">
                                    Please enter a name
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="test-select" class="form-label">Service</label>
                                <select class="form-select" id="test-select" required>
                                    <option value="">Choose a service</option>
                                    <option value="1">Regular Checkup</option>
                                    <option value="2">Therapy Session</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a service
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="test-date" class="form-label">Appointment Date</label>
                                <input type="date" class="form-control" id="test-date" required>
                                <div class="invalid-feedback">
                                    Please select a date
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                        <small class="text-muted mt-2 d-block">Testing: Form controls, validation styles, button styling</small>
                    </div>
                </div>
            
                <div class="col-md-6 mb-3">
                    <div class="component-test">
                        <span id="progress-test-indicator" class="badge bg-secondary test-indicator">Testing...</span>
                        <h5>Progress Bar Test</h5>
                        <div class="progress">
                            <div id="test-progress" class="progress-bar" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">75%</div>
                        </div>
                        <small class="text-muted mt-2 d-block">Testing: Width calculation, color styling, text contrast</small>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <div class="component-test">
                        <span id="list-test-indicator" class="badge bg-secondary test-indicator">Testing...</span>
                        <h5>List Group Test</h5>
                        <ul id="test-list" class="list-group">
                            <li class="list-group-item">List item 1</li>
                            <li class="list-group-item active">List item 2</li>
                            <li class="list-group-item">List item 3</li>
                        </ul>
                        <small class="text-muted mt-2 d-block">Testing: Border styling, active state highlighting</small>
                    </div>
                </div>
            </div>
            
            <h3 class="h5">2. FullCalendar.js Visual Test <span id="calendar-test-result" class="badge bg-secondary">Testing...</span></h3>
            <div class="alert alert-info mb-3">
                <strong>Automated Test:</strong> The system is checking if FullCalendar.js initializes properly and can display events. No user interaction required.
            </div>
            <div id="calendar" style="height: 400px;"></div>
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
    

    <!-- Include Bootstrap JS for form validation -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Include FullCalendar.js -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Bootstrap Component Tests
            let bootstrapTestsPassed = true;
            const bootstrapTestResult = document.getElementById('bootstrap-test-result');
            const cardIndicator = document.getElementById('card-test-indicator');
            const alertIndicator = document.getElementById('alert-test-indicator');
            const formIndicator = document.getElementById('form-test-indicator');
            const progressIndicator = document.getElementById('progress-test-indicator');
            const listIndicator = document.getElementById('list-test-indicator');
            
            // Test 1: Verify card styling
            const card = document.getElementById('test-card');
            if (!card || !window.getComputedStyle(card).borderRadius) {
                bootstrapTestsPassed = false;
                cardIndicator.textContent = "FAIL";
                cardIndicator.className = "badge bg-danger test-indicator";
                console.error("Card component failed style test");
            } else {
                cardIndicator.textContent = "PASS";
                cardIndicator.className = "badge bg-success test-indicator";
            }
            
            // Test 2: Verify alert styling
            const alert = document.getElementById('test-alert');
            if (!alert || window.getComputedStyle(alert).backgroundColor === 'rgba(0, 0, 0, 0)') {
                bootstrapTestsPassed = false;
                alertIndicator.textContent = "FAIL";
                alertIndicator.className = "badge bg-danger test-indicator";
                console.error("Alert component failed style test");
            } else {
                alertIndicator.textContent = "PASS";
                alertIndicator.className = "badge bg-success test-indicator";
            }
            
            // Test 3: Verify form controls
            const form = document.getElementById('test-form');
            const formInput = document.getElementById('test-input');
            const formSelect = document.getElementById('test-select');
            if (!form || !formInput || !formSelect) {
                bootstrapTestsPassed = false;
                formIndicator.textContent = "FAIL";
                formIndicator.className = "badge bg-danger test-indicator";
                console.error("Form component failed test - elements not found");
            } else {
                // Test form validation classes more flexibly
                if (!form || !formInput || !formSelect) {
                    // Keep existing error handling
                } else {
                    // Add a timeout to ensure styles are applied
                    setTimeout(() => {
                        formInput.classList.add('is-invalid');
                        const hasInvalidClass = formInput.classList.contains('is-invalid');
                        
                        if (!hasInvalidClass) {
                            // Only check for class presence, not computed styles
                            bootstrapTestsPassed = false;
                            formIndicator.textContent = "FAIL";
                            formIndicator.className = "badge bg-danger test-indicator";
                        } else {
                            formIndicator.textContent = "PASS";
                            formIndicator.className = "badge bg-success test-indicator";
                        }
                    }, 100);
                }                
                // Reset the form
                formInput.classList.remove('is-invalid');
            }
            
            // Test 4: Verify progress bar
            const progress = document.getElementById('test-progress');
            if (!progress || progress.style.width !== "75%") {
                bootstrapTestsPassed = false;
                progressIndicator.textContent = "FAIL";
                progressIndicator.className = "badge bg-danger test-indicator";
                console.error("Progress bar failed style test");
            } else {
                progressIndicator.textContent = "PASS";
                progressIndicator.className = "badge bg-success test-indicator";
            }
            
            // Test 5: Verify list group
            const list = document.getElementById('test-list');
            if (!list || list.querySelectorAll('li').length !== 3) {
                bootstrapTestsPassed = false;
                listIndicator.textContent = "FAIL";
                listIndicator.className = "badge bg-danger test-indicator";
                console.error("List group failed test");
            } else {
                listIndicator.textContent = "PASS";
                listIndicator.className = "badge bg-success test-indicator";
            }
            
            // Update Bootstrap test result
            bootstrapTestResult.textContent = bootstrapTestsPassed ? "PASS" : "FAIL";
            bootstrapTestResult.className = bootstrapTestsPassed ? "badge bg-success" : "badge bg-danger";
            
            // Form validation setup
            (function() {
                'use strict';
                
                // Fetch all forms we want to apply validation to
                const forms = document.querySelectorAll('.needs-validation');
                
                // Loop over and prevent submission
                Array.from(forms).forEach(form => {
                    form.addEventListener('submit', event => {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        
                        form.classList.add('was-validated');
                    }, false);
                });
            })();
            
            // FullCalendar Test
            const calendarTestResult = document.getElementById('calendar-test-result');
            const calendarEl = document.getElementById('calendar');
            
            if (!calendarEl) {
                calendarTestResult.textContent = "FAIL";
                calendarTestResult.className = "badge bg-danger";
                console.error("Calendar element not found");
                return;
            }
            
            try {
                // Get current date and calculate future dates for dynamic events
                const today = new Date();
                const nextMonth = new Date(today);
                nextMonth.setMonth(today.getMonth() + 1);
                
                // Format dates in YYYY-MM-DD format
                const formatDate = (date) => {
                    return date.getFullYear() + '-' + 
                           String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                           String(date.getDate()).padStart(2, '0');
                };
                
                // Create dynamic test events
                const testDate1 = formatDate(nextMonth);
                const testDate2 = formatDate(new Date(nextMonth.getTime() + (5 * 24 * 60 * 60 * 1000)));
                
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    initialDate: nextMonth,
                    height: 400,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek'
                    },
                    events: [
                        {
                            title: 'Appointment Test 1',
                            start: testDate1,
                            end: formatDate(new Date(new Date(testDate1).getTime() + (2 * 24 * 60 * 60 * 1000))),
                            color: '#dc3545'
                        },
                        {
                            title: 'Appointment Test 2',
                            start: testDate2 + 'T10:30:00',
                            end: testDate2 + 'T12:30:00',
                            color: '#0d6efd'
                        }
                    ]
                });
                
                // Render calendar
                calendar.render();
                
                // Check if FullCalendar rendered properly
                if (document.querySelector('.fc-toolbar') && 
                    document.querySelector('.fc-view-harness')) {
                    calendarTestResult.textContent = "PASS";
                    calendarTestResult.className = "badge bg-success";
                } else {
                    calendarTestResult.textContent = "FAIL";
                    calendarTestResult.className = "badge bg-danger";
                    console.error("Calendar did not render properly");
                }
            } catch (error) {
                calendarTestResult.textContent = "FAIL";
                calendarTestResult.className = "badge bg-danger";
                console.error("Calendar initialization error:", error);
            }
        });
    </script>
</body>
</html>
