<?php
/**
 * CSRF Protection Test
 * 
 * Tests that CSRF protection is properly implemented and working
 */

// Define application path constants if not already defined
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__)); // Go up one level from tests directory
}

echo "<h1>CSRF Protection Test</h1>";

// Test CSRF token generation
function testTokenGeneration() {
    echo "<h2>Testing CSRF Token Generation</h2>";
    
    $token1 = generate_csrf_token();
    echo "<p>Generated token: " . substr($token1, 0, 10) . "...</p>";
    
    if (!empty($token1) && strlen($token1) >= 32) {
        echo "<p style='color:green'>✓ Token generation successful</p>";
    } else {
        echo "<p style='color:red'>✗ Token generation failed</p>";
    }
    
    // Check token persistence
    $token2 = generate_csrf_token();
    if ($token1 === $token2) {
        echo "<p style='color:green'>✓ Token persistence working</p>";
    } else {
        echo "<p style='color:red'>✗ Token persistence failed</p>";
    }
}

// Test CSRF token validation
function testTokenValidation() {
    echo "<h2>Testing CSRF Token Validation</h2>";
    
    $token = generate_csrf_token();
    
    // Valid token test
    if (validate_csrf_token($token)) {
        echo "<p style='color:green'>✓ Valid token accepted</p>";
    } else {
        echo "<p style='color:red'>✗ Valid token rejected</p>";
    }
    
    // Invalid token test
    $fake_token = bin2hex(random_bytes(32));
    if (!validate_csrf_token($fake_token)) {
        echo "<p style='color:green'>✓ Invalid token rejected</p>";
    } else {
        echo "<p style='color:red'>✗ Invalid token accepted</p>";
    }
}

// Test critical forms for CSRF token inclusion
function testCriticalForms() {
    echo "<h2>Testing Critical Forms for CSRF Field</h2>";
    
    // Define paths to critical form files to check with correct paths
    $criticalForms = [
        APP_ROOT . '/views/auth/register.php',
        APP_ROOT . '/views/auth/index.php',
        APP_ROOT . '/views/auth/forgot_password.php',
        APP_ROOT . '/views/auth/reset_password.php',
        APP_ROOT . '/views/patient/book.php',
        APP_ROOT . '/views/provider/notifications.php',
        APP_ROOT . '/views/admin/add_provider.php',
        APP_ROOT . '/views/admin/edit_appointment.php',
        APP_ROOT . '/views/admin/user_edit.php'
    ];
    
    $pass = 0;
    $fail = 0;
    $missing = 0;
    
    foreach ($criticalForms as $formPath) {
        if (file_exists($formPath)) {
            $formContent = file_get_contents($formPath);
            $hasCSRF = (strpos($formContent, 'csrf_token') !== false || 
                        strpos($formContent, 'csrf_field()') !== false);
            
            echo "<p>Form {$formPath}: ";
            if ($hasCSRF) {
                echo "<span style='color:green'>✓ CSRF field found</span></p>";
                $pass++;
            } else {
                echo "<span style='color:red'>✗ CSRF field NOT found</span></p>";
                $fail++;
            }
        } else {
            echo "<p>Form {$formPath}: <span style='color:orange'>⚠ File not found</span></p>";
            $missing++;
        }
    }
    
    echo "<p>Summary: <span style='color:green'>{$pass} forms with CSRF</span>, ";
    echo "<span style='color:red'>{$fail} forms missing CSRF</span>, ";
    echo "<span style='color:orange'>{$missing} files not found</span></p>";
}

// Test critical controllers for CSRF validation
function testControllerValidation() {
    echo "<h2>Testing Critical Controllers for CSRF Validation</h2>";
    
    // Define paths to critical controller files to check with correct paths
    $criticalControllers = [
        APP_ROOT . '/controllers/auth_controller.php',
        APP_ROOT . '/controllers/patient_controller.php',
        APP_ROOT . '/controllers/provider_controller.php',
        APP_ROOT . '/controllers/admin_controller.php',
        APP_ROOT . '/controllers/appointments_controller.php'
    ];
    
    $pass = 0;
    $fail = 0;
    $missing = 0;
    
    foreach ($criticalControllers as $controllerPath) {
        if (file_exists($controllerPath)) {
            $controllerContent = file_get_contents($controllerPath);
            $hasValidation = (strpos($controllerContent, 'verify_csrf_token') !== false);
            
            echo "<p>Controller {$controllerPath}: ";
            if ($hasValidation) {
                echo "<span style='color:green'>✓ CSRF validation found</span></p>";
                $pass++;
            } else {
                echo "<span style='color:red'>✗ CSRF validation NOT found</span></p>";
                $fail++;
            }
        } else {
            echo "<p>Controller {$controllerPath}: <span style='color:orange'>⚠ File not found</span></p>";
            $missing++;
        }
    }
    
    echo "<p>Summary: <span style='color:green'>{$pass} controllers with validation</span>, ";
    echo "<span style='color:red'>{$fail} controllers missing validation</span>, ";
    echo "<span style='color:orange'>{$missing} files not found</span></p>";
}

// Run the tests
testTokenGeneration();
testTokenValidation();
testCriticalForms();
testControllerValidation();

// Manual testing instructions
echo "<h2>Manual Testing Instructions</h2>";
echo "<p>For complete verification, manually test these critical submission paths:</p>";
echo "<ol>";
echo "<li>User Registration - should fail without CSRF token</li>";
echo "<li>User Login - should fail without CSRF token</li>";
echo "<li>Appointment Booking - should fail without CSRF token</li>";
echo "<li>Appointment Rescheduling - should fail without CSRF token</li>";
echo "<li>Provider Service Management - should fail without CSRF token</li>";
echo "<li>Admin User Management - should fail without CSRF token</li>";
echo "</ol>";

echo "<h2>Security Considerations</h2>";
echo "<ul>";
echo "<li>CSRF tokens should be unique per session</li>";
echo "<li>CSRF tokens should have adequate entropy (at least 32 bytes)</li>";
echo "<li>Validation should use constant-time comparison (hash_equals)</li>";
echo "<li>Tokens should expire after some time (implemented in helper)</li>";
echo "<li>All state-changing operations should have CSRF protection</li>";
echo "</ul>";

echo "<p>All tests completed to verify CSRF protection.</p>";