<?php
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/Appointment.php';

class ProviderController {
    private $db;
    private $providerModel;
    private $appointmentModel;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
        
        // Check if user is logged in and has provider or admin role
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || 
            ($_SESSION['role'] !== 'provider' && $_SESSION['role'] !== 'admin')) {
            // Redirect to login
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        $this->providerModel = new Provider($this->db);
        $this->appointmentModel = new Appointment($this->db);
    }
    
    public function index() {
        // Debug info
        error_log("Provider controller index method called");
        
        // Set a provider ID for testing if needed
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 2; // Default to provider #2 for testing
            $_SESSION['role'] = 'provider';
            error_log("No user_id in session, setting default provider ID: 2");
        }
        
        $provider_id = $_SESSION['user_id'];
        error_log("Using provider_id: " . $provider_id);
        
        // Initialize variables to prevent undefined variable errors
        $provider_availability = [];
        $appointments = [];
        
        try {
            // Get provider availability - now with better error handling
            try {
                $provider_availability = $this->providerModel->getAvailability($provider_id);
                error_log("Successfully retrieved provider availability: " . count($provider_availability));
            } catch (Exception $e) {
                error_log("Error getting provider availability: " . $e->getMessage());
                $provider_availability = [];
            }
            
            // Get appointments using your existing Appointment model
            try {
                $appointments = $this->appointmentModel->getByProvider($provider_id);
                error_log("Successfully retrieved provider appointments: " . count($appointments));
            } catch (Exception $e) {
                error_log("Error getting provider appointments: " . $e->getMessage());
                $appointments = [];
            }
        } catch (Exception $e) {
            // Log error
            error_log("Error in provider dashboard: " . $e->getMessage());
        }
        
        // After loading data, add more debug
        error_log("Provider availability records: " . count($provider_availability));
        error_log("Appointment records: " . count($appointments));
        
        // Include the view with the variables set
        include VIEW_PATH . '/provider/index.php';
    }

    public function upload_availability() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Match the field names from your form
            $date = $_POST['available_date'] ?? '';
            $start_time = $_POST['start_time'] ?? '';
            $end_time = $_POST['end_time'] ?? '';
            
            // Get provider_id from session instead of hardcoding
            $provider_id = $_SESSION['user_id'] ?? 2;
            
            if (!empty($date) && !empty($start_time) && !empty($end_time)) {
                try {
                    $result = $this->providerModel->addAvailability($date, $start_time, $end_time, $provider_id);
                    if ($result) {
                        header('Location: ' . base_url('index.php/provider?success=1'));
                        exit;
                    } else {
                        $error = "Failed to add availability record";
                        error_log($error);
                    }
                } catch (Exception $e) {
                    $error = "Error adding availability: " . $e->getMessage();
                    error_log($error);
                }
            } else {
                $error = "Please fill out all fields";
                error_log($error);
            }
        }
        
        // You may not need a separate view file - just redirect back to provider index
        header('Location: ' . base_url('index.php/provider'));
        exit;
    }
}
?>