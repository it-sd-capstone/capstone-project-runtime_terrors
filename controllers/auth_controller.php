<?php
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
    
    private function setError($message) {
        $_SESSION['error'] = $message;
    }
    
    public function index() {
        // Display login form
        include VIEW_PATH . '/auth/index.php';
    }
    
    public function login() {
        $error = '';
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    $user = $userModel->authenticate($email, $password);
                    
                    if ($user) {
                        // Check if password change is required
                        if (isset($user['password_change_required']) && $user['password_change_required'] == 1) {
                            // Store user ID in session for password change
                            $_SESSION['temp_user_id'] = $user['user_id'];
                            
                            // Redirect to password change page
                            header('Location: ' . base_url('index.php/auth/change_password'));
                            exit;
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
                                header('Location: ' . base_url('index.php/admin'));
                                break;
                            case 'provider':
                                header('Location: ' . base_url('index.php/provider'));
                                break;
                            default:
                                header('Location: ' . base_url('index.php/appointments'));
                        }
                        exit;
                    } else {
                        // Try to be more specific about the error
                        // Check if user exists with that email
                        if ($userModel->emailExists($email)) {
                            $errors[] = "Invalid password. Please try again.";
                        } else {
                            $errors[] = "No account found with that email address.";
                        }
                        
                        // Additional helper message for demo purposes
                        $errors[] = "For testing, try the demo logins below or use 'password' as the password.";
                        
                        // Log failed login attempt
                        $this->activityLogModel->logAuth(null, 'login_failed', "Email: $email");
                    }
                } catch (Exception $e) {
                    error_log("Login error: " . $e->getMessage());
                    $error = "An error occurred during login. Please try again.";
                }
            }
        }
        
        include VIEW_PATH . '/auth/index.php';
    }
    
    public function register() {
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
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $role = 'patient'; // Default role for new users
            
            // Password validation
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
            error_log("Password validation errors: " . print_r($errors, true));
            if (count($errors) > 0) {
                // Return errors to the view
                include VIEW_PATH . '/auth/register.php';
                return;
            }
            
            // Basic validation
            if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
                $error = 'All required fields must be filled';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match';
            } else {
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
                    
                    // Send verification email
                    $verifyUrl = base_url("index.php/auth/verify?token=$token");
                    $success = 'Registration successful! Please check your email to verify your account.';
                    
                    // For demonstration purposes:
                    $success .= " <a href='$verifyUrl'>Verify now</a> (for demonstration only)";
                    
                    // Log the registration
                    $this->activityLogModel->logAuth(null, 'registered', "New {$_POST['role']} account");
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
        $token = $_GET['token'] ?? '';
        $error = '';
        $success = '';
        
        if (!empty($token)) {
            // Verify token and activate account
            $stmt = $this->db->prepare("SELECT user_id FROM users WHERE verification_token = ? AND token_expires > NOW()");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Mark account as verified
                $updateStmt = $this->db->prepare("UPDATE users SET email_verified_at = NOW(), verification_token = NULL WHERE user_id = ?");
                $updateStmt->bind_param("i", $user['user_id']);
                $updateStmt->execute();
                
                $success = 'Your email has been verified! You can now log in.';
            } else {
                $error = 'Invalid or expired verification token.';
            }
        } else {
            $error = 'No verification token provided.';
        }
        
        // Display verification result
        include VIEW_PATH . '/auth/verify.php';
    }
    
    /**
     * Handle forgot password requests
     */
    public function forgot_password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            
            // Check if email exists
            $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token
                $updateStmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?");
                $updateStmt->bind_param("ssi", $token, $expires, $user['user_id']);
                $updateStmt->execute();
                
                // Send reset email
                $resetUrl = base_url("index.php/auth/reset_password?token=$token");
                // Send email with reset link
            }
            
            // Always show success to prevent email enumeration
            $success = "If an account exists with that email, a password reset link has been sent.";
        }
        
        include VIEW_PATH . '/auth/forgot_password.php';
    }
    
    /**
     * Handle password reset
     */
    public function reset_password() {
        $token = $_GET['token'] ?? '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validate token and passwords
            if ($password === $confirmPassword) {
                $stmt = $this->db->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    
                    // Hash new password
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Update password and clear token
                    $updateStmt = $this->db->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE user_id = ?");
                    $updateStmt->bind_param("si", $passwordHash, $user['user_id']);
                    $updateStmt->execute();
                    
                    // Redirect to login with success message
                    header('Location: ' . base_url('index.php/auth?success=password_reset'));
                    exit;
                }
            }
        }
        
        include VIEW_PATH . '/auth/reset_password.php';
    }
    
    /**
     * Handle forced password change for new accounts
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
                // Update password
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
                                header('Location: ' . base_url('index.php/admin'));
                                break;
                            default: // provider and patient both go to home
                                header('Location: ' . base_url('index.php/home'));
                                break;
                        }
                        exit;

                        // Remove temporary user ID
                        //unset($_SESSION['temp_user_id']);
                        
                        // Redirect based on role
                        //$this->redirectBasedOnRole($user['role']);

                    } else {
                        $success = 'Password changed successfully';
                    }
                } else {
                    $error = 'Failed to update password';
                }
            }
        }
        
        include VIEW_PATH . '/auth/change_password.php';
    }
    
    /**
     * Handle user logout
     */
    public function logout() {
        // Add this at the beginning:
        if (isset($_SESSION['user_id'])) {
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
        
        // Redirect to home page instead of login page
        header('Location: ' . base_url('index.php/home'));        

        // Redirect to login page
        //header('Location: ' . base_url('index.php/auth'));
        
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
                        header('Location: ' . base_url('index.php/admin'));
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