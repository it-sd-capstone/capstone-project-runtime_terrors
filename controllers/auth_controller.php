<?php
require_once __DIR__ . '/../helpers/system_notifications.php';
require_once(__DIR__ . '/../services/EmailService.php');
require_once(__DIR__ . '/../config/email_config.php');
require_once(__DIR__ . '/../helpers/validation_helpers.php');

// Add reCAPTCHA configuration
if (file_exists(__DIR__ . '/../config/recaptcha_config.php')) {
    require_once(__DIR__ . '/../config/recaptcha_config.php');
} else {
    // Use test keys if config file doesn't exist
    define('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
    define('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
}

class AuthController {
    private $db;
    private $userModel;
    private $activityLogModel;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
        
        // Initialize models
        require_once MODEL_PATH . '/User.php';
        $this->userModel = new User($this->db);
        
        require_once MODEL_PATH . '/ActivityLog.php';
        $this->activityLogModel = new ActivityLog($this->db);
    }
    
    // private function setError($message) {
        // set_flash_message('error', $message, 'global');
    // }
    
    /**
     * Set error message in session
     */
    private function setErrorMessage($message) {
    set_flash_message('error', $message, 'global');
    }

    /**
     * Set success message in session
     */
    private function setSuccessMessage($message) {
    set_flash_message('success', $message, 'global');
    }

    /**
     * Load view with data
     */
    private function loadView($viewPath, $data = []) {
        extract($data);
        include VIEW_PATH . '/' . $viewPath . '.php';
    }

    /**
     * Validate CSRF token
     * @return bool True if token is valid, false otherwise
     */
    private function validateCsrfToken() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->setErrorMessage('Invalid CSRF token');
            header('Location: ' . base_url('index.php/auth/login'));
            exit; // Important to stop execution
        }
        return true;
    }

    
    public function index() {
        // Display login form
        include VIEW_PATH . '/auth/index.php';
    }
    
   public function login() {
        $error = '';
        $errors = [];
        $resent = false;
        // Check if verification email was requested to be resent
        if (isset($_GET['resend']) && isset($_GET['email'])) {
            $email = $this->sanitizeInput($_GET['email']);
            $resent = $this->resendVerificationEmail($email);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            if (!verify_csrf_token()) {
                return;
            }
            error_log("Login attempt for email: " . ($_POST['email'] ?? 'none'));
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            // Basic validation
            if (empty($email)) {
                $errors[] = "Email is required";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            }
            if (empty($password)) {
                $errors[] = "Password is required";
            }
            // If basic validation passes, attempt authentication
            if (empty($errors)) {
                error_log("Attempting to authenticate user with email: $email");
                try {
                    // Load user model
                    $userModel = new User($this->db);
                    
                    // Get user regardless of active status
                    $stmt = null;
                    if ($this->db instanceof mysqli) {
                        $stmt = $this->db->prepare("SELECT user_id, email, password_hash, first_name, last_name, role, 
                                password_change_required, email_verified_at, is_verified, is_active
                                FROM users 
                                WHERE email = ?");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                    } elseif ($this->db instanceof PDO) {
                        $stmt = $this->db->prepare("SELECT user_id, email, password_hash, first_name, last_name, role, 
                                password_change_required, email_verified_at, is_verified, is_active
                                FROM users
                                WHERE email = :email");
                        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                        $stmt->execute();
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    
                    if ($user) {
                        // Check if account is deactivated
                        if (isset($user['is_active']) && $user['is_active'] == 0) {
                            $errors[] = "This account has been deactivated. Please contact administration for assistance.";
                            $this->activityLogModel->logAuth($user['user_id'], 'login_failed', "Account deactivated: $email");
                        }
                        // Check password (defensive: ensure hash exists)
                        elseif (!isset($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
                            $errors[] = "Invalid password. Please try again.";
                            $this->activityLogModel->logAuth($user['user_id'], 'login_failed', "Invalid password: $email");
                        }
                        // Check if email is verified
                        elseif (empty($user['email_verified_at']) && ($user['is_verified'] ?? 0) == 0) {
                            $errors[] = "Your email address has not been verified. Please check your email for the verification link or
                                <a href='" . base_url('index.php/auth/login?resend=1&email=' . urlencode($email)) . "'>click here</a> to resend the verification email.";
                            // Log verification needed
                            $this->activityLogModel->logAuth($user['user_id'], 'login_failed', "Email not verified: $email");
                            // Include the view and exit
                            include VIEW_PATH . '/auth/index.php';
                            return;
                        }
                        // Check if password change is required
                        elseif (isset($user['password_change_required']) && $user['password_change_required'] == 1) {
                            // Store user ID in session for password change
                            $_SESSION['temp_user_id'] = $user['user_id'];
                            // Redirect to password change page
                            header('Location: ' . base_url('index.php/auth/change_password'));
                            exit;
                        }
                        // Successful login
                        else {
                            // After authentication succeeds but before redirect:
                            $loginUpdate = $this->userModel->updateLastLogin($user['user_id']);
                            if ($loginUpdate && isset($loginUpdate['timestamp'])) {
                                // Store the login timestamp in the session for validation
                                $_SESSION['login_timestamp'] = strtotime($loginUpdate['timestamp']);
                            }
                            // Set session variables
                            $_SESSION['user_id'] = $user['user_id'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['logged_in'] = true;
                            // Log the successful login
                            $this->activityLogModel->logAuth($user['user_id'], 'login_success');
                            // Redirect based on role
                            switch ($user['role']) {
                                case 'admin':
                                    header('Location: ' . base_url('index.php/home'));
                                    break;
                                case 'provider':
                                    header('Location: ' . base_url('index.php/home'));
                                    break;
                                default:
                                    header('Location: ' . base_url('index.php/home'));
                            }
                            exit;
                        }
                    } else {
                        $errors[] = "No account found with that email address. Create an account or please contact administration for assistance";
                        $this->activityLogModel->logAuth(null, 'login_failed', "Email: $email");
                    }
                } catch (Exception $e) {
                    // Log system event
                    logSystemEvent('system_error', 'A system error occurred: ' . $e->getMessage() . '', 'System Error Detected');
                    error_log("Login error: " . $e->getMessage());
                    $error = "An error occurred during login. Please try again.";
                }
            }
        }
        // Pass the resend status to the view
        $data = [
            'errors' => $errors,
            'error' => $error,
            'resent' => $resent
        ];
        extract($data);
        include VIEW_PATH . '/auth/index.php';
    }

    /**
     * Resend verification email
     *
     * @param string $email User email
     * @return boolean Success flag
     */
    private function resendVerificationEmail($email) {
        // Use User model to get user by email
        $user = $this->userModel->getUserByEmail($email);
        
        if (!$user) {
            return false; // Email doesn't exist
        }
        
        // Check if already verified
        if (!empty($user['email_verified_at']) || ($user['is_verified'] ?? 0) == 1) {
            return false; // Already verified
        }
        
        // Generate new verification token
        $token = bin2hex(random_bytes(32));
        $tokenExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Update token using User model
        $updated = $this->userModel->updateVerificationToken($user['user_id'], $token, $tokenExpires);
        
        if (!$updated) {
            return false; // Failed to update token
        }
        
        // Send verification email
        $emailService = new EmailService();
        $fullName = $user['first_name'] . ' ' . $user['last_name'];
        $emailSent = $emailService->sendVerificationEmail($user['email'], $fullName, $token);
        
        // Log the activity
        if (method_exists($this, 'activityLogModel')) {
            $this->activityLogModel->logAuth($user['user_id'], 'verification_resent',
                "Verification email resent during login attempt, Status: " . ($emailSent ? "Success" : "Failed"));
        }
        
        return $emailSent;
    }

    
    
    public function register() {
        $old = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? ''
        ];
        
        error_log("Register method called");
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        $error = '';
        $success = '';
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("POST data: " . print_r($_POST, true));
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Capitalize first letter of first and last name before validation
            $firstName = ucfirst(strtolower($_POST['first_name'] ?? ''));
            $lastName = ucfirst(strtolower($_POST['last_name'] ?? ''));
            
            $phone = $_POST['phone'] ?? '';
            $role = 'patient'; // Default role for new users
            
            // Validate first name
            $firstNameValidation = validateName($firstName);
            if (!$firstNameValidation['valid']) {
                $errors[] = $firstNameValidation['error'];
            } else {
                $firstName = $firstNameValidation['sanitized'];
                // Ensure capitalization after sanitization
                $firstName = ucfirst(strtolower($firstName));
            }
            
            // Validate last name
            $lastNameValidation = validateName($lastName);
            if (!$lastNameValidation['valid']) {
                $errors[] = $lastNameValidation['error'];
            } else {
                $lastName = $lastNameValidation['sanitized'];
                // Ensure capitalization after sanitization
                $lastName = ucfirst(strtolower($lastName));
            }
            
            // Basic field validation
            if (empty($email)) {
                $errors[] = "Email address is required";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            }
            
            if (empty($firstName)) {
                $errors[] = "First name is required";
            }
            
            if (empty($lastName)) {
                $errors[] = "Last name is required";
            }
            
            // Check if email already exists
            if (!empty($email) && $this->userModel->emailExists($email)) {
                $errors[] = "Email address is already registered";
            }

            // Password validation - collect all errors
            if (empty($password)) {
                $errors[] = "Password is required";
            } else {
                if (strlen($password) < 8) {
                    $errors[] = "Password must be at least 8 characters long";
                }
                if (!preg_match('/[A-Z]/', $password)) {
                    $errors[] = "Password must contain at least one uppercase letter";
                }
                if (!preg_match('/[a-z]/', $password)) {
                    $errors[] = "Password must contain at least one lowercase letter";
                }
                if (!preg_match('/[0-9]/', $password)) {
                    $errors[] = "Password must contain at least one number";
                }
                if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                    $errors[] = "Password must contain at least one special character";
                }
            }

            if ($password !== $confirmPassword) {
                $errors[] = "Passwords do not match";
            }

            if (!isset($_POST['terms'])) {
                $errors[] = "You must agree to the Terms of Service";
            }

            // Validate reCAPTCHA (only if the constant is defined)
            if (defined('RECAPTCHA_SECRET_KEY') && defined('RECAPTCHA_SITE_KEY')) {
                if (empty($_POST['g-recaptcha-response'])) {
                    $errors[] = "Please complete the reCAPTCHA verification";
                } else {
                    // Verify reCAPTCHA response
                    $recaptchaResponse = $_POST['g-recaptcha-response'];
                    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
                    $recaptchaData = [
                        'secret' => RECAPTCHA_SECRET_KEY,
                        'response' => $recaptchaResponse,
                        'remoteip' => $_SERVER['REMOTE_ADDR']
                    ];
                    
                    // Use cURL for better error handling and security
                    $ch = curl_init($recaptchaUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($recaptchaData));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify SSL
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout
                    
                    $recaptchaResult = curl_exec($ch);
                    $curlError = curl_error($ch);
                    curl_close($ch);
                    
                    if ($recaptchaResult) {
                        $recaptchaData = json_decode($recaptchaResult);
                        
                        if (!isset($recaptchaData->success) || !$recaptchaData->success) {
                            $errors[] = "reCAPTCHA verification failed. Please try again.";
                            
                            // Log the error with error codes if available
                            if (isset($recaptchaData->{'error-codes'}) && is_array($recaptchaData->{'error-codes'})) {
                                error_log("reCAPTCHA error codes: " . implode(', ', $recaptchaData->{'error-codes'}));
                            }
                        }
                    } else {
                        error_log("Failed to connect to reCAPTCHA service: " . $curlError);
                        $errors[] = "Could not verify reCAPTCHA. Please try again later.";
                    }
                }
            }


            // Only proceed if no errors
            if (empty($errors)) {
                // Hash password before registration
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Register user with hashed password
                $result = $this->userModel->register($email, $passwordHash, $firstName, $lastName, $phone, $role);
                
                if (isset($result['user_id'])) {
                    // Generate verification token and store it
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    // Store verification token
                    $stmt = $this->db->prepare("UPDATE users SET verification_token = ?, token_expires = ? WHERE user_id = ?");
                    $stmt->bind_param("ssi", $token, $expires, $result['user_id']);
                    $stmt->execute();
                    
                    // Create verification URL
                    $verifyUrl = base_url("index.php/auth/verify?token=$token");
                    
                    // Send verification email using EmailService
                    $emailService = new EmailService();
                    $fullName = $firstName . ' ' . $lastName;
                    $emailSent = $emailService->sendVerificationEmail($email, $fullName, $token);
                    
                    // Set success message
                    $success = 'Registration successful! Please check your email to verify your account.';
                    
                    // For development environment only, show the verification link directly
                    // if (ENVIRONMENT === 'development') {
                    //     $success .= " <a href='$verifyUrl'>Verify now</a> (for development only)";
                    //     error_log("Verification URL: $verifyUrl");
                    // }
                    
                    // Log whether email was sent successfully
                    error_log("Verification email " . ($emailSent ? "sent successfully" : "failed to send") . " to $email");
                    
                    // Log the registration
                    $this->activityLogModel->logAuth(null, 'registered', "New {$role} account created, verification email " . ($emailSent ? "sent" : "failed"));
                    $this->activityLogModel->logUserActivity(null, 'created', $result['user_id']);
                } else {
                    $error = $result['error'] ?? 'Registration failed';
                }
            }
        }
        
        // Display registration form
        include VIEW_PATH . '/auth/register.php';
    }
    
    /**
     * Handle email verification
     */
    public function verify() {
        // Sanitize token input for security
        $token = $this->sanitizeInput($_GET['token'] ?? '');
        $error = '';
        $success = '';
        
        if (empty($token)) {
            $error = 'No verification token provided.';
            include VIEW_PATH . '/auth/verify.php';
            return;
        }
        
        // Use User model to fetch user by verification token
        $user = $this->userModel->getUserByVerificationToken($token);
        
        // Check if token exists
        if (!$user) {
            $error = 'Invalid verification token. Please check your email and try the link again.';
            include VIEW_PATH . '/auth/verify.php';
            return;
        }
        
        // Check if account is already verified
        if (!empty($user['email_verified_at'])) {
            $success = 'Your email has already been verified. You can now log in.';
            include VIEW_PATH . '/auth/verify.php';
            return;
        }
        
        // Check if token is expired
        if (strtotime($user['token_expires']) < time()) {
            // Generate new verification token
            $newToken = bin2hex(random_bytes(32));
            $tokenExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Use User model to update token
            $updated = $this->userModel->updateVerificationToken($user['user_id'], $newToken, $tokenExpires);
            
            if ($updated) {
                // Send new verification email
                $emailService = new EmailService();
                $fullName = $user['first_name'] . ' ' . $user['last_name'];
                $emailSent = $emailService->sendVerificationEmail($user['email'], $fullName, $newToken);
                
                $error = 'Your verification link has expired. We have sent a new verification email to your address.';
                
                // Log activity
                if (method_exists($this, 'activityLogModel')) {
                    $this->activityLogModel->logAuth($user['user_id'], 'verification_resent', "New verification email sent due to expired token");
                }
            } else {
                $error = 'There was an error processing your verification. Please try again later.';
            }
            
            include VIEW_PATH . '/auth/verify.php';
            return;
        }
        
        // Mark account as verified using User model
        $success = $this->userModel->markEmailAsVerified($user['user_id']);
        
        if ($success) {
            // Log the verification
            if (method_exists($this, 'activityLogModel')) {
                $this->activityLogModel->logAuth($user['user_id'], 'verified', "Email successfully verified");
                $this->activityLogModel->logUserActivity($user['user_id'], 'verified_email', $user['user_id']);
            }
            
            $success = 'Your email has been successfully verified. Thank you for being a valued customer. You can now log in to your account and start using all features of our system.';
        } else {
            $error = 'The verification link may have expired or is invalid. Please contact support or try resending the verification email.';
        }
        
        include VIEW_PATH . '/auth/verify.php';
    }

    
    /**
     * Helper function to sanitize input
     */
    private function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Display forgot password form
     */
    public function forgot_password() {
        $this->loadView('auth/forgot_password');
    }

    /**
     * Process forgot password request
     */
    public function forgot_password_process() {
        // Validate CSRF token
        $this->validateCsrfToken();
        
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        
        if (empty($email)) {
            $this->setErrorMessage('Email address is required.');
            header('Location: ' . base_url('index.php/auth/forgot_password'));
            exit; // Important to add exit after header redirect
        }
        
        // Use existing User model method to handle password reset request
        // This method already checks if email exists, generates token and updates database
        $resetRequest = $this->userModel->requestPasswordReset($email);
        
        // Always show success message even if email doesn't exist (security best practice)
        if (!$resetRequest) {
            $this->setSuccessMessage('If your email is registered, you will receive instructions to reset your password.');
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        // Get user data for logging
        $user = $this->userModel->getUserByEmail($email);
        
        if ($user) {
            // Send password reset email
            $emailService = new EmailService();
            $fullName = $user['first_name'] . ' ' . $user['last_name'];
            $emailSent = $emailService->sendPasswordResetEmail($email, $fullName, $resetRequest['token']);
            
            // Log the activity
            if (method_exists($this, 'logActivity')) {
                $this->logActivity($user['user_id'], 'password_reset_request',
                        "Password reset requested, Email sent: " . ($emailSent ? 'Yes' : 'No'));
            }
            
            // Set success message
            $this->setSuccessMessage('Password reset instructions have been sent to your email.');
            
            // // For development, also show the link directly
            // if (ENVIRONMENT === 'development') {
            //     $resetUrl = base_url("index.php/auth/reset_password?token=" . $resetRequest['token']);
            //     $this->setSuccessMessage('For development: <a href="' . $resetUrl . '">Reset password now</a>');
            // }
        }
        
        header('Location: ' . base_url('index.php/auth/login'));
        exit;
    }



    /**
     * Display reset password form
     */
    public function reset_password() {
        $token = $this->sanitizeInput($_GET['token'] ?? '');
        
        if (empty($token)) {
            $this->setErrorMessage('Invalid reset token.');
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        error_log("Processing reset password request with token: " . $token);
        
        // Check if token exists and is valid, using the correct column names
        $stmt = $this->db->prepare("
            SELECT user_id, token_expires 
            FROM users
            WHERE verification_token = ?
        ");
        
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        error_log("Token query returned " . $result->num_rows . " rows");
        
        if ($result->num_rows === 0) {
            $this->setErrorMessage('Invalid reset token.');
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        $user = $result->fetch_assoc();
        
        error_log("User data: " . json_encode($user));
        
        // Check if token is expired with NULL safety
        if (empty($user['token_expires']) || strtotime($user['token_expires']) < time()) {
            $this->setErrorMessage('Reset token has expired. Please request a new one.');
            header('Location: ' . base_url('index.php/auth/forgot_password'));
            exit;
        }
        
        // Load reset password view with token
        $data = ['token' => $token];
        $this->loadView('auth/reset_password', $data);
    }


    /**
     * Process password reset form submission
     */
    public function reset_password_process() {
        // Add debug logging
        error_log("Reset password process started");
        // Validate CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->setErrorMessage('Invalid CSRF token');
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        $token = $this->sanitizeInput($_GET['token'] ?? '');
        $password = $this->sanitizeInput($_POST['password'] ?? '');
        $confirmPassword = $this->sanitizeInput($_POST['confirm_password'] ?? '');
        
        error_log("Processing password reset with token: " . $token);
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $this->setErrorMessage('All fields are required');
            $this->loadView('auth/reset_password', ['token' => $token]);
            return;
        }
        
        // Validate password
        if ($password !== $confirmPassword) {
            $this->setErrorMessage('Passwords do not match');
            $this->loadView('auth/reset_password', ['token' => $token]);
            return;
        }
        
        // Validate password complexity with inline validation
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || 
            !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || 
            !preg_match('/[^A-Za-z0-9]/', $password)) {
            $this->setErrorMessage('Password must be at least 8 characters and include uppercase, lowercase, number, and special character');
            $this->loadView('auth/reset_password', ['token' => $token]);
            return;
        }
        
        // Verify token and get user
        $stmt = $this->db->prepare("
            SELECT user_id, token_expires
            FROM users
            WHERE verification_token = ?
        ");
        
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $this->setErrorMessage('Invalid reset token');
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        $user = $result->fetch_assoc();
        
        // Check if token is expired
        if (empty($user['token_expires']) || strtotime($user['token_expires']) < time()) {
            $this->setErrorMessage('Reset token has expired. Please request a new one.');
            header('Location: ' . base_url('index.php/auth/forgot_password'));
            exit;
        }
        
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update the password in the database
        $updateStmt = $this->db->prepare("
            UPDATE users
            SET password_hash = ?, verification_token = NULL, token_expires = NULL
            WHERE user_id = ?
        ");
        
        $updateStmt->bind_param("si", $hashedPassword, $user['user_id']);
        $updateResult = $updateStmt->execute();
        
        error_log("Password update result: " . ($updateResult ? 'success' : 'failed'));
        
        if ($updateResult) {
            $this->setSuccessMessage('Your password has been updated successfully. You can now log in with your new password.');
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        } else {
            $this->setErrorMessage('An error occurred. Please try again.');
            $this->loadView('auth/reset_password', ['token' => $token]);
        }
    }

    /**
     * Helper method to get user ID by reset token
     * (This is needed because after reset, the token is cleared)
     */
    private function getUserIdByResetToken($token) {
        try {
            $stmt = $this->db->prepare("
                SELECT user_id FROM users 
                WHERE reset_token = ?
            ");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                return $user['user_id'];
            }
            return null;
        } catch (Exception $e) {
            error_log("Error getting user ID by reset token: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Handle forced password change for new accounts and regular password changes
     */
    public function change_password() {
        $error = '';
        $success = '';
        
        // Check if user is logged in or has a temporary user ID
        $userId = $_SESSION['user_id'] ?? $_SESSION['temp_user_id'] ?? null;
        
        if (!$userId) {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validate passwords
            if (empty($newPassword)) {
                $error = 'New password is required';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Passwords do not match';
            } else {
                // For temporary users (first login), no password verification needed
                if (isset($_SESSION['temp_user_id'])) {
                    // Update password without verification
                    $this->updateUserPassword($_SESSION['temp_user_id'], $newPassword, $error, $success);
                } else {
                    // Regular users must verify current password
                    $currentPassword = $_POST['current_password'] ?? '';
                    
                    if (empty($currentPassword)) {
                        $error = 'Current password is required';
                    } else {
                        // Verify current password
                        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE user_id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        
                        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                            $error = 'Current password is incorrect';
                        } else {
                            // Current password verified, proceed with update
                            $this->updateUserPassword($userId, $newPassword, $error, $success);
                        }
                    }
                }
            }
        }
        
        include VIEW_PATH . '/auth/change_password.php';
    }
    /**
     * Helper method to update user password
     */
    private function updateUserPassword($userId, $newPassword, &$error, &$success) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, password_change_required = 0 WHERE user_id = ?");
        $stmt->bind_param("si", $passwordHash, $userId);
        
        if ($stmt->execute()) {
            // If this was a temporary login, create a proper session now
            if (isset($_SESSION['temp_user_id'])) {
                // Get user details for session
                $userStmt = $this->db->prepare("SELECT user_id, email, first_name, last_name, role FROM users WHERE user_id = ?");
                $userStmt->bind_param("i", $userId);
                $userStmt->execute();
                $result = $userStmt->get_result();
                $user = $result->fetch_assoc();
                
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                // Remove temporary user ID
                unset($_SESSION['temp_user_id']);
                
                // Redirect based on role - all go to home except admin
                switch ($_SESSION['role']) {
                    case 'admin':
                        header('Location: ' . base_url('index.php/home'));
                        break;
                    default: // provider and patient both go to home
                        header('Location: ' . base_url('index.php/home'));
                        break;
                }
                exit;
            } else {
                $success = 'Password changed successfully';
            }
        } else {
            $error = 'Failed to update password';
        }
    }

   /**
     * Handle user logout
     */
    public function logout() {
        // Add this at the beginning:
        if (isset($_SESSION['user_id'])) {
            // Update the last_login timestamp to invalidate other sessions
            $userModel = new User($this->db);
            $currentTime = date('Y-m-d H:i:s');
            $userModel->updateLastLogin($_SESSION['user_id'], $currentTime);
            
            // Log the logout
            $this->activityLogModel->logAuth($_SESSION['user_id'], 'logout');
        }
        
        // Destroy the session
        $_SESSION = array();
        
        // If a session cookie is used, destroy it
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        // Add a flash message to show on home page
        set_flash_message('success', 'You have been logged out successfully.', 'global');
        
        // Redirect to home page instead of login page
        header('Location: ' . base_url('index.php/home'));
        
        exit;
    }

    /**
     * Demo function to allow easy role switching for testing
     */
    public function demo() {
        $role = $_GET['role'] ?? '';
        
        if ($role && in_array($role, ['patient', 'provider', 'admin'])) {
            // Get a demo user with the requested role
            $stmt = $this->db->prepare("SELECT user_id, email, first_name, last_name, role 
                                   FROM users WHERE role = ? AND is_active = 1 LIMIT 1");
            $stmt->bind_param("s", $role);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Set session variables directly - no password check needed for demo
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                $_SESSION['demo'] = true;
                
                // Redirect based on role - all go to home except admin
                switch ($_SESSION['role']) {
                    case 'admin':
                        header('Location: ' . base_url('index.php/home'));
                        break;

                    default: // provider and patient both go to home
                        header('Location: ' . base_url('index.php/home'));

//                     case 'provider':
//                         header('Location: ' . base_url('index.php/provider'));
//                         break;
//                     default: // patient
//                         header('Location: ' . base_url('index.php/appointments'));

                        break;
                }
                exit;
            }
        }
        
        // If we get here, role not found
        header('Location: ' . base_url('index.php/auth'));
        exit;
    }
    
    /**
    * Redirect user based on their role
    */
//     private function redirectBasedOnRole($role) {
//         switch ($role) {
//             case 'admin':
//                 header('Location: ' . base_url('index.php/admin'));
//                 break;
//             case 'provider':
//                 header('Location: ' . base_url('index.php/provider'));
//                 break;
//             default: // patient
//                 header('Location: ' . base_url('index.php/appointments'));
//                 break;
//         }
//         exit;
//     }
    
    /**
     * Handle account settings page
     */
    public function settings() {
        // Check if user is logged in
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        $error = '';
        
        $success = '';
        
        // Get user data
        $userData = $this->userModel->getUserById($userId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'update_profile') {
                // Update profile information
                $updateData = [
                    'first_name' => $_POST['first_name'] ?? $userData['first_name'],
                    'last_name' => $_POST['last_name'] ?? $userData['last_name'],
                    'phone' => $_POST['phone'] ?? $userData['phone']
                ];
                
                $result = $this->userModel->updateUser($userId, $updateData);
                
                if ($result === true) {
                    $success = 'Profile updated successfully.';
                    // Update session name
                    $_SESSION['name'] = $updateData['first_name'] . ' ' . $updateData['last_name'];
                    // Refresh user data
                    $userData = $this->userModel->getUserById($userId);
                } else {
                    $error = $result['error'] ?? 'Failed to update profile.';
                }
            } elseif ($action === 'change_password') {
                // Update password
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    $error = 'All password fields are required.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'New passwords do not match.';
                } else {
                    $result = $this->userModel->changePassword($userId, $currentPassword, $newPassword);
                    
                    if ($result === true) {
                        $success = 'Password changed successfully.';
                    } else {
                        $error = $result['error'] ?? 'Failed to change password.';
                    }
                }
            }
        }
        
        // Display settings page
        include VIEW_PATH . '/auth/settings.php';

    }
}
?>