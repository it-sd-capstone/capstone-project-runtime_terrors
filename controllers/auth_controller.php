<?php
class AuthController {
    private $db;
    private $user;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
        
        // Initialize User model
        require_once MODEL_PATH . '/User.php';
        $this->user = new User($this->db);
    }
    
    public function index() {
        // Display login form
        include VIEW_PATH . '/auth/index.php';
    }
    
    public function login() {
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Basic validation
            if (empty($email) || empty($password)) {
                $error = 'Email and password are required';
            } else {
                // Authenticate user using the User model
                $user = $this->user->authenticate($email, $password);
                
                if ($user && !isset($user['error'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    
                    // Redirect based on role
                    switch ($_SESSION['role']) {
                        case 'admin':
                            header('Location: /appointment-system/capstone-project-runtime_terrors/public_html/index.php/admin');
                            break;
                        case 'provider':
                            header('Location: /appointment-system/capstone-project-runtime_terrors/public_html/index.php/provider');
                            break;
                        default: // patient
                            header('Location: /appointment-system/capstone-project-runtime_terrors/public_html/index.php/appointments');
                            break;
                    }
                    exit;
                } else {
                    $error = $user['error'] ?? 'Invalid email or password';
                }
            }
        }
        
        // If we get here, authentication failed
        include VIEW_PATH . '/auth/index.php';
    }
    
    public function register() {
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $role = 'patient'; // Default role for new users
            
            // Basic validation
            if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
                $error = 'All required fields must be filled';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match';
            } else {
                // Register user
                $result = $this->user->register($email, $password, $firstName, $lastName, $phone, $role);
                
                if (isset($result['user_id'])) {
                    // Generate verification token and send verification email
                    $token = $this->user->generateVerificationToken($result['user_id']);
                    
                    // In a real system, you would send an email with the verification link
                    // For this example, we'll just show the token in the success message
                    $success = 'Registration successful! Please check your email to verify your account.';
                    
                    // For demonstration purposes:
                    $verifyUrl = "/appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth/verify?token=$token";
                    $success .= " <a href='$verifyUrl'>Verify now</a> (for demonstration only)";
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
            $verified = $this->user->verifyEmail($token);
            
            if ($verified) {
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
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            
            if (empty($email)) {
                $error = 'Email is required';
            } else {
                $result = $this->user->requestPasswordReset($email);
                
                if ($result) {
                    // In a real system, you would send an email with the reset link
                    // For this example, we'll just show the token in the success message
                    $success = 'Password reset instructions have been sent to your email.';
                    
                    // For demonstration purposes:
                    $resetUrl = "/appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth/reset_password?token=" . $result['token'];
                    $success .= " <a href='$resetUrl'>Reset now</a> (for demonstration only)";
                } else {
                    $error = 'No account found with that email address.';
                }
            }
        }
        
        // Display forgot password form
        include VIEW_PATH . '/auth/forgot_password.php';
    }
    
    /**
     * Handle password reset
     */
    public function reset_password() {
        $token = $_GET['token'] ?? '';
        $error = '';
        $success = '';
        
        if (empty($token)) {
            $error = 'Invalid reset token.';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($password) || empty($confirmPassword)) {
                $error = 'All fields are required.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                $result = $this->user->resetPassword($token, $password);
                
                if ($result === true) {
                    $success = 'Your password has been reset successfully. You can now log in with your new password.';
                } else {
                    $error = $result['error'] ?? 'Password reset failed.';
                }
            }
        }
        
        // Display reset password form
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
        header('Location: /appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth');
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
                
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                $_SESSION['demo'] = true;
                
                // Redirect based on role
                switch ($_SESSION['role']) {
                    case 'admin':
                        header('Location: /appointment-system/capstone-project-runtime_terrors/public_html/index.php/admin');
                        break;
                    case 'provider':
                        header('Location: /appointment-system/capstone-project-runtime_terrors/public_html/index.php/provider');
                        break;
                    default: // patient
                        header('Location: /appointment-system/capstone-project-runtime_terrors/public_html/index.php/appointments');
                        break;
                }
                exit;
            }
        }
        
        // If we get here, role not found
        header('Location: /appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth');
        exit;
    }
    
    /**
     * Handle account settings page
     */
    public function settings() {
        // Check if user is logged in
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            header('Location: /appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $error = '';
        $success = '';
        
        // Get user data
        $userData = $this->user->getUserById($userId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'update_profile') {
                // Update profile information
                $updateData = [
                    'first_name' => $_POST['first_name'] ?? $userData['first_name'],
                    'last_name' => $_POST['last_name'] ?? $userData['last_name'],
                    'phone' => $_POST['phone'] ?? $userData['phone']
                ];
                
                $result = $this->user->updateUser($userId, $updateData);
                
                if ($result === true) {
                    $success = 'Profile updated successfully.';
                    // Update session name
                    $_SESSION['name'] = $updateData['first_name'] . ' ' . $updateData['last_name'];
                    // Refresh user data
                    $userData = $this->user->getUserById($userId);
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
                    $result = $this->user->changePassword($userId, $currentPassword, $newPassword);
                    
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
