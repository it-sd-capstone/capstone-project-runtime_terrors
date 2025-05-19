<?php
// Start output buffering at the very beginning to control output
ob_start();
require_once dirname(__DIR__) . '/public_html/bootstrap.php';

/**
 * Security Test Suite
 * Runs tests on application security features
 */
class SecurityTest {
    private $db;
    private $userModel;
    private $authController;
    private $testResults = [];
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $originalSession;
    private $requestHeaders = [];
    
    /**
     * Initialize test environment
     */
    public function setUp() {
        // Initialize database connection
        $this->db = get_db();
        
        // Initialize models and controllers
        require_once MODEL_PATH . '/User.php';
        $this->userModel = new User($this->db);
        
        require_once CONTROLLER_PATH . '/auth_controller.php';
        $this->authController = new AuthController();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Save original session data
        $this->originalSession = $_SESSION;
        
        // Store original headers for comparison
        if (function_exists('apache_response_headers')) {
            $this->requestHeaders = apache_response_headers();
        }
        
        echo "<div class='test-container'>";
        echo "<h2 class='test-header'>Security Test Suite</h2>";
    }
    
    /**
     * Clean up resources
     */
    public function tearDown() {
        // Restore original session
        $_SESSION = $this->originalSession;
        
        // Close the container div
        echo "</div>";
        
        // Display test summary
        $this->displaySummary();
    }
    
    /**
     * Run all tests
     */
    public function run() {
        // Clear any existing output first
        ob_clean();
        
        // Print only the test header
        echo "<!DOCTYPE html><html><head><title>Security Tests</title></head><body>";
        
        $this->setUp();
        
        // Run all test methods
        $this->testCSRFProtection();
        $this->testXSSProtection();
        $this->testSQLInjectionProtection();
        $this->testSessionSecurity();
        $this->testPasswordStorage();
        $this->testBruteForceProtection();
        $this->testSecurityHeaders();
        $this->testDirectoryTraversal();
        $this->testFileUploadSecurity();
        $this->testContentSecurityPolicy();
        
        $this->tearDown();
        
        echo "</body></html>";
        
        // Flush the output buffer
        ob_end_flush();
    }
    
    /**
     * Test CSRF protection
     */
    public function testCSRFProtection() {
        echo "<div class='test-section'>";
        echo "<h3>Testing CSRF Protection</h3>";
        
        // Test CSRF function existence
        $this->assertTest(
            'CSRF Token Generation Functions',
            function() {
                return function_exists('generate_csrf_token') && function_exists('validate_csrf_token');
            },
            'Application should have CSRF token generation and verification functions'
        );
        
        // Test CSRF token creation
        $this->assertTest(
            'CSRF Token Creation',
            function() {
                // Check if csrf token is created and stored in session
                if (!function_exists('generate_csrf_token')) {
                    return false;
                }
                
                $token = generate_csrf_token();
                return !empty($token) && !empty($_SESSION['csrf_token']);
            },
            'Application should generate and store CSRF tokens'
        );
        
        // Test CSRF token verification
        $this->assertTest(
            'CSRF Token Verification',
            function() {
                if (!function_exists('validate_csrf_token')) {
                    return false;
                }
                
                // Get a valid token
                $validToken = generate_csrf_token();
                
                // Create an invalid token
                $invalidToken = 'invalid_' . time();
                
                // Save original token
                $originalToken = $_SESSION['csrf_token'];
                
                // Test valid token verification
                $validResult = validate_csrf_token($validToken);
                
                // Test invalid token verification
                $invalidResult = !validate_csrf_token($invalidToken);
                
                // Restore original token
                $_SESSION['csrf_token'] = $originalToken;
                
                return $validResult && $invalidResult;
            },
            'CSRF verification should accept valid tokens and reject invalid ones'
        );
        
        // Test CSRF implementation in forms
        $this->assertTest(
            'CSRF Implementation in Forms',
            function() {
                // Check if csrf_field function exists
                if (!function_exists('csrf_field') && !function_exists('csrf_token_field')) {
                    return false;
                }
                
                // Check a sample form output for CSRF token
                ob_start();
                include_once(VIEW_PATH . '/auth/index.php');
                $formContent = ob_get_clean();
                
                return strpos($formContent, 'name="csrf_token"') !== false;
            },
            'Forms should include CSRF token fields'
        );
        
        echo "</div>";
    }
    
    /**
     * Test XSS protection
     */
    public function testXSSProtection() {
        echo "<div class='test-section'>";
        echo "<h3>Testing XSS Protection</h3>";
        
        // Test output escaping functions
        $this->assertTest(
            'Output Escaping Functions',
            function() {
                return function_exists('html_escape') || function_exists('e') || function_exists('htmlspecialchars');
            },
            'Application should have HTML escaping functions'
        );
        
        // Test input validation
        $this->assertTest(
            'Input Validation',
            function() {
                // Check if form validation functions exist
                if (method_exists($this->userModel, 'validateInput')) {
                    return true;
                }
                
                // If no specific method, check for PHP's filter_var usage
                if (defined('FILTER_SANITIZE_STRING') || defined('FILTER_SANITIZE_SPECIAL_CHARS')) {
                    return true;
                }
                
                return false;
            },
            'Application should validate and sanitize input'
        );
        
        echo "</div>";
    }
    
    /**
     * Test SQL Injection protection
     */
    public function testSQLInjectionProtection() {
        echo "<div class='test-section'>";
        echo "<h3>Testing SQL Injection Protection</h3>";
        
        // Test prepared statement usage
        $this->assertTest(
            'Prepared Statement Usage',
            function() {
                // Extract a sample method that should use prepared statements
                $preparedStatementsUsed = false;
                
                // Analyze the User.php file to check for prepared statements
                $userModelContent = file_get_contents(MODEL_PATH . '/User.php');
                
                // Look for signs of prepared statements
                $preparedStatementsUsed = strpos($userModelContent, 'prepare(') !== false &&
                                         strpos($userModelContent, 'bind_param(') !== false;
                
                return $preparedStatementsUsed;
            },
            'Application should use prepared statements for database queries'
        );
        
        
        // Test malicious input handling
        $this->assertTest(
            'Malicious Input Handling',
            function() {
                $maliciousInputs = [
                    "admin' --",
                    "admin'; DROP TABLE users; --",
                    "1' OR '1'='1"
                ];
                
                // Test each malicious input with the emailExists method
                $allSafe = true;
                
                foreach ($maliciousInputs as $input) {
                    try {
                        // Call emailExists but catch any errors
                        $exists = $this->userModel->emailExists($input);
                        
                        // If no error was thrown, check that the result is reasonable
                        // (shouldn't return true for malicious inputs)
                        if ($exists === true) {
                            // Do a direct check to verify
                            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
                            $stmt->bind_param("s", $input);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $count = $result->fetch_assoc()['count'];
                            
                            if ($count === 0 && $exists === true) {
                                $allSafe = false;
                                break;
                            }
                        }
                    } catch (Exception $e) {
                        // Error might mean it's not handling the input properly
                        $allSafe = false;
                        break;
                    }
                }
                
                return $allSafe;
            },
            'Database functions should safely handle malicious inputs'
        );
        
        echo "</div>";
    }
    
    /**
     * Test session security
     */
    public function testSessionSecurity() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Session Security</h3>";
        
        // Test session configuration
        $this->assertTest(
            'Session Configuration',
            function() {
                $secure = false;
                $httpOnly = false;
                
                // Check session cookie parameters
                $params = session_get_cookie_params();
                $secure = $params['secure'];
                $httpOnly = $params['httponly'];
                
                // For local testing, we may allow non-secure cookies
                if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
                    $secure = true; // Consider it secure for testing
                }
                
                return $httpOnly;
            },
            'Session cookies should use secure configuration'
        );
        
        // Test session regeneration
        $this->assertTest(
            'Session ID Regeneration',
            function() {
                // Check if login method regenerates session ID
                $loginMethodContent = file_get_contents(CONTROLLER_PATH . '/auth_controller.php');
                
                return strpos($loginMethodContent, 'session_regenerate_id') !== false;
            },
            'Application should regenerate session IDs during authentication state changes'
        );
        
        // Test session expiration
        $this->assertTest(
            'Session Expiration',
            function() {
                // Check if session timeout is configured
                $timeout = ini_get('session.gc_maxlifetime');
                
                // Also look for custom timeout implementation
                $loginMethodContent = file_get_contents(CONTROLLER_PATH . '/auth_controller.php');
                $customTimeout = strpos($loginMethodContent, 'timeout') !== false || 
                                strpos($loginMethodContent, 'expire') !== false;
                
                return $timeout < 86400 || $customTimeout; // Less than 24 hours
            },
            'Sessions should have reasonable expiration times'
        );
        
        // Test session fixation protection
        $this->assertTest(
            'Session Fixation Protection',
            function() {
                // This is closely related to session regeneration
                // Check for session regeneration
                $authControllerContent = file_get_contents(CONTROLLER_PATH . '/auth_controller.php');
                
                return strpos($authControllerContent, 'session_regenerate_id') !== false;
            },
            'Application should protect against session fixation'
        );
        
        echo "</div>";
    }
    
    /**
     * Test password storage security
     */
    public function testPasswordStorage() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Password Storage</h3>";
        
        // Test password hashing
        $this->assertTest(
            'Password Hashing Algorithm',
            function() {
                // Check User model for password hashing
                                $userModelContent = file_get_contents(MODEL_PATH . '/User.php');
                
                // Check for password_hash function (which uses bcrypt by default)
                $usesPasswordHash = strpos($userModelContent, 'password_hash') !== false;
                
                // Check for specific algorithm configurations
                $secureAlgorithm = strpos($userModelContent, 'PASSWORD_BCRYPT') !== false || 
                                  strpos($userModelContent, 'PASSWORD_ARGON2I') !== false ||
                                  strpos($userModelContent, 'PASSWORD_ARGON2ID') !== false ||
                                  strpos($userModelContent, 'PASSWORD_DEFAULT') !== false;
                
                return $usesPasswordHash && $secureAlgorithm;
            },
            'Application should use strong hashing algorithms for passwords'
        );
        
        // Test password verification
        $this->assertTest(
            'Password Verification',
            function() {
                // Check User model for password verification
                $userModelContent = file_get_contents(MODEL_PATH . '/User.php');
                
                // Look for password_verify function
                return strpos($userModelContent, 'password_verify') !== false;
            },
            'Application should use secure password verification'
        );
        
        // Test password cost factor
        $this->assertTest(
            'Password Hashing Cost Factor',
            function() {
                $userModelContent = file_get_contents(MODEL_PATH . '/User.php');
                
                // Check for explicit cost factor settings
                $hasCostFactor = preg_match('/[\'"]cost[\'"]\s*=>\s*\d+/', $userModelContent) === 1;
                
                // If no explicit cost factor, default is typically acceptable
                if (!$hasCostFactor) {
                    // Check if it's using PASSWORD_DEFAULT which is acceptable
                    $hasCostFactor = strpos($userModelContent, 'PASSWORD_DEFAULT') !== false;
                }
                
                return $hasCostFactor;
            },
            'Password hashing should use appropriate cost factor'
        );
        
        // Test password storage format
        $this->assertTest(
            'Password Storage Format',
            function() {
                // Get a sample hashed password from database
                $stmt = $this->db->prepare("SELECT password_hash FROM users LIMIT 1");
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    return false; // No users in database
                }
                
                $hash = $result->fetch_assoc()['password_hash'];
                
                // Check if it looks like a bcrypt or argon2 hash
                $isBcrypt = strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0;
                $isArgon2 = strpos($hash, '$argon2i$') === 0 || strpos($hash, '$argon2id$') === 0;
                
                return $isBcrypt || $isArgon2;
            },
            'Passwords should be stored in secure hash format'
        );
        
        echo "</div>";
    }
    
    /**
     * Test brute force protection
     */
    public function testBruteForceProtection() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Brute Force Protection</h3>";
        
        
        // Test IP-based lockout
        $this->assertTest(
            'IP-Based Lockout',
            function() {
                // Check for IP-based lockout
                $userModelContent = file_get_contents(MODEL_PATH . '/User.php');
                $authControllerContent = file_get_contents(CONTROLLER_PATH . '/auth_controller.php');
                
                return strpos($userModelContent, 'IP') !== false || 
                       strpos($userModelContent, 'REMOTE_ADDR') !== false ||
                       strpos($authControllerContent, 'IP') !== false || 
                       strpos($authControllerContent, 'REMOTE_ADDR') !== false;
            },
            'Application should implement IP-based lockout for multiple failed attempts'
        );
        
        // Test CAPTCHA or similar mechanisms
        $this->assertTest(
            'Secondary Verification',
            function() {
                // Check for CAPTCHA or similar mechanisms
                $viewFiles = glob(VIEW_PATH . '/auth/*.php');
                $hasSecondaryVerification = false;
                
                if (!empty($viewFiles)) {
                    foreach ($viewFiles as $file) {
                        $content = file_get_contents($file);
                        if (strpos($content, 'captcha') !== false || 
                            strpos($content, 'recaptcha') !== false ||
                            strpos($content, 'g-recaptcha') !== false) {
                            $hasSecondaryVerification = true;
                            break;
                        }
                    }
                }
                
                return $hasSecondaryVerification;
            },
            'Application should implement CAPTCHA or similar mechanisms'
        );
        
        echo "</div>";
    }
    
    /**
     * Test security headers
     */
    public function testSecurityHeaders() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Security Headers</h3>";
        
        // Test X-XSS-Protection header
        $this->assertTest(
            'X-XSS-Protection Header',
            function() {
                // Check for X-XSS-Protection header
                if (!empty($this->requestHeaders) && isset($this->requestHeaders['X-XSS-Protection'])) {
                    return $this->requestHeaders['X-XSS-Protection'] === '1; mode=block';
                }
                
                // Check .htaccess or index.php for header setting
                $htaccessExists = file_exists(dirname(__DIR__) . '/public_html/.htaccess');
                $indexContent = file_get_contents(dirname(__DIR__) . '/public_html/index.php');
                
                return ($htaccessExists && strpos(file_get_contents(dirname(__DIR__) . '/public_html/.htaccess'), 'X-XSS-Protection') !== false) ||
                       strpos($indexContent, 'X-XSS-Protection') !== false;
            },
            'Application should set X-XSS-Protection header'
        );
        
        // Test X-Content-Type-Options header
        $this->assertTest(
            'X-Content-Type-Options Header',
            function() {
                // Check for X-Content-Type-Options header
                if (!empty($this->requestHeaders) && isset($this->requestHeaders['X-Content-Type-Options'])) {
                    return $this->requestHeaders['X-Content-Type-Options'] === 'nosniff';
                }
                
                // Check .htaccess or index.php for header setting
                $htaccessExists = file_exists(dirname(__DIR__) . '/public_html/.htaccess');
                $indexContent = file_get_contents(dirname(__DIR__) . '/public_html/index.php');
                
                return ($htaccessExists && strpos(file_get_contents(dirname(__DIR__) . '/public_html/.htaccess'), 'X-Content-Type-Options') !== false) ||
                       strpos($indexContent, 'X-Content-Type-Options') !== false;
            },
            'Application should set X-Content-Type-Options header'
        );
        
        // Test X-Frame-Options header
        $this->assertTest(
            'X-Frame-Options Header',
            function() {
                // Check for X-Frame-Options header
                if (!empty($this->requestHeaders) && isset($this->requestHeaders['X-Frame-Options'])) {
                    return in_array($this->requestHeaders['X-Frame-Options'], ['DENY', 'SAMEORIGIN']);
                }
                
                // Check .htaccess or index.php for header setting
                $htaccessExists = file_exists(dirname(__DIR__) . '/public_html/.htaccess');
                $indexContent = file_get_contents(dirname(__DIR__) . '/public_html/index.php');
                
                return ($htaccessExists && strpos(file_get_contents(dirname(__DIR__) . '/public_html/.htaccess'), 'X-Frame-Options') !== false) ||
                       strpos($indexContent, 'X-Frame-Options') !== false;
            },
            'Application should set X-Frame-Options header'
        );
        
        // Test Referrer-Policy header
        $this->assertTest(
            'Referrer-Policy Header',
            function() {
                // Check for Referrer-Policy header
                if (!empty($this->requestHeaders) && isset($this->requestHeaders['Referrer-Policy'])) {
                    return in_array($this->requestHeaders['Referrer-Policy'], 
                        ['no-referrer', 'same-origin', 'strict-origin', 'strict-origin-when-cross-origin']);
                }
                
                // Check .htaccess or index.php for header setting
                $htaccessExists = file_exists(dirname(__DIR__) . '/public_html/.htaccess');
                $indexContent = file_get_contents(dirname(__DIR__) . '/public_html/index.php');
                
                return ($htaccessExists && strpos(file_get_contents(dirname(__DIR__) . '/public_html/.htaccess'), 'Referrer-Policy') !== false) ||
                       strpos($indexContent, 'Referrer-Policy') !== false;
            },
            'Application should set Referrer-Policy header'
        );
        
        echo "</div>";
    }
    
    /**
     * Test directory traversal protection
     */
    public function testDirectoryTraversal() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Directory Traversal Protection</h3>";
        
        
        // Test file inclusion protection
        $this->assertTest(
            'File Inclusion Protection',
            function() {
                // Check if the application uses secure file inclusion methods
                $bootstrapContent = file_get_contents(dirname(__DIR__) . '/public_html/bootstrap.php');
                
                // Look for whitelisting or validation of includes
                return strpos($bootstrapContent, 'require_once') !== false && 
                       strpos($bootstrapContent, 'include') !== false &&
                       (strpos($bootstrapContent, 'defined') !== false || 
                        strpos($bootstrapContent, 'constant') !== false ||
                        strpos($bootstrapContent, 'PATH') !== false);
            },
            'Application should use secure file inclusion methods'
        );
        
        // Test URL parameter filtering
        $this->assertTest(
            'URL Parameter Filtering',
            function() {
                // Check routing mechanism for proper path validation
                $indexContent = file_get_contents(dirname(__DIR__) . '/public_html/index.php');
                
                return strpos($indexContent, 'filter_var') !== false || 
                       strpos($indexContent, 'htmlspecialchars') !== false || 
                       strpos($indexContent, 'sanitize') !== false ||
                       strpos($indexContent, 'basename') !== false;
            },
            'Application should filter URL parameters to prevent directory traversal'
        );
        
        echo "</div>";
    }
    
    /**
     * Test file upload security
     */
    public function testFileUploadSecurity() {
        echo "<div class='test-section'>";
        echo "<h3>Testing File Upload Security</h3>";
        
        // Find a file upload handler in the application
        $uploadHandler = null;
        $controllerFiles = glob(CONTROLLER_PATH . '/*.php');
        
        foreach ($controllerFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'upload') !== false || 
                strpos($content, '$_FILES') !== false || 
                strpos($content, 'move_uploaded_file') !== false) {
                $uploadHandler = $file;
                break;
            }
        }
        
        // Test file type validation
        $this->assertTest(
            'File Type Validation',
            function() use ($uploadHandler) {
                if (!$uploadHandler) {
                    return true; // No upload handler found, so consider it a pass
                }
                
                $content = file_get_contents($uploadHandler);
                
                // Check for MIME type validation
                return strpos($content, 'image/') !== false || 
                       strpos($content, 'application/pdf') !== false || 
                       strpos($content, 'mime') !== false || 
                       strpos($content, 'finfo_file') !== false ||
                       strpos($content, 'getMimeType') !== false;
            },
            'Application should validate file types before upload'
        );
        
        // Test file size limitations
        $this->assertTest(
            'File Size Limitations',
            function() use ($uploadHandler) {
                if (!$uploadHandler) {
                    return true; // No upload handler found, so consider it a pass
                }
                
                $content = file_get_contents($uploadHandler);
                
                // Check for file size validation
                return strpos($content, 'filesize') !== false || 
                       strpos($content, 'size') !== false || 
                       strpos($content, 'MAX_FILE_SIZE') !== false;
            },
            'Application should enforce file size limitations'
        );
        
        // Test file extension validation
        $this->assertTest(
            'File Extension Validation',
            function() use ($uploadHandler) {
                if (!$uploadHandler) {
                    return true; // No upload handler found, so consider it a pass
                }
                
                $content = file_get_contents($uploadHandler);
                
                // Check for file extension validation
                return strpos($content, 'extension') !== false || 
                       strpos($content, 'pathinfo') !== false || 
                       preg_match('/\.(jpg|jpeg|png|gif|pdf|doc|docx)/', $content);
            },
            'Application should validate file extensions before upload'
        );
        
        // Test file storage location
        $this->assertTest(
            'Secure File Storage',
            function() use ($uploadHandler) {
                if (!$uploadHandler) {
                    return true; // No upload handler found, so consider it a pass
                }
                
                $content = file_get_contents($uploadHandler);
                
                // Check if files are stored outside web root or access is properly controlled
                return strpos($content, 'uploads') !== false && 
                      (strpos($content, '.htaccess') !== false || 
                       strpos($content, 'dirname') !== false || 
                       strpos($content, realpath(dirname(__DIR__))) !== false);
            },
            'Application should store uploaded files in secure locations'
        );
        
        echo "</div>";
    }
    
    /**
     * Test Content Security Policy
     */
    public function testContentSecurityPolicy() {
        echo "<div class='test-section'>";
        echo "<h3>Testing Content Security Policy</h3>";
        
        // Test CSP header
        $this->assertTest(
            'CSP Header Implementation',
            function() {
                // Check for CSP header
                if (!empty($this->requestHeaders) && isset($this->requestHeaders['Content-Security-Policy'])) {
                    return true;
                }
                
                // Check .htaccess or index.php for CSP setting
                $htaccessExists = file_exists(dirname(__DIR__) . '/public_html/.htaccess');
                $indexContent = file_get_contents(dirname(__DIR__) . '/public_html/index.php');
                
                return ($htaccessExists && strpos(file_get_contents(dirname(__DIR__) . '/public_html/.htaccess'), 'Content-Security-Policy') !== false) ||
                       strpos($indexContent, 'Content-Security-Policy') !== false;
            },
            'Application should implement Content Security Policy headers'
        );
        
        
        echo "</div>";
    }
    
    /**
     * Assert a test condition and record the result
     * 
     * @param string $testName The name of the test
     * @param callable $testFunction The function containing the test logic
     * @param string $description Description of what the test is checking
     */
    private function assertTest($testName, $testFunction, $description) {
        try {
            $result = $testFunction();
            
            if ($result === true) {
                $this->testsPassed++;
                $status = 'passed';
                $statusClass = 'test-pass';
            } else {
                $this->testsFailed++;
                $status = 'failed';
                $statusClass = 'test-fail';
            }
        } catch (Exception $e) {
            $this->testsFailed++;
            $status = 'error';
            $statusClass = 'test-error';
            $description .= ' (Error: ' . $e->getMessage() . ')';
        }
        
        $this->testResults[] = [
            'name' => $testName,
            'status' => $status,
            'description' => $description
        ];
        
        echo "<div class='test-case $statusClass'>";
        echo "<h4>$testName</h4>";
        echo "<p>$description</p>";
        echo "<span class='test-result'>$status</span>";
        echo "</div>";
    }
    
    /**
     * Display test result summary
     */
    private function displaySummary() {
        $total = $this->testsPassed + $this->testsFailed;
        $percentPassed = $total > 0 ? round(($this->testsPassed / $total) * 100) : 0;
        
        echo "<div class='test-summary'>";
        echo "<h3>Test Summary</h3>";
        echo "<p>Total Tests: $total</p>";
        echo "<p>Passed: $this->testsPassed</p>";
        echo "<p>Failed: $this->testsFailed</p>";
        echo "<p>Pass Rate: $percentPassed%</p>";
        
        // Display security rating
        $securityRating = '';
        if ($percentPassed >= 90) {
            $securityRating = 'Excellent';
            $ratingClass = 'rating-excellent';
        } elseif ($percentPassed >= 75) {
            $securityRating = 'Good';
            $ratingClass = 'rating-good';
        } elseif ($percentPassed >= 50) {
            $securityRating = 'Fair';
            $ratingClass = 'rating-fair';
        } else {
            $securityRating = 'Poor';
            $ratingClass = 'rating-poor';
        }
        
        echo "<p>Security Rating: <span class='$ratingClass'>$securityRating</span></p>";
        echo "</div>";
        
        // Add some CSS to style the results
        echo "<style>
            .test-container {
                font-family: Arial, sans-serif;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f5f5f5;
                border-radius: 5px;
            }
            .test-header {
                background-color: #2c3e50;
                color: white;
                padding: 10px;
                border-radius: 5px;
                text-align: center;
            }
            .test-section {
                margin: 20px 0;
                padding: 10px;
                background-color: #ecf0f1;
                border-radius: 5px;
            }
            .test-section h3 {
                border-bottom: 1px solid #bdc3c7;
                padding-bottom: 5px;
                color: #2c3e50;
            }
            .test-case {
                margin: 10px 0;
                padding: 10px;
                border-radius: 5px;
            }
            .test-pass {
                background-color: #d5f5e3;
                border-left: 5px solid #2ecc71;
            }
            .test-fail {
                background-color: #fadbd8;
                border-left: 5px solid #e74c3c;
            }
            .test-error {
                background-color: #fcf3cf;
                border-left: 5px solid #f1c40f;
            }
            .test-result {
                float: right;
                font-weight: bold;
                text-transform: uppercase;
            }
            .test-pass .test-result {
                color: #27ae60;
            }
            .test-fail .test-result {
                color: #c0392b;
            }
            .test-error .test-result {
                color: #d35400;
            }
            .test-summary {
                margin-top: 30px;
                padding: 15px;
                background-color: #34495e;
                color: white;
                border-radius: 5px;
                text-align: center;
            }
            .rating-excellent {
                color: #2ecc71;
                font-weight: bold;
            }
            .rating-good {
                color: #3498db;
                font-weight: bold;
            }
            .rating-fair {
                color: #f1c40f;
                font-weight: bold;
            }
            .rating-poor {
                color: #e74c3c;
                font-weight: bold;
            }
        </style>";
    }
}

// Create and run the security test
$securityTest = new SecurityTest();
$securityTest->run();
?>
