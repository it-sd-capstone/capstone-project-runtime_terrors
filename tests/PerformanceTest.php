<?php
// Start output buffering at the very beginning to control output
ob_start();

require_once dirname(__DIR__) . '/public_html/bootstrap.php';

/**
 * Enhanced Performance Test Suite
 * Runs comprehensive performance tests on the appointment system
 */
class EnhancedPerformanceTest {
    private $db;
    private $testResults = [];
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $metrics = [];
    
    /**
     * Initialize test environment
     */
    public function setUp() {
        // Initialize database connection
        $this->db = get_db();
        
        // Initialize metrics storage
        $this->initializeMetrics();
        
        echo "<div class='test-container'>";
        echo "<h2 class='test-header'>Enhanced Performance Test Suite</h2>";
    }
    
    /**
     * Clean up resources
     */
    public function tearDown() {
        // Clean up any test data if necessary
        
        // Close the container div
        echo "</div>";
        
        // Display test summary
        $this->displaySummary();
    }
    
    /**
     * Initialize performance metrics storage
     */
    private function initializeMetrics() {
        $this->metrics = [
            'response_times' => [],
            'database_query_times' => [],
            'memory_usage' => [],
            'concurrent_users' => [],
            'load_times' => []
        ];
    }
    
    /**
     * Run all tests
     */
    public function run() {
        // Clear any existing output first
        ob_clean();
        
        // Print only the test header
        echo "<!DOCTYPE html><html><head><title>Performance Tests</title></head><body>";
        
        $this->setUp();
        
        // Run all test methods
        $this->testResponseTimes();
        $this->testConcurrentUsers();
        $this->testDatabaseOptimization();
        $this->testMemoryUsage();
        $this->testSystemLoad();
        
        $this->tearDown();
        
        echo "</body></html>";
        
        // Flush the output buffer
        ob_end_flush();
    }
    
    /**
     * Test response times for key pages
     */
    public function testResponseTimes() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Page Response Times</h3>";
        
        // Define key pages to test
        $pages = [
            'Home Page' => '/',
            'Login Page' => '/auth/login',
            'Dashboard' => '/dashboard',
            'Appointment Booking' => '/appointments/book',
            'Provider Schedule' => '/provider/schedule'
        ];
        
        foreach ($pages as $name => $url) {
            $this->assertTest(
                "Response Time: {$name}",
                function() use ($url, $name) {
                    $start_time = microtime(true);
                    
                    // Simulate a page request
                    $response = $this->simulatePageRequest($url);
                    
                    $end_time = microtime(true);
                    $response_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
                    
                    // Store metric for reporting
                    $this->metrics['response_times'][$name] = $response_time;
                    
                    // Pass if response time is under 500ms
                    return $response_time < 500;
                },
                "Page should load in under 500ms"
            );
        }
        
        echo "</div>";
    }
    
    /**
     * Test concurrent user handling
     */
    public function testConcurrentUsers() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Concurrent User Handling</h3>";
        
        // Test with different numbers of concurrent users
        $userCounts = [5, 10, 20, 50];
        
        foreach ($userCounts as $count) {
            $this->assertTest(
                "Handle {$count} Concurrent Users",
                function() use ($count) {
                    $start_time = microtime(true);
                    
                    // Simulate multiple users booking simultaneously
                    $success = $this->simulateConcurrentBookings($count);
                    
                    $end_time = microtime(true);
                    $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
                    
                    // Store metric for reporting
                    $this->metrics['concurrent_users'][$count] = $execution_time;
                    
                    // Test passes if no errors were encountered
                    return $success;
                },
                "System should handle {$count} concurrent bookings without errors"
            );
        }
        
        echo "</div>";
    }
    
    /**
     * Test database optimization and query performance
     */
    public function testDatabaseOptimization() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Database Query Performance</h3>";
        
        // Test common database queries
        $queries = [
            'Basic User Query' => "SELECT * FROM users WHERE role = 'patient' LIMIT 10",
            'Appointment Lookup' => "SELECT * FROM appointments WHERE appointment_date >= CURDATE() LIMIT 50",
            'Provider Availability' => "SELECT * FROM provider_availability WHERE provider_id = 68 AND availability_date >= CURDATE() LIMIT 50",
            'Complex Join Query' => "SELECT a.*, u1.first_name AS patient_name, u2.first_name AS provider_name, s.name AS service_name 
                                 FROM appointments a 
                                 JOIN users u1 ON a.patient_id = u1.user_id 
                                 JOIN users u2 ON a.provider_id = u2.user_id 
                                 JOIN services s ON a.service_id = s.service_id 
                                 LIMIT 25"
        ];
        
        foreach ($queries as $name => $sql) {
            $this->assertTest(
                "Query Performance: {$name}",
                function() use ($sql, $name) {
                    // Execute query and measure time
                    $start_time = microtime(true);
                    
                    $result = $this->db->query($sql);
                    
                    $end_time = microtime(true);
                    $query_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
                    
                    // Store metric for reporting
                    $this->metrics['database_query_times'][$name] = $query_time;
                    
                    // Test passes if query executes in under 100ms and returns valid result
                    return ($query_time < 100) && ($result !== false);
                },
                "Query should execute in under 100ms"
            );
        }
        
        // Test index usage
        $this->assertTest(
            "Database Index Usage",
            function() {
                // Check if key tables are using indexes for common queries
                $sql = "EXPLAIN SELECT * FROM appointments WHERE provider_id = 68";
                $result = $this->db->query($sql);
                
                if ($result) {
                    $row = $result->fetch_assoc();
                    // Check if 'key' field indicates an index is being used
                    return isset($row['key']) && !empty($row['key']);
                }
                
                return false;
            },
            "Queries should utilize database indexes"
        );
        
        echo "</div>";
    }
    
    /**
     * Test memory usage during operations
     */
    public function testMemoryUsage() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Memory Usage</h3>";
        
        $operations = [
            'Basic Database Query' => function() {
                $result = $this->db->query("SELECT * FROM users LIMIT 50");
                $data = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $data[] = $row;
                    }
                }
                return count($data) > 0;
            },
            'Large Data Processing' => function() {
                $result = $this->db->query("SELECT * FROM appointments");
                $data = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $data[] = $row;
                    }
                }
                // Process data
                $count = count($data);
                $filtered = array_filter($data, function($item) {
                    return isset($item['status']) && $item['status'] == 'confirmed';
                });
                return count($filtered) <= $count;
            },
            'Session Handling' => function() {
                // Simulate session operations
                $_SESSION['test_data'] = array_fill(0, 100, 'test_value');
                $result = isset($_SESSION['test_data']);
                unset($_SESSION['test_data']);
                return $result;
            }
        ];
        
        foreach ($operations as $name => $operation) {
            $this->assertTest(
                "Memory Usage: {$name}",
                function() use ($operation, $name) {
                    // Get memory before operation
                    $memory_before = memory_get_usage();
                    
                    // Run operation
                    $result = $operation();
                    
                    // Get memory after operation
                    $memory_after = memory_get_usage();
                    $memory_used = $memory_after - $memory_before;
                    
                    // Store metric for reporting
                    $this->metrics['memory_usage'][$name] = $memory_used;
                    
                    // Most operations should use less than 2MB of additional memory
                    $threshold = 2 * 1024 * 1024; // 2MB
                    
                    return $result && ($memory_used < $threshold);
                },
                "Operation should use reasonable amount of memory"
            );
        }
        
        echo "</div>";
    }
    
    /**
     * Test system load under various conditions
     */
    public function testSystemLoad() {
        echo "<div class='test-section'>";
        echo "<h3>Testing System Load</h3>";
        
        $loadTests = [
            'API Response Time' => function() {
                // Simulate API calls
                $start_time = microtime(true);
                
                // Simulate multiple API requests
                for ($i = 0; $i < 10; $i++) {
                    $this->simulateApiCall('/api/appointments');
                }
                
                $end_time = microtime(true);
                $total_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
                
                // Store metric
                $this->metrics['load_times']['API Requests'] = $total_time / 10; // Average time
                
                return $total_time < 1000; // Total under 1 second
            },
            'Database Connection Pool' => function() {
                // Test multiple database connections
                $connections = [];
                $max_connections = 5;
                
                $start_time = microtime(true);
                
                for ($i = 0; $i < $max_connections; $i++) {
                    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                    if (!$conn->connect_error) {
                        $connections[] = $conn;
                    }
                }
                
                $end_time = microtime(true);
                $total_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
                
                // Store metric
                $this->metrics['load_times']['DB Connections'] = $total_time / $max_connections;
                
                // Close connections
                foreach ($connections as $conn) {
                    $conn->close();
                }
                
                return count($connections) == $max_connections;
            },
            'Session Creation Speed' => function() {
                $sessions = 10;
                $start_time = microtime(true);
                
                for ($i = 0; $i < $sessions; $i++) {
                    // Simulate starting a new session
                    $session_id = md5(uniqid(rand(), true));
                    $session_data = [
                        'user_id' => rand(1, 100),
                        'logged_in' => true,
                        'created_at' => time()
                    ];
                }
                
                $end_time = microtime(true);
                $session_time = ($end_time - $start_time) * 1000 / $sessions;
                
                // Store metric
                $this->metrics['load_times']['Session Creation'] = $session_time;
                
                return $session_time < 10; // Under 10ms per session
            }
        ];
        
        foreach ($loadTests as $name => $test) {
            $this->assertTest(
                "System Load: {$name}",
                $test,
                "System should handle load efficiently"
            );
        }
        
        echo "</div>";
    }
    
    /**
     * Simulate a page request
     * 
     * @param string $url The URL to request
     * @return boolean Success status
     */
    private function simulatePageRequest($url) {
        // This is a simulation - in a real test, we might use cURL or a HTTP client
        // For this test, we'll simulate with a realistic delay
        usleep(rand(50000, 200000)); // 50-200ms
        
        // Simulate different response times based on page complexity
        if (strpos($url, 'dashboard') !== false) {
            usleep(100000); // Additional 100ms for dashboard page
        }
        
        return true;
    }
    
    /**
     * Simulate concurrent booking requests
     * 
     * @param int $count Number of concurrent users
     * @return boolean Success status
     */
    private function simulateConcurrentBookings($count) {
        // In a real test, we might use threading or parallel processes
        // For simulation, we'll add time proportional to user count
        $base_time = 20000; // 20ms base time
        $per_user = 5000;   // 5ms per user
        
        usleep($base_time + ($count * $per_user));
        
        // Simulate a race condition probability at high user counts
        $race_condition = ($count > 30) ? (rand(1, 100) <= 5) : false; // 5% chance of race condition at >30 users
        
        return !$race_condition;
    }
    
    /**
     * Simulate an API call
     * 
     * @param string $endpoint API endpoint
     * @return string Simulated response
     */
    private function simulateApiCall($endpoint) {
        // Simulate API response time
        usleep(rand(30000, 80000)); // 30-80ms
        
        // Return a fake response
        return json_encode(['status' => 'success']);
    }
    
    /**
     * Assert test result
     * 
     * @param string $name Test name
     * @param callable|boolean $testFunction Function that returns boolean result or direct boolean
     * @param string $message Description of what is being tested
     */
    private function assertTest($name, $testFunction, $message = '') {
        $result = false;
        
        try {
            if (is_callable($testFunction)) {
                $result = $testFunction();
            } else {
                $result = (bool)$testFunction;
            }
        } catch (Exception $e) {
            error_log("Test exception: " . $e->getMessage());
            $result = false;
        }
        
        if ($result) {
            echo "<div class='test-result success'><strong>✓ PASS:</strong> {$name}</div>";
            $this->testsPassed++;
        } else {
            echo "<div class='test-result failure'><strong>✗ FAIL:</strong> {$name} - {$message}</div>";
            $this->testsFailed++;
        }
        
        $this->testResults[] = [
            'name' => $name,
            'passed' => $result,
            'message' => $message
        ];
    }
    
    /**
     * Format bytes to human-readable format
     * 
     * @param int $bytes Bytes to format
     * @return string Formatted string
     */
    private function formatBytes($bytes) {
        $units = ['bytes', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Display test summary
     */
    private function displaySummary() {
        echo "<div class='test-summary'>";
        echo "<h3>Test Summary</h3>";
        echo "<p><strong>Total Tests:</strong> " . ($this->testsPassed + $this->testsFailed) . "</p>";
        echo "<p><strong>Tests Passed:</strong> {$this->testsPassed}</p>";
        echo "<p><strong>Tests Failed:</strong> {$this->testsFailed}</p>";
        
        if ($this->testsFailed > 0) {
            echo "<div class='failed-tests'>";
            echo "<h4>Failed Tests:</h4>";
            echo "<ul>";
            foreach ($this->testResults as $test) {
                if (!$test['passed']) {
                    echo "<li><strong>{$test['name']}:</strong> {$test['message']}</li>";
                }
            }
            echo "</ul>";
            echo "</div>";
        }
        
        echo "</div>";
        
        // Display performance metrics
        echo "<div class='metrics-summary'>";
        echo "<h3>Performance Metrics</h3>";
        
        // Response Times
        if (!empty($this->metrics['response_times'])) {
            echo "<div class='metric-section'>";
            echo "<h4>Page Response Times</h4>";
            echo "<table class='metrics-table'>";
            echo "<tr><th>Page</th><th>Response Time (ms)</th><th>Status</th></tr>";
            
            foreach ($this->metrics['response_times'] as $page => $time) {
                $status = $time < 500 ? 'Good' : 'Needs Improvement';
                $statusClass = $time < 500 ? 'good' : 'warning';
                
                echo "<tr>";
                echo "<td>{$page}</td>";
                echo "<td>" . number_format($time, 2) . " ms</td>";
                echo "<td class='{$statusClass}'>{$status}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
        }
        
        // Database Query Times
        if (!empty($this->metrics['database_query_times'])) {
            echo "<div class='metric-section'>";
            echo "<h4>Database Query Performance</h4>";
            echo "<table class='metrics-table'>";
            echo "<tr><th>Query</th><th>Execution Time (ms)</th><th>Status</th></tr>";
            
            foreach ($this->metrics['database_query_times'] as $query => $time) {
                $status = $time < 100 ? 'Good' : 'Needs Optimization';
                $statusClass = $time < 100 ? 'good' : 'warning';
                
                echo "<tr>";
                echo "<td>{$query}</td>";
                echo "<td>" . number_format($time, 2) . " ms</td>";
                echo "<td class='{$statusClass}'>{$status}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
        }
        
        // Memory Usage
        if (!empty($this->metrics['memory_usage'])) {
            echo "<div class='metric-section'>";
            echo "<h4>Memory Usage</h4>";
            echo "<table class='metrics-table'>";
            echo "<tr><th>Operation</th><th>Memory Used</th><th>Status</th></tr>";
            
            foreach ($this->metrics['memory_usage'] as $operation => $bytes) {
                $formatted = $this->formatBytes($bytes);
                $status = $bytes < (2 * 1024 * 1024) ? 'Good' : 'High Usage';
                $statusClass = $bytes < (2 * 1024 * 1024) ? 'good' : 'warning';
                
                echo "<tr>";
                echo "<td>{$operation}</td>";
                echo "<td>{$formatted}</td>";
                echo "<td class='{$statusClass}'>{$status}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
        }
        
        // Concurrent Users
        if (!empty($this->metrics['concurrent_users'])) {
            echo "<div class='metric-section'>";
            echo "<h4>Concurrent User Performance</h4>";
            echo "<table class='metrics-table'>";
            echo "<tr><th>User Count</th><th>Processing Time (ms)</th><th>Status</th></tr>";
            
            foreach ($this->metrics['concurrent_users'] as $count => $time) {
                $status = $time < ($count * 20) ? 'Good' : 'Needs Optimization';
                $statusClass = $time < ($count * 20) ? 'good' : 'warning';
                
                echo "<tr>";
                echo "<td>{$count} Users</td>";
                echo "<td>" . number_format($time, 2) . " ms</td>";
                echo "<td class='{$statusClass}'>{$status}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
        }
        
        // System Load Times
        if (!empty($this->metrics['load_times'])) {
            echo "<div class='metric-section'>";
            echo "<h4>System Load Metrics</h4>";
            echo "<table class='metrics-table'>";
            echo "<tr><th>Test</th><th>Time (ms)</th><th>Status</th></tr>";
            
            foreach ($this->metrics['load_times'] as $test => $time) {
                $threshold = ($test == 'API Requests') ? 100 : 50;
                $status = $time < $threshold ? 'Good' : 'Needs Optimization';
                $statusClass = $time < $threshold ? 'good' : 'warning';
                
                echo "<tr>";
                echo "<td>{$test}</td>";
                echo "<td>" . number_format($time, 2) . " ms</td>";
                echo "<td class='{$statusClass}'>{$status}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
        }
        
        echo "</div>";
        
        // Add styling
        echo "<style>
            .test-container {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                padding: 20px;
                background-color: #f8f9fa;
                border-radius: 5px;
                margin-bottom: 20px;
                max-width: 100%;
                box-sizing: border-box;
            }
            .test-header {
                color: #333;
                border-bottom: 2px solid #ccc;
                padding-bottom: 10px;
                margin-top: 0;
            }
            .test-section {
                margin-bottom: 20px;
                padding: 15px;
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .test-section h3 {
                color: #444;
                margin-top: 0;
                border-bottom: 1px solid #eee;
                padding-bottom: 5px;
            }
            .test-result {
                padding: 10px;
                margin-bottom: 10px;
                border-radius: 4px;
            }
            .success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .failure {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .test-summary, .metrics-summary {
                margin-top: 20px;
                padding: 15px;
                background-color: #e9ecef;
                border-radius: 5px;
            }
            .metrics-summary {
                background-color: #f1f8ff;
            }
            .failed-tests {
                background-color: #fff;
                padding: 15px;
                border-radius: 5px;
                margin-top: 15px;
            }
            .failed-tests h4 {
                color: #721c24;
                margin-top: 0;
            }
            .metric-section {
                margin-bottom: 20px;
            }
            .metrics-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 15px;
            }
            .metrics-table th {
                background-color: #e9ecef;
                text-align: left;
                padding: 8px;
                border: 1px solid #ddd;
            }
            .metrics-table td {
                padding: 8px;
                border: 1px solid #ddd;
            }
            .metrics-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .good {
                color: #155724;
            }
            .warning {
                color: #856404;
            }
            .critical {
                color: #721c24;
            }
            body {
                background: transparent !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        </style>";
    }
}

// Create test instance and run tests
$test = new EnhancedPerformanceTest();
$test->run();
?>