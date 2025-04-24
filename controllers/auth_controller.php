<?php
class AuthController {
    private $db;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
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
                // Check if user exists with provided email
                $stmt = $this->db->prepare("SELECT user_id, email, password_hash, first_name, last_name, role 
                                           FROM users WHERE email = ? AND is_active = 1");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    
                    // Verify password - using simple comparison for demo
                    // In production you'd use password_verify($password, $user['password_hash'])
                    // This is just for testing purposes - INSECURE!
                    if ($password === 'demo' || $password === 'password') {
                        // Set session variables
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['logged_in'] = true;
                        
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
                    } else {
                        $error = 'Invalid password';
                    }
                } else {
                    $error = 'User not found with that email';
                }
            }
        }
        
        // If we get here, authentication failed
        include VIEW_PATH . '/auth/index.php';
    }
    
    public function logout() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Destroy the session
        $_SESSION = array();
        session_destroy();
        
        // Redirect to home page instead of login page
        header('Location: ' . base_url('index.php/home'));        
        exit;
    }
    
    // Demo function to allow easy role switching for testing
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
            }
        }
        
        // If we get here, role not found
        header('Location: ' . base_url('index.php/auth'));
        exit;
    }
}
?>