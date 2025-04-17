<?php
require_once MODEL_PATH . '/Appointment.php';
require_once MODEL_PATH . '/Provider.php';

class AppointmentsController {
    private $db;
    private $appointmentModel;
    private $providerModel;
    
    // Updated constructor to better handle database connection
    public function __construct() {
        // Get database connection from bootstrap
        $this->db = get_db();
        
        // Check connection type and ensure models are compatible
        if ($this->db instanceof mysqli) {
            // Using MySQLi - ensure models are initialized correctly
            error_log("Using MySQLi connection in AppointmentsController");
        } elseif ($this->db instanceof PDO) {
            // Using PDO - ensure models are initialized correctly
            error_log("Using PDO connection in AppointmentsController");
        } else {
            error_log("Unknown database connection type: " . get_class($this->db));
        }
        
        // Initialize models with the connection
        $this->appointmentModel = new Appointment($this->db);
        $this->providerModel = new Provider($this->db);
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function index() {
        error_log("APPOINTMENT controller index method called - this should appear if routing is correct");
        
        // Debug the database connection type
        if ($this->db instanceof mysqli) {
            error_log("Using MySQLi connection in AppointmentsController");
        } elseif ($this->db instanceof PDO) {
            error_log("Using PDO connection in AppointmentsController");
        } else {
            error_log("Unknown database connection type in AppointmentsController");
        }
        
        // Get available slots
        try {
            $availableSlots = $this->providerModel->getAvailableSlots();
            error_log("Successfully retrieved available slots: " . count($availableSlots));
            
            // Make sure provider_name is set for each slot
            foreach ($availableSlots as &$slot) {
                if (!isset($slot['provider_name'])) {
                    // Get provider name if not already set
                    $providerId = $slot['provider_id'];
                    // Query to get provider name
                    if ($this->db instanceof mysqli) {
                        $query = "SELECT CONCAT(first_name, ' ', last_name) as provider_name 
                                  FROM users WHERE user_id = ?";
                        $stmt = $this->db->prepare($query);
                        $stmt->bind_param("i", $providerId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $slot['provider_name'] = $row['provider_name'];
                        } else {
                            $slot['provider_name'] = 'Unknown Provider';
                        }
                    } else {
                        // Default for when query fails
                        $slot['provider_name'] = 'Provider #' . $providerId;
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Error getting available slots: " . $e->getMessage());
            $availableSlots = [];
        }
        
        // Get user's appointments if logged in
        $appointments = [];
        try {
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                $userRole = $_SESSION['role'] ?? 'patient';
                
                if ($userRole == 'patient') {
                    $appointments = $this->appointmentModel->getByPatient($userId);
                } elseif ($userRole == 'provider') {
                    $appointments = $this->appointmentModel->getByProvider($userId);
                }
            }
        } catch (Exception $e) {
            error_log("Error getting appointments: " . $e->getMessage());
        }
        
        error_log("Available slots: " . count($availableSlots));
        error_log("Appointments: " . count($appointments));
        
        // Include the view
        include VIEW_PATH . '/appointments/index.php';
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $patient_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $availability_id = $_POST['availability_id'] ?? 0;
            
            // Validate input
            if (!$availability_id) {
                header("Location: /index.php?page=appointments&error=invalid_input");
                exit;
            }
            
            // Check if patient is logged in
            if (!$patient_id) {
                header("Location: /index.php?page=auth/login");
                exit;
            }
            
            // Check if slot is already booked
            try {
                if ($this->appointmentModel->isSlotBooked($availability_id)) {
                    // Redirect with error message
                    header("Location: /index.php?page=appointments&error=slot_booked");
                    exit;
                }
                
                // Create the appointment
                if ($this->appointmentModel->create($patient_id, $availability_id)) {
                    header("Location: /index.php?page=appointments&success=1");
                } else {
                    header("Location: /index.php?page=appointments&error=create_failed");
                }
            } catch (Exception $e) {
                error_log("Error in appointment creation: " . $e->getMessage());
                header("Location: /index.php?page=appointments&error=system_error");
            }
            exit;
        }
        
        // If not a POST request, redirect to appointments page
        header("Location: /index.php?page=appointments");
        exit;
    }
}
?>
