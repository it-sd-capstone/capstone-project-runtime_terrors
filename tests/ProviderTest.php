<?php
// Start output buffering to control output
ob_start();
require_once dirname(__DIR__) . '/public_html/bootstrap.php';

/**
 * Provider System Test Suite
 * Tests provider profile management and service offerings
 */
class ProviderTest {
    private $db;
    private $userModel;
    private $serviceModel;
    private $providerModel;
    private $testResults = [];
    private $testsPassed = 0;
    private $testsFailed = 0;
    
    // Test data
    private $providerId;
    private $serviceIds = [];
    
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
        
        // Check if Provider model exists
        if (file_exists(MODEL_PATH . '/Provider.php')) {
            require_once MODEL_PATH . '/Provider.php';
            $this->providerModel = new Provider($this->db);
        } else {
            // If no dedicated Provider model, use User model with provider role
            $this->providerModel = $this->userModel;
        }
        
        // Get primary key column names
        $userPrimaryKey = $this->getUserPrimaryKeyColumn();
        $servicePrimaryKey = $this->getServicePrimaryKeyColumn();
        
        echo "<div class='test-container'>";
        echo "<h2 class='test-header'>Provider System Test Suite</h2>";
        
        // Find or create a provider for testing
        $providerQuery = "SELECT $userPrimaryKey FROM users WHERE role = 'provider' LIMIT 1";
        $providerResult = $this->db->query($providerQuery);
        if ($providerResult && $providerResult->num_rows > 0) {
            $this->providerId = $providerResult->fetch_assoc()[$userPrimaryKey];
            echo "Using existing provider with ID: " . $this->providerId . "<br>";
        } else {
            // Create a test provider if none exists
            $this->providerId = $this->createTestProvider();
            echo "Created test provider with ID: " . $this->providerId . "<br>";
        }
        
        // Find or create test services
        $this->createTestServices();
        
        echo "Test environment initialized successfully.<br>";
    }
    
    /**
     * Clean up resources
     */
    public function tearDown() {
        // Clean up any test data created during testing
        $this->cleanupTestData();
        
        // Close the container div
        echo "</div>";
        
        // Display test summary
        $this->displaySummary();
    }
    
    /**
     * Test provider profile management
     */
    public function testProviderProfile() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Provider Profile Management</h3>";
        
        // Check if provider_profiles table exists
        $tableExists = $this->tableExists('provider_profiles');
        $profileTableName = $tableExists ? 'provider_profiles' : 'users';
        
        echo "Using table for provider profiles: $profileTableName<br>";
        
        // If the profile is stored in provider_profiles table
        if ($tableExists) {
            // Check if profile already exists for this provider
            $profileExistsQuery = "SELECT * FROM provider_profiles WHERE provider_id = ?";
            $stmt = $this->db->prepare($profileExistsQuery);
            $stmt->bind_param("i", $this->providerId);
            $stmt->execute();
            $profileResult = $stmt->get_result();
            $profileExists = $profileResult->num_rows > 0;
            
            // Create test profile data
            $bio = "Test provider bio created during automated testing. This provider specializes in test services.";
            $credentials = "Ph.D in Testing, Automated University";
            $specialties = "Unit Testing, Integration Testing";
            $visibility = 1; // Public
            
            if ($profileExists) {
                // Update existing profile
                $updateFields = [];
                $updateParams = [];
                $updateTypes = "";
                
                // Check which columns exist in the table
                $columns = $this->getTableColumns('provider_profiles');
                
                if (in_array('bio', $columns)) {
                    $updateFields[] = "bio = ?";
                    $updateParams[] = $bio;
                    $updateTypes .= "s";
                }
                
                if (in_array('credentials', $columns)) {
                    $updateFields[] = "credentials = ?";
                    $updateParams[] = $credentials;
                    $updateTypes .= "s";
                }
                
                if (in_array('specialties', $columns)) {
                    $updateFields[] = "specialties = ?";
                    $updateParams[] = $specialties;
                    $updateTypes .= "s";
                }
                
                if (in_array('visibility', $columns)) {
                    $updateFields[] = "visibility = ?";
                    $updateParams[] = $visibility;
                    $updateTypes .= "i";
                }
                
                // Add provider_id to params
                $updateParams[] = $this->providerId;
                $updateTypes .= "i";
                
                $updateSql = "UPDATE provider_profiles SET " . implode(", ", $updateFields) . " WHERE provider_id = ?";
                $stmt = $this->db->prepare($updateSql);
                $stmt->bind_param($updateTypes, ...$updateParams);
                $updateResult = $stmt->execute();
                
                $this->assertTest(
                    'Update Provider Profile',
                    function() use ($updateResult) {
                        return $updateResult;
                    },
                    'Provider profile updated successfully'
                );
            } else {
                // Create new profile
                $insertFields = ["provider_id"];
                $placeholders = ["?"];
                $insertParams = [$this->providerId];
                $insertTypes = "i";
                
                // Check which columns exist in the table
                $columns = $this->getTableColumns('provider_profiles');
                
                if (in_array('bio', $columns)) {
                    $insertFields[] = "bio";
                    $placeholders[] = "?";
                    $insertParams[] = $bio;
                    $insertTypes .= "s";
                }
                
                if (in_array('credentials', $columns)) {
                    $insertFields[] = "credentials";
                    $placeholders[] = "?";
                    $insertParams[] = $credentials;
                    $insertTypes .= "s";
                }
                
                if (in_array('specialties', $columns)) {
                    $insertFields[] = "specialties";
                    $placeholders[] = "?";
                    $insertParams[] = $specialties;
                    $insertTypes .= "s";
                }
                
                if (in_array('visibility', $columns)) {
                    $insertFields[] = "visibility";
                    $placeholders[] = "?";
                    $insertParams[] = $visibility;
                    $insertTypes .= "i";
                }
                
                $insertSql = "INSERT INTO provider_profiles (" . implode(", ", $insertFields) . ") VALUES (" . implode(", ", $placeholders) . ")";
                $stmt = $this->db->prepare($insertSql);
                $stmt->bind_param($insertTypes, ...$insertParams);
                $createResult = $stmt->execute();
                
                $this->assertTest(
                    'Create Provider Profile',
                    function() use ($createResult) {
                        return $createResult;
                    },
                    'Provider profile created successfully'
                );
            }
            
            // Test retrieving the profile
            $stmt = $this->db->prepare("SELECT * FROM provider_profiles WHERE provider_id = ?");
            $stmt->bind_param("i", $this->providerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $profile = $result->fetch_assoc();
            
            $this->assertTest(
                'Retrieve Provider Profile',
                function() use ($profile) {
                    return $profile !== null;
                },
                'Provider profile retrieved successfully'
            );
            
            // Test updating the visibility (if column exists)
            if (in_array('visibility', $columns)) {
                $newVisibility = 0; // Private
                $stmt = $this->db->prepare("UPDATE provider_profiles SET visibility = ? WHERE provider_id = ?");
                $stmt->bind_param("ii", $newVisibility, $this->providerId);
                $visibilityResult = $stmt->execute();
                
                $this->assertTest(
                    'Update Profile Visibility',
                    function() use ($visibilityResult, $newVisibility) {
                        // Verify the visibility was updated
                        $stmt = $this->db->prepare("SELECT visibility FROM provider_profiles WHERE provider_id = ?");
                        $stmt->bind_param("i", $this->providerId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $profile = $result->fetch_assoc();
                        return $visibilityResult && $profile && $profile['visibility'] == $newVisibility;
                    },
                    'Provider profile visibility updated successfully'
                );
            }
        } else {
            // Profile information is stored in the users table
            // Update the provider's information
            $bio = "Test provider bio for automated testing";
            $updateFields = [];
            $updateParams = [];
            $updateTypes = "";
            
            // Check which profile-related columns exist in users table
            $columns = $this->getTableColumns('users');
            
            if (in_array('bio', $columns)) {
                $updateFields[] = "bio = ?";
                $updateParams[] = $bio;
                $updateTypes .= "s";
            }
            
            if (in_array('about', $columns)) {
                $updateFields[] = "about = ?";
                $updateParams[] = $bio;
                $updateTypes .= "s";
            }
            
            if (in_array('specialties', $columns)) {
                $updateFields[] = "specialties = ?";
                $updateParams[] = "Testing, Automation";
                $updateTypes .= "s";
            }
            
            // Only proceed if we have fields to update
            if (count($updateFields) > 0) {
                // Add user ID to params
                $userPK = $this->getUserPrimaryKeyColumn();
                $updateParams[] = $this->providerId;
                $updateTypes .= "i";
                
                $updateSql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE $userPK = ?";
                $stmt = $this->db->prepare($updateSql);
                $stmt->bind_param($updateTypes, ...$updateParams);
                $updateResult = $stmt->execute();
                
                $this->assertTest(
                    'Update Provider Information',
                    function() use ($updateResult) {
                        return $updateResult;
                    },
                    'Provider information updated successfully'
                );
                
                // Test retrieving the profile
                $userPK = $this->getUserPrimaryKeyColumn();
                $stmt = $this->db->prepare("SELECT * FROM users WHERE $userPK = ?");
                $stmt->bind_param("i", $this->providerId);
                $stmt->execute();
                $result = $stmt->get_result();
                $provider = $result->fetch_assoc();
                
                $this->assertTest(
                    'Retrieve Provider Information',
                    function() use ($provider) {
                        return $provider !== null;
                    },
                    'Provider information retrieved successfully'
                );
            } else {
                echo "No provider profile fields found in users table. Skipping profile tests.<br>";
            }
        }
        
        echo "</div>";
    }
    
    /**
     * Test provider services management
     */
    public function testProviderServices() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Provider Services Management</h3>";
        
        // Check if provider_services table exists (many-to-many relationship)
        $providerServicesExists = $this->tableExists('provider_services');
        
        if ($providerServicesExists) {
            // Use the provider_services table to manage services
            echo "Using provider_services table for service management<br>";
            
            // Get columns from provider_services table
            $columns = $this->getTableColumns('provider_services');
            $hasPricing = in_array('price', $columns) || in_array('custom_price', $columns);
            $priceColumn = in_array('price', $columns) ? 'price' : 'custom_price';
            
            // 1. Test adding services to provider
            foreach ($this->serviceIds as $serviceId) {
                // Check if service is already assigned to provider
                $checkSql = "SELECT * FROM provider_services WHERE provider_id = ? AND service_id = ?";
                $stmt = $this->db->prepare($checkSql);
                $stmt->bind_param("ii", $this->providerId, $serviceId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 0) {
                    // Add service to provider
                    if ($hasPricing) {
                        $customPrice = 120.00; // Custom price for testing
                        $insertSql = "INSERT INTO provider_services (provider_id, service_id, $priceColumn) VALUES (?, ?, ?)";
                        $stmt = $this->db->prepare($insertSql);
                        $stmt->bind_param("iid", $this->providerId, $serviceId, $customPrice);
                    } else {
                        $insertSql = "INSERT INTO provider_services (provider_id, service_id) VALUES (?, ?)";
                        $stmt = $this->db->prepare($insertSql);
                        $stmt->bind_param("ii", $this->providerId, $serviceId);
                    }
                    
                    $addResult = $stmt->execute();
                    
                    $this->assertTest(
                        "Add Service to Provider (ID: $serviceId)",
                        function() use ($addResult) {
                            return $addResult;
                        },
                        "Service added to provider successfully"
                    );
                } else {
                    echo "Service ID $serviceId already assigned to provider<br>";
                }
            }
            
            // 2. Test retrieving provider's services
            $stmt = $this->db->prepare("SELECT * FROM provider_services WHERE provider_id = ?");
            $stmt->bind_param("i", $this->providerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $this->assertTest(
                'Retrieve Provider Services',
                function() use ($result) {
                    return $result->num_rows > 0;
                },
                "Provider has " . $result->num_rows . " services assigned"
            );
            
            // 3. Test updating custom pricing (if applicable)
                        if ($hasPricing) {
                // Get the first service assigned to the provider
                $stmt = $this->db->prepare("SELECT * FROM provider_services WHERE provider_id = ? LIMIT 1");
                $stmt->bind_param("i", $this->providerId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $providerService = $result->fetch_assoc();
                    $serviceId = $providerService['service_id'];
                    $newPrice = 150.00; // New custom price
                    
                    // Update the price
                    $updateSql = "UPDATE provider_services SET $priceColumn = ? WHERE provider_id = ? AND service_id = ?";
                    $stmt = $this->db->prepare($updateSql);
                    $stmt->bind_param("dii", $newPrice, $this->providerId, $serviceId);
                    $updateResult = $stmt->execute();
                    
                    $this->assertTest(
                        'Update Custom Service Price',
                        function() use ($updateResult, $newPrice, $priceColumn) {
                            // Verify the price was updated
                            $stmt = $this->db->prepare("SELECT $priceColumn FROM provider_services WHERE provider_id = ? AND service_id = ?");
                            $stmt->bind_param("ii", $this->providerId, $serviceId);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $service = $result->fetch_assoc();
                            return $updateResult && $service && (float)$service[$priceColumn] == $newPrice;
                        },
                        'Custom service price updated successfully'
                    );
                }
            }
            
            // 4. Test removing a service from a provider
            if (count($this->serviceIds) > 0) {
                // Remove the last service
                $serviceToRemove = end($this->serviceIds);
                $deleteSql = "DELETE FROM provider_services WHERE provider_id = ? AND service_id = ?";
                $stmt = $this->db->prepare($deleteSql);
                $stmt->bind_param("ii", $this->providerId, $serviceToRemove);
                $removeResult = $stmt->execute();
                
                $this->assertTest(
                    'Remove Service from Provider',
                    function() use ($removeResult, $serviceToRemove) {
                        // Verify the service was removed
                        $stmt = $this->db->prepare("SELECT * FROM provider_services WHERE provider_id = ? AND service_id = ?");
                        $stmt->bind_param("ii", $this->providerId, $serviceToRemove);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        return $removeResult && $result->num_rows == 0;
                    },
                    'Service removed from provider successfully'
                );
            }
        } else {
            // No provider_services table, check if services are assigned directly to providers
            echo "No provider_services table found. Testing alternative service assignment methods.<br>";
            
            // Check if providers have a 'services' column (comma-separated list)
            $userColumns = $this->getTableColumns('users');
            if (in_array('services', $userColumns)) {
                // Update the services column
                $servicesList = implode(',', $this->serviceIds);
                $userPK = $this->getUserPrimaryKeyColumn();
                $stmt = $this->db->prepare("UPDATE users SET services = ? WHERE $userPK = ?");
                $stmt->bind_param("si", $servicesList, $this->providerId);
                $updateResult = $stmt->execute();
                
                $this->assertTest(
                    'Assign Services to Provider',
                    function() use ($updateResult, $servicesList) {
                        // Verify the services were assigned
                        $userPK = $this->getUserPrimaryKeyColumn();
                        $stmt = $this->db->prepare("SELECT services FROM users WHERE $userPK = ?");
                        $stmt->bind_param("i", $this->providerId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $provider = $result->fetch_assoc();
                        return $updateResult && $provider && $provider['services'] == $servicesList;
                    },
                    'Services assigned to provider successfully'
                );
            } else {
                echo "No method found to assign services to providers. Skipping service assignment tests.<br>";
            }
        }
        
        echo "</div>";
        
        return true;
    }
    
    /**
     * Check if a table exists in the database
     * 
     * @param string $tableName Table name to check
     * @return bool True if table exists
     */
    private function tableExists($tableName) {
        $result = $this->db->query("SHOW TABLES LIKE '$tableName'");
        return $result && $result->num_rows > 0;
    }
    
    /**
     * Get user primary key column name
     * 
     * @return string Primary key column name
     */
    private function getUserPrimaryKeyColumn() {
        $columns = $this->getTableColumns('users');
        return in_array('user_id', $columns) ? 'user_id' : 'id';
    }
    
    /**
     * Get service primary key column name
     * 
     * @return string Primary key column name
     */
    private function getServicePrimaryKeyColumn() {
        $columns = $this->getTableColumns('services');
        return in_array('service_id', $columns) ? 'service_id' : 'id';
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
     * Create a test provider
     * 
     * @return int Provider ID
     */
    private function createTestProvider() {
        $email = 'test_provider_' . time() . '@example.com';
        $password = password_hash('TestPass123!', PASSWORD_DEFAULT);
        $firstName = 'Test';
        $lastName = 'Provider';
        $role = 'provider';
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
     * Create or find test services
     */
    private function createTestServices() {
        $servicePK = $this->getServicePrimaryKeyColumn();
        
        // Try to find existing services
        $query = "SELECT $servicePK FROM services LIMIT 3";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            // Use existing services
            while ($row = $result->fetch_assoc()) {
                $this->serviceIds[] = $row[$servicePK];
            }
            echo "Using " . count($this->serviceIds) . " existing services<br>";
        } else {
            // Create test services
            $services = [
                [
                    'name' => 'Test Service 1',
                    'description' => 'First test service for provider testing',
                    'duration' => 60,
                    'price' => 100.00
                ],
                [
                    'name' => 'Test Service 2',
                    'description' => 'Second test service for provider testing',
                    'duration' => 90,
                    'price' => 150.00
                ]
            ];
            
            // Check if the services table has duration column
            $columns = $this->getTableColumns('services');
            $hasDuration = in_array('duration', $columns);
            
            foreach ($services as $service) {
                if ($hasDuration) {
                    $sql = "INSERT INTO services (name, description, duration, price) VALUES (?, ?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bind_param("ssid", $service['name'], $service['description'], $service['duration'], $service['price']);
                } else {
                    $sql = "INSERT INTO services (name, description, price) VALUES (?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bind_param("ssd", $service['name'], $service['description'], $service['price']);
                }
                
                $stmt->execute();
                $this->serviceIds[] = $this->db->insert_id;
            }
            
            echo "Created " . count($this->serviceIds) . " test services<br>";
        }
    }
    
    /**
     * Clean up test data
     */
    private function cleanupTestData() {
        // In a real application, you would clean up test data here
        // For this test, we'll leave the data for inspection
    }
    
    /**
     * Run all tests
     * 
     * @return bool True if all tests pass
     */
    public function run() {
        $this->setUp();
        
        $profileResult = $this->testProviderProfile();
        $servicesResult = $this->testProviderServices();
        
        $this->tearDown();
        
        return $this->testsFailed === 0;
    }
    
    /**
     * Assert test result
     * 
     * @param string $name Test name
     * @param callable $testFunction Function that returns boolean result
     * @param string $message Description of test
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

// Instantiate and run the tests
$test = new ProviderTest();
$result = $test->run();

// Display output or return status code for CI
ob_end_flush();
exit($result ? 0 : 1);
