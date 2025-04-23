<?php
class HomeController {
    private $db;
    
    public function __construct() {
        // Initialize database connection
        $this->db = get_db();
        
        // Initialize session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Main index method for the home page
     */
    public function index() {
        // Get some basic stats for the home page
        $stats = [];
        
        // Only show provider stats if there are providers
        if ($this->getCount('users', "role = 'provider'") > 0) {
            $stats['totalProviders'] = $this->getCount('users', "role = 'provider'");
            
            // Get total number of services
            if ($this->tableExists('services')) {
                $stats['totalServices'] = $this->getCount('services');
            }
        }
        
        // Include the home page view
        include VIEW_PATH . '/home/index.php';
    }
    
    /**
     * About page for the application
     */
    public function about() {
        include VIEW_PATH . '/home/about.php';
    }
    
    /**
     * Contact page for the application
     */
    public function contact() {
        $success = '';
        $error = '';
        
        // Process contact form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            if (empty($name) || empty($email) || empty($message)) {
                $error = 'All fields are required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address';
            } else {
                // Store message in database or send email
                // This is a simplified example
                $success = 'Thank you for your message. We will contact you soon!';
            }
        }
        
        include VIEW_PATH . '/home/contact.php';
    }
    
    /**
     * Helper method to get counts from database tables
     */
    private function getCount($table, $where = '') {
        $query = "SELECT COUNT(*) as count FROM $table";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        
        $result = $this->db->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            return $row['count'];
        }
        return 0;
    }
    
    /**
     * Helper method to check if a table exists
     */
    private function tableExists($table) {
        $result = $this->db->query("SHOW TABLES LIKE '$table'");
        return $result && $result->num_rows > 0;
    }
}