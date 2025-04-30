<?php
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/Appointment.php';
require_once MODEL_PATH . '/Services.php';
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/ActivityLog.php';

class PatientController {
    private $db;
    private $userModel;
    private $appointmentModel;
    private $serviceModel;
    private $providerModel;
    private $activityLogModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
            header('Location: ' . base_url('index.php/auth?error=Unauthorized access'));
            exit;
        }
        $this->db = get_db();
        $this->userModel = new User($this->db);
        $this->appointmentModel = new Appointment($this->db);
        $this->serviceModel = new Service($this->db);
        $this->providerModel = new Provider($this->db);
        $this->activityLogModel = new ActivityLog($this->db);
    }
    public function index() {
        $patient_id = $_SESSION['user_id'] ?? null;
        if (!$patient_id) {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
            // Get patient details
        $userData = $this->userModel->getUserById($patient_id);
        $patientData = $this->userModel->getPatientProfile($patient_id);

        // Merge user and patient data
        $patient = array_merge($userData ?: [], $patientData ?: []);

        $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments($patient_id) ?? [];
        $pastAppointments = $this->appointmentModel->getPastAppointments($patient_id) ?? [];
        
        include VIEW_PATH . '/patient/index.php';
    }

    /**
     * Handles all patient-related appointment actions (booking, canceling, rescheduling, history)
     */
    public function processPatientAction() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $action = $_POST['action'] ?? '';

            // Common Variables
            $patient_id = $_SESSION['user_id'] ?? null;
            $appointment_id = $_POST['appointment_id'] ?? null;
            $provider_id = $_POST['provider_id'] ?? null;
            $service_id = $_POST['service_id'] ?? null;
            $appointment_date = $_POST['appointment_date'] ?? null;
            $appointment_time = $_POST['start_time'] ?? null;
            $new_date = $_POST['new_date'] ?? null;
            $new_time = $_POST['new_time'] ?? null;
            $reason = $_POST['reason'] ?? '';

            switch ($action) {
                case 'book':
                    if ($provider_id && $appointment_date && $appointment_time) {
                        if (!$this->appointmentModel->isSlotAvailable($provider_id, $appointment_date, $appointment_time)) {
                            $_SESSION['error'] = "This time slot is unavailable.";
                            header("Location: " . base_url("index.php/patient_services?action=book"));
                            exit;
                        }
                        $success = $this->appointmentModel->scheduleAppointment(
                            $patient_id, $provider_id, $service_id, $appointment_date, $appointment_time
                        );
                        $_SESSION[$success ? 'success' : 'error'] = $success ? "Appointment booked successfully!" : "Booking failed.";
                    }
                    break;

                case 'cancel':
                    if ($appointment_id) {
                        $success = $this->appointmentModel->cancelAppointment($appointment_id, $reason);
                        $_SESSION[$success ? 'success' : 'error'] = $success ? "Appointment canceled." : "Cancellation failed.";
                    }
                    break;

                case 'reschedule':
                    if ($appointment_id && $new_date && $new_time) {
                        if (!$this->appointmentModel->isSlotAvailable($provider_id, $new_date, $new_time)) {
                            $_SESSION['error'] = "Selected time slot is unavailable. Please choose another time.";
                            header("Location: " . base_url("index.php/patient_services?action=reschedule&appointment_id=$appointment_id"));
                            exit;
                        }
                        $success = $this->appointmentModel->rescheduleAppointment($appointment_id, $new_date, $new_time);
                        $_SESSION[$success ? 'success' : 'error'] = $success ? "Rescheduled successfully!" : "Rescheduling failed.";
                    }
                    break;
            }

            header("Location: " . base_url("index.php/patient_services?action=" . $action));
            exit;
        }
    }
    public function book($providerId = null) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
    
        // Get provider details if ID is provided
        $provider = $providerId ? $this->providerModel->getProviderById($providerId) : null;
        
        if ($providerId && !$provider) {
            $_SESSION['error'] = "Provider not found.";
            header('Location: ' . base_url('index.php/patient/search'));
            exit;
        }
    
        // Get all providers and services
        $providers = $this->providerModel->getAllProvidersWithDetails();
        $services = $this->serviceModel->getAllServices();
    
        // Load the booking view
        include VIEW_PATH . '/patient/book.php';
    }

    /**
     * Check provider availability before booking
     */
    public function checkAvailability() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $input = json_decode(file_get_contents("php://input"), true);
            $provider_id = intval($input['provider_id'] ?? 0);
            $appointment_date = htmlspecialchars($input['date'] ?? '');
            $appointment_time = htmlspecialchars($input['time'] ?? '');
            $available = $this->appointmentModel->isSlotAvailable($provider_id, $appointment_date, $appointment_time);
            header("Content-Type: application/json");
            echo json_encode(["available" => $available]);
            exit;
        }
    }

    /**
     * View Appointment History
     */
    public function history() {
        $patient_id = $_SESSION['user_id'] ?? null;
        if (!$patient_id) {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments($patient_id) ?? [];
        $pastAppointments = $this->appointmentModel->getPastAppointments($patient_id) ?? [];
        include VIEW_PATH . '/patient/book.php';
    }

    /**
     * Load patient profile view
     */
    public function profile() {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        $userData = $this->userModel->getUserById($user_id);
        $patientData = $this->userModel->getPatientProfile($user_id);
        $patient = array_merge($userData ?: [], $patientData ?: []);
        include VIEW_PATH . '/patient/profile.php';
    }

    /**
     * Update patient profile
     */
    public function updateProfile() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to update your profile";
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $userData = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'phone' => $_POST['phone'] ?? ''
            ];
            $this->userModel->updateUser($userId, $userData);
            $patientData = [
                'address' => $_POST['address'] ?? '',
                'emergency_contact' => $_POST['emergency_contact'] ?? '',
                'medical_conditions' => $_POST['medical_conditions'] ?? ''
            ];
            $result = $this->userModel->updatePatientProfile($userId, $patientData);
            $_SESSION[$result ? 'success' : 'error'] = $result ? "Profile updated successfully" : "Failed to update profile";
            header('Location: ' . base_url('index.php/patient/profile'));
            exit;
        }
    }
    public function search() {
        // Retrieve search parameters
        $specialty = $_GET['specialty'] ?? '';
        $location = $_GET['location'] ?? '';
    
        // Get available specialties for filtering
        $specialties = $this->providerModel->getDistinctSpecializations();
    
        // Validate search criteria
        $error = (isset($_GET['search_submitted']) && empty($specialty) && empty($location)) 
                 ? "Please enter at least one search criteria." 
                 : ($_GET['error'] ?? null);
    
        // Fetch providers based on filters
        $providers = $this->providerModel->searchProviders($specialty, $location);
    
        // Provide fallback suggested providers when no results are found
        $suggested_providers = empty($providers) ? $this->providerModel->getSuggestedProviders() : [];
    
        // Pass variables to the view
        include VIEW_PATH . '/patient/search.php';
    }
}
?>