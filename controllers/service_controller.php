<?php
require_once MODEL_PATH . '/Services.php';

class ServiceController {
    protected $db;
    protected $serviceModel;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
        
        // Initialize models
        $this->serviceModel = new Service($this->db);
    }
    
    /**
     * Process form submission to add a service
     */
    public function addService() {
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            if (!verify_csrf_token()) {
                return;
            }
            
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $duration = $_POST['duration'] ?? 30;
            
            // Validate inputs
            $errors = [];
            
            if (empty($name)) {
                $errors[] = "Service name is required";
            }
            
            if (empty($description)) {
                $errors[] = "Description is required";
            }
            
            if (empty($price) || !is_numeric($price)) {
                $errors[] = "Valid price is required";
            }
            
            // If no errors, insert service
            if (empty($errors)) {
                $serviceData = [
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'duration' => $duration,
                    'is_active' => 1
                ];
                
                $result = $this->serviceModel->createService($serviceData);
                
                if ($result) {
                    $_SESSION['success'] = "Service added successfully";
                    header('Location: ' . base_url('index.php/admin/services'));
                    exit;
                } else {
                    $errors[] = "Error adding service";
                }
            }
            
            // If there are errors, include them in the session
            $_SESSION['errors'] = $errors;
        }
        
        // Redirect back to services page
        header('Location: ' . base_url('index.php/admin/services'));
        exit;
    }
}