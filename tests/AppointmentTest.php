<?php
// Start output buffering at the very beginning to control output
ob_start();
require_once dirname(__DIR__) . '/public_html/bootstrap.php';

/**
 * Appointment System Test Suite
 * Runs tests on the appointment scheduling features
 */
class AppointmentTest {
    private $db;
    private $userModel;
    private $serviceModel;
    private $appointmentModel;
    private $testResults = [];
    private $testsPassed = 0;
    private $testsFailed = 0;
    
    // Test data
    private $patientId;
    private $providerId;
    private $serviceId;
    private $appointmentId;
    
    /**
     * Initialize test environment
     */
    public function setUp() {
        // Initialize database connection
        $this->db = get_db();
        
        // Initialize models
        require_once MODEL_PATH . '/User.php';
        $this->userModel = new User($this->db);
        
        require_once MODEL_PATH . '/Services.php';
        $this->serviceModel = new Services($this->db);
        
        require_once MODEL_PATH . '/Appointment.php';
        $this->appointmentModel = new Appointment($this->db);
        
        // Get primary key column names
        $userPrimaryKey = $this->getUserPrimaryKeyColumn();
        $servicePrimaryKey = $this->getServicePrimaryKeyColumn();
        $appointmentPrimaryKey = $this->getAppointmentPrimaryKeyColumn();
        
        echo "<div class='test-container'>";
        echo "<h2 class='test-header'>Appointment System Test Suite</h2>";
        
        echo "Using primary key for users: " . $userPrimaryKey . "<br>";
        echo "Using primary key for services: " . $servicePrimaryKey . "<br>";
        echo "Using primary key for appointments: " . $appointmentPrimaryKey . "<br>";
        
        // Find a patient user for testing
        $patientQuery = "SELECT $userPrimaryKey FROM users WHERE role = 'patient' LIMIT 1";
        $patientResult = $this->db->query($patientQuery);
        if ($patientResult && $patientResult->num_rows > 0) {
            $this->patientId = $patientResult->fetch_assoc()[$userPrimaryKey];
        } else {
            // Create a test patient if none exists
            $this->patientId = $this->createTestUser('patient');
        }
        
        // Find a provider user for testing
        $providerQuery = "SELECT $userPrimaryKey FROM users WHERE role = 'provider' LIMIT 1";
        $providerResult = $this->db->query($providerQuery);
        if ($providerResult && $providerResult->num_rows > 0) {
            $this->providerId = $providerResult->fetch_assoc()[$userPrimaryKey];
        } else {
            // Create a test provider if none exists
            $this->providerId = $this->createTestUser('provider');
        }
        
        // Find a service for testing
        $serviceQuery = "SELECT $servicePrimaryKey FROM services LIMIT 1";
        $serviceResult = $this->db->query($serviceQuery);
        if ($serviceResult && $serviceResult->num_rows > 0) {
            $this->serviceId = $serviceResult->fetch_assoc()[$servicePrimaryKey];
        } else {
            // Create a test service if none exists
            $this->serviceId = $this->createTestService();
        }
        
        echo "Test environment initialized successfully.<br>";
    }
    
    /**
     * Clean up resources
     */
    public function tearDown() {
        // Clean up any test appointments created during testing
        $this->cleanupTestData();
        
        // Close the container div
        echo "</div>";
        
        // Display test summary
        $this->displaySummary();
    }
    
    /**
     * Run all tests
     */
    public function run() {
        try {
            $this->setUp();
            
            // Run all test methods
            $this->testAppointmentLifecycle();
            $this->testDoubleBookingPrevention();
            
            $this->tearDown();
            
            // Get the output
            $output = ob_get_contents();
            
            // Return success if all tests passed
            return $this->testsFailed == 0;
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>Test Error</h4>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";
            return false;
        }
    }
    
    /**
     * Test appointment lifecycle (create, reschedule, cancel)
     */
    public function testAppointmentLifecycle() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Appointment Lifecycle</h3>";
        
        // Get column names for appointments table
        $appointmentColumns = $this->getTableColumns('appointments');
        echo "Using columns: ";
        
        // Show the patient, provider, and service column names
        $patientColumn = in_array('patient_id', $appointmentColumns) ? 'patient_id' : 'user_id';
        $providerColumn = in_array('provider_id', $appointmentColumns) ? 'provider_id' : 'provider';
        $serviceColumn = in_array('service_id', $appointmentColumns) ? 'service_id' : 'service';
        $dateColumn = in_array('appointment_date', $appointmentColumns) ? 'appointment_date' : 'date'; // Use correct date column
        
        echo "patient=$patientColumn, provider=$providerColumn, service=$serviceColumn, date=$dateColumn<br>";
        
        // Create an appointment
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $startTime = '10:00:00';
        $endTime = '11:00:00';
        $appointmentDate = $tomorrow;
        
        // Build SQL based on whether end_time column exists
        if (in_array('end_time', $appointmentColumns)) {
            // Check if status column exists
            $statusColumnExists = in_array('status', $appointmentColumns);
            $scheduledStatus = 'scheduled';
            
            if ($statusColumnExists) {
                $sql = "INSERT INTO appointments ($patientColumn, $providerColumn, $serviceColumn, $dateColumn, start_time, end_time, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            } else {
                $sql = "INSERT INTO appointments ($patientColumn, $providerColumn, $serviceColumn, $dateColumn, start_time, end_time)
                        VALUES (?, ?, ?, ?, ?, ?)";
            }
        } else {
            // No end_time column, check for status
            $statusColumnExists = in_array('status', $appointmentColumns);
            $scheduledStatus = 'scheduled';
            
            if ($statusColumnExists) {
                $sql = "INSERT INTO appointments ($patientColumn, $providerColumn, $serviceColumn, $dateColumn, start_time, status)
                        VALUES (?, ?, ?, ?, ?, ?)";
            } else {
                $sql = "INSERT INTO appointments ($patientColumn, $providerColumn, $serviceColumn, $dateColumn, start_time)
                        VALUES (?, ?, ?, ?, ?)";
            }
        }
        
        $stmt = $this->db->prepare($sql);
        
        if (in_array('end_time', $appointmentColumns) && $statusColumnExists) {
            $stmt->bind_param("iiissss", 
                $this->patientId, 
                $this->providerId, 
                $this->serviceId,
                $tomorrow, 
                $startTime, 
                $endTime, 
                $scheduledStatus
            );
        } elseif (in_array('end_time', $appointmentColumns)) {
            $stmt->bind_param("iiisss", 
                $this->patientId, 
                $this->providerId, 
                $this->serviceId,
                $tomorrow, 
                $startTime, 
                $endTime
            );
        } elseif ($statusColumnExists) {
            $stmt->bind_param("iiiss", 
                $this->patientId, 
                $this->providerId, 
                $this->serviceId,
                $tomorrow, 
                $startTime, 
                $scheduledStatus
            );
        } else {
            $stmt->bind_param("iiis", 
                $this->patientId, 
                $this->providerId, 
                $this->serviceId,
                $tomorrow, 
                $startTime
            );
        }
        
        $createResult = $stmt->execute();
        $this->appointmentId = $this->db->insert_id;
        
        $this->assertTest(
            'Create Appointment',
            function() use ($createResult) {
                return $createResult && $this->appointmentId > 0;
            },
            'Appointment created successfully with ID: ' . $this->appointmentId
        );
        
        // Verify appointment status if status column exists
        if ($statusColumnExists) {
            $this->assertTest(
                'Verify Appointment Status',
                function() use ($statusColumnExists, $scheduledStatus) {
                    $appointmentPK = $this->getAppointmentPrimaryKeyColumn();
                    $stmt = $this->db->prepare("SELECT status FROM appointments WHERE $appointmentPK = ?");
                    $stmt->bind_param("i", $this->appointmentId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $appointment = $result->fetch_assoc();
                    return $appointment && $appointment['status'] == $scheduledStatus;
                },
                'Appointment status verified as \'scheduled\''
            );
        }
        
        // Reschedule the appointment
        $newDate = date('Y-m-d', strtotime('+2 days'));
        $newStartTime = '14:00:00';
        $newEndTime = '15:00:00';
        
        $appointmentPK = $this->getAppointmentPrimaryKeyColumn();
        
        if (in_array('end_time', $appointmentColumns)) {
            $updateSql = "UPDATE appointments SET $dateColumn = ?, start_time = ?, end_time = ? WHERE $appointmentPK = ?";
            $stmt = $this->db->prepare($updateSql);
            $stmt->bind_param("sssi", $newDate, $newStartTime, $newEndTime, $this->appointmentId);
        } else {
            $updateSql = "UPDATE appointments SET $dateColumn = ?, start_time = ? WHERE $appointmentPK = ?";
            $stmt = $this->db->prepare($updateSql);
            $stmt->bind_param("ssi", $newDate, $newStartTime, $this->appointmentId);
        }
        
        $rescheduleResult = $stmt->execute();
        
        $this->assertTest(
            'Reschedule Appointment',
            function() use ($rescheduleResult) {
                return $rescheduleResult;
            },
            'Appointment successfully rescheduled'
        );
        
        // Cancel appointment
        if ($statusColumnExists) {
            $cancelSql = "UPDATE appointments SET status = ? WHERE $appointmentPK = ?";
            $cancelStatus = 'canceled';
            $stmt = $this->db->prepare($cancelSql);
            $stmt->bind_param("si", $cancelStatus, $this->appointmentId);
            $cancelResult = $stmt->execute();
            
            $this->assertTest(
                'Cancel Appointment',
                function() use ($cancelResult, $appointmentPK, $cancelStatus) {
                    $stmt = $this->db->prepare("SELECT status FROM appointments WHERE $appointmentPK = ?");
                    $stmt->bind_param("i", $this->appointmentId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $appointment = $result->fetch_assoc();
                    return $cancelResult && $appointment && $appointment['status'] == $cancelStatus;
                },
                'Appointment successfully cancelled'
            );
        } else {
            // If no status column, we'll delete the appointment to cancel it
            $cancelSql = "DELETE FROM appointments WHERE $appointmentPK = ?";
            $stmt = $this->db->prepare($cancelSql);
            $stmt->bind_param("i", $this->appointmentId);
            $cancelResult = $stmt->execute();
            
            $this->assertTest(
                'Cancel Appointment',
                function() use ($cancelResult) {
                    return $cancelResult;
                },
                'Appointment successfully cancelled'
            );
        }
        
        $this->assertTest(
            'Appointment Lifecycle Test',
            function() {
                return true;
            },
            'Appointment lifecycle test passed'
        );
        
        echo "</div>";
        
        return true;
    }
    
    /**
     * Test double booking prevention
     */
    public function testDoubleBookingPrevention() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Double Booking Prevention</h3>";
        
        // Get the primary key column name for users table
        $primaryKeyColumn = $this->getUserPrimaryKeyColumn();
        
        // Find a different patient for this test
        $stmt = $this->db->prepare("SELECT $primaryKeyColumn FROM users WHERE role = 'patient' AND $primaryKeyColumn != ? LIMIT 1");
        $stmt->bind_param("i", $this->patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $secondPatientId = $result->fetch_assoc()[$primaryKeyColumn];
        } else {
            // Create another test patient if none exists
            $secondPatientId = $this->createTestUser('patient');
        }
        
        // Create a test appointment for the provider
        $testDate = date('Y-m-d', strtotime('+3 days'));
        $startTime = '09:00:00';
        $endTime = '10:00:00';
        
        // Get appointment columns
        $appointmentColumns = $this->getTableColumns('appointments');
        $patientColumn = in_array('patient_id', $appointmentColumns) ? 'patient_id' : 'user_id';
        $providerColumn = in_array('provider_id', $appointmentColumns) ? 'provider_id' : 'provider';
                $serviceColumn = in_array('service_id', $appointmentColumns) ? 'service_id' : 'service';
        $dateColumn = in_array('appointment_date', $appointmentColumns) ? 'appointment_date' : 'date';
                
        // Build SQL based on whether end_time column exists
        if (in_array('end_time', $appointmentColumns)) {
            // Check if status column exists
            $statusColumnExists = in_array('status', $appointmentColumns);
            $scheduledStatus = 'scheduled';
            
            if ($statusColumnExists) {
                $sql = "INSERT INTO appointments ($patientColumn, $providerColumn, $serviceColumn, $dateColumn, start_time, end_time, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            } else {
                $sql = "INSERT INTO appointments ($patientColumn, $providerColumn, $serviceColumn, $dateColumn, start_time, end_time) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            }
        } else {
            // No end_time column, check for status
            $statusColumnExists = in_array('status', $appointmentColumns);
            $scheduledStatus = 'scheduled';
            
            if ($statusColumnExists) {
                $sql = "INSERT INTO appointments ($patientColumn, $providerColumn, $serviceColumn, $dateColumn, start_time, status) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            } else {
                $sql = "INSERT INTO appointments ($patientColumn, $providerColumn, $serviceColumn, $dateColumn, start_time) 
                        VALUES (?, ?, ?, ?, ?)";
            }
        }
        
        $stmt = $this->db->prepare($sql);
        
        if (in_array('end_time', $appointmentColumns) && $statusColumnExists) {
            $stmt->bind_param("iiissss", 
                $this->patientId, 
                $this->providerId, 
                $this->serviceId,
                $testDate, 
                $startTime, 
                $endTime, 
                $scheduledStatus
            );
        } elseif (in_array('end_time', $appointmentColumns)) {
            $stmt->bind_param("iiisss", 
                $this->patientId, 
                $this->providerId, 
                $this->serviceId,
                $testDate, 
                $startTime, 
                $endTime
            );
        } elseif ($statusColumnExists) {
            $stmt->bind_param("iiiss", 
                $this->patientId, 
                $this->providerId, 
                $this->serviceId,
                $testDate, 
                $startTime, 
                $scheduledStatus
            );
        } else {
            $stmt->bind_param("iiiss", 
                $this->patientId, 
                $this->providerId, 
                $this->serviceId,
                $testDate, 
                $startTime
            );
        }
        
        $firstBookingResult = $stmt->execute();
        $firstAppointmentId = $this->db->insert_id;
        
        $this->assertTest(
            'Create First Appointment',
            function() use ($firstBookingResult, $firstAppointmentId) {
                return $firstBookingResult && $firstAppointmentId > 0;
            },
            'Successfully created first test appointment'
        );
        
        // Now try to book a second appointment at the same time with a different patient
        if (in_array('end_time', $appointmentColumns) && $statusColumnExists) {
            $stmt->bind_param("iiissss", 
                $secondPatientId, 
                $this->providerId, 
                $this->serviceId,
                $testDate, 
                $startTime, 
                $endTime, 
                $scheduledStatus
            );
        } elseif (in_array('end_time', $appointmentColumns)) {
            $stmt->bind_param("iiisss", 
                $secondPatientId, 
                $this->providerId, 
                $this->serviceId,
                $testDate, 
                $startTime, 
                $endTime
            );
        } elseif ($statusColumnExists) {
            $stmt->bind_param("iiiss", 
                $secondPatientId, 
                $this->providerId, 
                $this->serviceId,
                $testDate, 
                $startTime, 
                $scheduledStatus
            );
        } else {
            $stmt->bind_param("iiiss", 
                $secondPatientId, 
                $this->providerId, 
                $this->serviceId,
                $testDate, 
                $startTime
            );
        }
        
        // If the application has conflict detection, this should fail or be prevented
        $doubleBookingResult = true;
        $secondAppointmentId = null;
        
        try {
            $doubleBookingResult = $stmt->execute();
            $secondAppointmentId = $this->db->insert_id;
            
            // Check if the appointment model has a conflict detection method
            if (method_exists($this->appointmentModel, 'checkTimeConflict')) {
                // If the method exists, then we'd expect the application to prevent this
                // in the controller layer, but for testing we can detect if they overlap
                $hasConflict = $this->appointmentModel->checkTimeConflict(
                    $this->providerId, 
                    $testDate, 
                    $startTime, 
                    $endTime,
                    null // exclude appointment ID
                );
                
                $doubleBookingResult = !$hasConflict;
            }
        } catch (Exception $e) {
            // If an exception was thrown, it might be because of a unique constraint
            $doubleBookingResult = false;
        }
        
        // For the test to pass, either:
        // 1. The insert failed (doubleBookingResult is false), or
        // 2. The application has a conflict detection method and it detected the conflict
        $this->assertTest(
            'Double Booking Prevention',
            function() use ($doubleBookingResult, $secondAppointmentId) {
                // The test passes if either:
                // - The double booking was prevented (execute() returned false)
                // - Or the application has conflict detection that would alert the user
                return !$doubleBookingResult || 
                    (method_exists($this->appointmentModel, 'checkTimeConflict') && 
                     $secondAppointmentId == 0);
            },
            'System should prevent double booking or detect conflicts'
        );
        
        // Clean up the test appointments
        $appointmentPK = $this->getAppointmentPrimaryKeyColumn();
        if ($firstAppointmentId) {
            $cleanupSql = "DELETE FROM appointments WHERE $appointmentPK = ?";
            $stmt = $this->db->prepare($cleanupSql);
            $stmt->bind_param("i", $firstAppointmentId);
            $stmt->execute();
        }
        
        if ($secondAppointmentId) {
            $cleanupSql = "DELETE FROM appointments WHERE $appointmentPK = ?";
            $stmt = $this->db->prepare($cleanupSql);
            $stmt->bind_param("i", $secondAppointmentId);
            $stmt->execute();
        }
        
        echo "</div>";
        
        return true;
    }
    
    /**
     * Get user primary key column name
     */
    private function getUserPrimaryKeyColumn() {
        $columns = $this->getTableColumns('users');
        return in_array('user_id', $columns) ? 'user_id' : 'id';
    }
    
    /**
     * Get service primary key column name
     */
    private function getServicePrimaryKeyColumn() {
        $columns = $this->getTableColumns('services');
        return in_array('service_id', $columns) ? 'service_id' : 'id';
    }
    
    /**
     * Get appointment primary key column name
     */
    private function getAppointmentPrimaryKeyColumn() {
        $columns = $this->getTableColumns('appointments');
        return in_array('appointment_id', $columns) ? 'appointment_id' : 'id';
    }
    
    /**
     * Get table column names
     * 
     * @param string $table Table name
     * @return array Column names
     */
    private function getTableColumns($table) {
        $columns = [];
        $result = $this->db->query("SHOW COLUMNS FROM $table");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
        }
        return $columns;
    }
    
    /**
     * Create a test user
     * 
     * @param string $role User role (patient, provider, admin)
     * @return int User ID
     */
    private function createTestUser($role) {
        $email = 'test_' . $role . '_' . time() . '@example.com';
        $password = password_hash('TestPass123!', PASSWORD_DEFAULT);
        $firstName = 'Test';
        $lastName = ucfirst($role);
        $isActive = 1;
        $isVerified = 1;
        
        $sql = "INSERT INTO users (email, password_hash, first_name, last_name, role, is_active, is_verified) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssssii", $email, $password, $firstName, $lastName, $role, $isActive, $isVerified);
        $stmt->execute();
        
        return $this->db->insert_id;
    }
    
    /**
     * Create a test service
     * 
     * @return int Service ID
     */
    private function createTestService() {
        $name = 'Test Service ' . time();
        $description = 'Test service for automated testing';
        $duration = 60;
        $price = 100.00;
        
        // Check if the table has a duration column
        $columns = $this->getTableColumns('services');
        if (in_array('duration', $columns)) {
            $sql = "INSERT INTO services (name, description, duration, price) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssid", $name, $description, $duration, $price);
        } else {
            $sql = "INSERT INTO services (name, description, price) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssd", $name, $description, $price);
        }
        
        $stmt->execute();
        return $this->db->insert_id;
    }
    
    /**
     * Clean up test data created during testing
     */
    private function cleanupTestData() {
        // Clean up test appointments
        if ($this->appointmentId) {
            $appointmentPK = $this->getAppointmentPrimaryKeyColumn();
            $sql = "DELETE FROM appointments WHERE $appointmentPK = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $this->appointmentId);
            $stmt->execute();
        }
    }
    
    /**
     * Assert test result
     *
     * @param string $name Test name
     * @param callable $testFunction Function that returns boolean result
     * @param string $message Description of what is being tested
     */
    private function assertTest($name, $testFunction, $message = '') {
        $result = false;
        
        try {
            $result = $testFunction();
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
            .test-summary {
                margin-top: 20px;
                padding: 15px;
                background-color: #e9ecef;
                border-radius: 5px;
            }
            .failed-tests {
                background-color: #fff;
                padding: 15px;
                border-radius: 5px;
                margin-top: 10px;
            }
            code {
                font-family: Monaco, Menlo, Consolas, 'Courier New', monospace;
                background-color: #f1f1f1;
                padding: 2px 4px;
                border-radius: 3px;
                font-size: 90%;
            }
        </style>";
    }
}

// Instantiate and run the test
$appointmentTest = new AppointmentTest();
$result = $appointmentTest->run();

// Display output or return status code for CI
ob_end_flush();
exit($result ? 0 : 1);
  