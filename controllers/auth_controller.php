<?php
class AuthController {
    private $db;
    private $userModel;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
        
        // Initialize the User model
        require_once MODEL_PATH . '/User.php';
        $this->userModel = new User($this->db);
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
                        // Set session variables
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['logged_in'] = true;
                        
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
                    $success .= " <a href='$verifyUrl'>Verify now</a> (for demonstration only)";                } else {
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
     * Handle user logout
     */
    public function logout() {
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
        
        // Redirect to login page
        header('Location: ' . base_url('index.php/auth'));        
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
                
                // Redirect based on role
                switch ($_SESSION['role']) {
                    case 'admin':
                        header('Location: ' . base_url('index.php/admin'));
                        break;
                    case 'provider':
                        header('Location: ' . base_url('index.php/provider'));
                        break;
                    default: // patient
                        header('Location: ' . base_url('index.php/appointments'));
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
    private function redirectBasedOnRole($role) {
        switch ($role) {
            case 'admin':
                header('Location: ' . base_url('index.php/admin'));
                break;
            case 'provider':
                header('Location: ' . base_url('index.php/provider'));
                break;
            default: // patient
                header('Location: ' . base_url('index.php/appointments'));
                break;
        }
        exit;
    }
    
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
                    $userData = $this->userModel->getUserById($userId);                } else {
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
