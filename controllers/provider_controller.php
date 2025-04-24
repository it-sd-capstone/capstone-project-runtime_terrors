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
        
        // Check if user is logged in and has provider or admin role
        if (!isset($_SESSION['user_id']) || 
            ($_SESSION['role'] !== 'provider' && $_SESSION['role'] !== 'admin')) {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        // Get database connection using the helper function
        $this->db = get_db();
        
        $this->providerModel = new Provider($this->db);
        $this->appointmentModel = new Appointment($this->db);
    }
    
    // Provider Dashboard
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
    
    // Upload provider availability
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

    // Fetch provider profile details
    public function profile() {
        $providerData = $this->providerModel->getProviderData($_SESSION['user_id']);
        include VIEW_PATH . '/provider/profile.php';
    }

    // Update provider profile
    public function updateProfile() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $name = trim($_POST['name']);
            $specialty = trim($_POST['specialty']);
            $bio = trim($_POST['bio']);
            if (!empty($name)) {
                $this->providerModel->updateProfile($_SESSION['user_id'], $name, $specialty, $bio);
                header('Location: ' . base_url('index.php/provider/profile?success=Profile updated'));
                exit;
            }
        }
        header('Location: ' . base_url('index.php/provider/profile?error=Invalid input'));
        exit;
    }

    // Validate appointment slot before booking
    public function validateAppointment() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_POST['provider_id'];
            $date = $_POST['appointment_date'];
            $start_time = $_POST['appointment_time'];
            $end_time = date("H:i:s", strtotime($start_time) + (30 * 60));
            
            if (!$this->providerModel->isSlotAvailable($provider_id, $date, $start_time, $end_time)) {
                header('Location: ' . base_url('index.php/patient/book?error=Slot unavailable'));
                exit;
            }
            
            header('Location: ' . base_url('index.php/patient/book?success=Slot available'));
            exit;
        }
    }

    // Provider Service Management (CRUD)
    public function services() {
        $provider_id = $_SESSION['user_id'];
        $providerServices = $this->providerModel->getServices($provider_id);
        include VIEW_PATH . '/provider/services.php';
    }

    public function addService() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $service_name = trim($_POST['service_name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            
            if (!empty($service_name)) {
                $this->providerModel->addService($provider_id, $service_name, $description, $price);
                header('Location: ' . base_url('index.php/provider/services?success=Service added'));
                exit;
            }
        }
        
        header('Location: ' . base_url('index.php/provider/services?error=Invalid input'));
        exit;
    }

    public function deleteService() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $service_id = intval($_POST['service_id']);
            
            if ($this->providerModel->deleteService($service_id, $provider_id)) {
                header('Location: ' . base_url('index.php/provider/services?success=Service deleted'));
            } else {
                header('Location: ' . base_url('index.php/provider/services?error=Failed to delete'));
            }
            exit;
        }
    }

    // Manage Recurring Schedules (CRUD)
    public function addRecurringSchedule() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $day_of_week = intval($_POST['day_of_week']);
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if (!empty($start_time) && !empty($end_time) && $end_time > $start_time) {
                $this->providerModel->addRecurringSchedule($provider_id, $day_of_week, $start_time, $end_time, $is_active);
                header('Location: ' . base_url('index.php/provider/schedule?success=Schedule added'));
                exit;
            }
        }
        
        header('Location: ' . base_url('index.php/provider/schedule?error=Invalid input'));
        exit;
    }

    public function getRecurringSchedule() {
        $provider_id = $_SESSION['user_id'];
        $recurringSchedules = $this->providerModel->getRecurringSchedule($provider_id);
        include VIEW_PATH . '/provider/schedule.php';
    }

    public function deleteRecurringSchedule() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $schedule_id = intval($_POST['schedule_id']);
            
            if ($this->providerModel->deleteRecurringSchedule($schedule_id, $provider_id)) {
                header('Location: ' . base_url('index.php/provider/schedule?success=Schedule deleted'));
            } else {
                header('Location: ' . base_url('index.php/provider/schedule?error=Failed to delete'));
            }
            exit;
        }
    }
}
?>