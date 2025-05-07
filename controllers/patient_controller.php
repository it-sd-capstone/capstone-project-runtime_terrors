<?php
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/Appointment.php';
require_once MODEL_PATH . '/Services.php';
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/ActivityLog.php';
// Add at top of patient_controller.php, before any redirects
error_log('SESSION DATA: ' . print_r($_SESSION, true));

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
        $this->serviceModel = new Services($this->db);
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
                    error_log("Book action received with parameters: " . print_r($_GET, true));
                    if ($provider_id && $appointment_date && $appointment_time) {
                        error_log("Attempting to book appointment: provider=$provider_id, date=$appointment_date, time=$appointment_time");
                        if (!$this->appointmentModel->isSlotAvailable($provider_id, $appointment_date, $appointment_time)) {
                            $_SESSION['error'] = "This time slot is unavailable.";
                            header("Location: " . base_url("index.php/patient_services?action=book"));
                            exit;
                        }
                        $success = $this->appointmentModel->scheduleAppointment($patient_id, $provider_id, $service_id, $appointment_date, $start_time, $end_time);
                        error_log("Booking result: " . ($success ? "Success" : "Failed"));
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
    /**
     * Display a provider's profile
     * 
     * @param int $id Provider ID
     * @return void
     */
    public function view_provider($id = null) {
        // Check if ID is provided
        if (!$id) {
            $_SESSION['error'] = 'Provider ID is required';
            header("Location: " . base_url("index.php/patient/search"));
            exit;
        }
        
        // Load necessary models
        require_once MODEL_PATH . '/provider.php';
        require_once MODEL_PATH . '/services.php';
        
        // Create model instances
        $providerModel = new Provider($this->db);
        $serviceModel = new Services($this->db);
        
        // Get provider details
        $provider = $providerModel->getById($id);
        
        if (!$provider) {
            $_SESSION['error'] = 'Provider not found';
            header("Location: " . base_url("index.php/patient/search"));
            exit;
        }
        
        // Get provider services using the Provider class method
        $services = $providerModel->getServices($id);
        
        // Get provider availability
        $availability = $providerModel->getAvailability($id);
        
        // Set up data for the view
        $data = [
            'provider' => $provider,
            'services' => $services,
            'availability' => $availability,
            'page_title' => 'Provider Profile'
        ];
        
        include VIEW_PATH . '/patient/view_provider.php';
    }
    

    public function book() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
            redirect('auth/login?redirect=patient/book');
            return;
        }
        
        // Add debugging for providers
        error_log("===== DEBUG: BOOKING APPOINTMENT =====");
        
        // Get all active providers
        $providers = $this->providerModel->getAll();
        
        // Debug the providers array
        error_log("Found " . count($providers) . " total providers from providerModel->getAll()");
        foreach ($providers as $index => $provider) {
            error_log("Provider[$index]: ID: " . ($provider['user_id'] ?? 'missing') . 
                     ", Name: " . ($provider['first_name'] ?? 'missing') . " " . 
                     ($provider['last_name'] ?? 'missing'));
        }
        
        // Get all services - Using getAllServices() instead of getAll()
        $services = $this->serviceModel->getAllServices();
        error_log("Found " . count($services) . " services from serviceModel->getAllServices()");
        
        // Debug the session data
        error_log("Current user: ID=" . $_SESSION['user_id'] . ", Role=" . $_SESSION['role']);
        
        // Add debugging to check SQL query directly - if providers array is empty
        if (empty($providers)) {
            $db = get_db();
            $result = $db->query("
                SELECT u.user_id, u.first_name, u.last_name, u.role
                FROM users u
                WHERE u.role = 'provider' AND u.is_active = 1
            ");
            $direct_providers = $result->fetch_all(MYSQLI_ASSOC);
            error_log("Direct SQL query found " . count($direct_providers) . " providers");
            foreach ($direct_providers as $p) {
                error_log("Direct SQL: Provider ID: {$p['user_id']}, Name: {$p['first_name']} {$p['last_name']}");
            }
        }
        
        // Load the booking view, not the home view
        $page_title = 'Book Appointment'; // Making the variable directly available for the view
        
        // Direct include instead of using view() function
        include VIEW_PATH . '/patient/book.php';
    }
    

    /**
     * Check provider availability before booking
     */
    public function processBooking() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // Verify CSRF token
            if (!verify_csrf_token()) {
                return;
            }
            
            $patient_id = $_SESSION['user_id'];
            $provider_id = intval($_POST['provider_id']);
            $service_id = intval($_POST['service_id'] ?? 0);
            $appointment_date = htmlspecialchars($_POST['appointment_date']);
            $appointment_time = htmlspecialchars($_POST['start_time']); // Changed to match form field name
            $type = htmlspecialchars($_POST['type'] ?? 'in_person');
            $notes = htmlspecialchars($_POST['notes'] ?? '');
            $reason = htmlspecialchars($_POST['reason'] ?? '');
            
            if ($provider_id && !empty($appointment_date) && !empty($appointment_time)) {
                // Check if this slot is already booked
                if (!$this->appointmentModel->isSlotAvailable($provider_id, $appointment_date, $appointment_time)) {
                    $_SESSION['error'] = "This time slot is already booked. Please select another time.";
                    header("Location: " . base_url("index.php/patient/book?provider_id=" . $provider_id));
                    exit;
                }

                // Check if patient already has an appointment at this time
                $existingAppointment = $this->appointmentModel->getPatientAppointmentAtTime(
                    $patient_id, 
                    $appointment_date, 
                    $appointment_time
                );

                if ($existingAppointment) {
                    $_SESSION['error'] = "You already have another appointment scheduled at this time.";
                    header("Location: " . base_url("index.php/patient/book?provider_id=" . $provider_id));
                    exit;
                }

                // Calculate end time based on service duration
                $service = $this->serviceModel->getServiceById($service_id);
                $duration = $service ? $service['duration'] : 30; // Default to 30 minutes
                $end_time = date('H:i:s', strtotime($appointment_time . ' +' . $duration . ' minutes'));
                
                $appointment_date = date('Y-m-d', strtotime($appointment_date));
                $appointment_time = date('H:i:s', strtotime($appointment_time));
                
                error_log("processBooking called with POST data: " . print_r($_POST, true));
                error_log("About to schedule appointment: patient=$patient_id, provider=$provider_id, service=$service_id, date=$appointment_date, time=$appointment_time");
                $success = $this->appointmentModel->scheduleAppointment(
                    $patient_id,
                    $provider_id,
                    $service_id,
                    $appointment_date,
                    $appointment_time,
                    $end_time,
                    $type,
                    $notes,
                    $reason
                );
                error_log("Appointment scheduling result: " . ($success ? "Success" : "Failed"));
                
                if ($success) {
                    // Log the appointment creation
                    $this->activityLogModel->logActivity('appointment_created',
                        "Patient scheduled appointment with provider #$provider_id",
                        $patient_id);
                           
                    // Redirect to appointments page with success message
                    $_SESSION['success'] = "Your appointment has been booked successfully!";
                    // Use the correct path format for cross-controller redirection
                    $redirectUrl = base_url("index.php/appointments?success=booked");
                    error_log("Redirecting to: " . $redirectUrl);
                    header("Location: " . $redirectUrl);
                    exit;
                } else {
                    $_SESSION['error'] = "Failed to book appointment. Please try again.";
                    header("Location: " . base_url("index.php/patient/book?provider_id=" . $provider_id));
                    exit;
                }                
                exit;
            } else {
                $_SESSION['error'] = "Missing required fields for booking.";
                header("Location: " . base_url("index.php/patient/book?provider_id=" . $provider_id));
                exit;
            }
        }
    }

    // ✅ Check Provider Availability
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
    // public function history() {
    //     $patient_id = $_SESSION['user_id'] ?? null;
    //     if (!$patient_id) {
    //         header('Location: ' . base_url('index.php/auth'));
    //         exit;
    //     }
    //     $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments($patient_id) ?? [];
    //     $pastAppointments = $this->appointmentModel->getPastAppointments($patient_id) ?? [];
    //     include VIEW_PATH . '/appointments/history.php';
    // }


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
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
            return;
        }
        
        // Retrieve all search parameters
        $searchParams = [
            'specialty' => $_GET['specialty'] ?? '',
            'location' => $_GET['location'] ?? '',
            'date' => $_GET['date'] ?? '',
            'gender' => $_GET['gender'] ?? '',
            'language' => $_GET['language'] ?? '',
            'insurance' => $_GET['insurance'] ?? ''
        ];
        
        // Get available specialties for filtering
        $specialties = $this->providerModel->getDistinctSpecializations();
        
        // Determine if search was submitted and validate
        $searchSubmitted = isset($_GET['search_submitted']);
        $hasSearchCriteria = !empty($searchParams['specialty']) || 
                             !empty($searchParams['location']) || 
                             !empty($searchParams['date']) || 
                             !empty($searchParams['gender']) || 
                             !empty($searchParams['language']) || 
                             !empty($searchParams['insurance']);
        
        // Validate search criteria
        $error = ($searchSubmitted && !$hasSearchCriteria) 
            ? "Please enter at least one search criteria."
            : ($_GET['error'] ?? null);
        
        // Initialize providers array
        $providers = [];
        $suggested_providers = [];
        
        // Only perform search if form was submitted
        if ($searchSubmitted) {
            // Fetch providers based on all search parameters
            $providers = $this->providerModel->searchProviders($searchParams);
            
            // Get suggested providers if no results found
            if (empty($providers)) {
                $suggested_providers = $this->providerModel->getSuggestedProviders();
            }
        }
        
        // Pass all variables to the view
        include VIEW_PATH . '/patient/search.php';
    }

    /**
     * Step 1: Service Selection
     * Displays a form for the user to select which service they're looking for
     */
    public function selectService() {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Please log in to book an appointment";
            redirect('auth/login');
            return;
        }
        
        // Load Services model if not already loaded
        if (!isset($this->serviceModel)) {
            require_once MODEL_PATH . '/Services.php';
            $this->serviceModel = new Services($this->db);
        }
        
        // Get all available services
        $services = $this->serviceModel->getAllServices();
        
        // Load the service selection view
        include VIEW_PATH . '/patient/select_service.php';
    }

    /**
     * Step 2: Find Providers
     * Displays providers who offer the selected service
     */
    public function findProviders() {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Please log in to book an appointment";
            redirect('auth/login');
            return;
        }
        
        // Get the selected service ID
        $service_id = $_GET['service_id'] ?? null;
        
        if (!$service_id) {
            $_SESSION['error'] = "Please select a service first";
            redirect('patient/selectService');
            return;
        }
        
        // Load models if not already loaded
        if (!isset($this->serviceModel)) {
            require_once MODEL_PATH . '/Services.php';
            $this->serviceModel = new Services($this->db);
        }
        
        if (!isset($this->providerModel)) {
            require_once MODEL_PATH . '/Provider.php';
            $this->providerModel = new Provider($this->db);
        }
        
        // Get the service details
        $service = $this->serviceModel->getById($service_id);
        
        if (!$service) {
            $_SESSION['error'] = "Service not found";
            redirect('patient/selectService');
            return;
        }
        
        // Get providers that offer this service
        $providers = $this->providerModel->getProvidersByService($service_id);
        
        // Load the provider selection view
        include VIEW_PATH . '/patient/select_provider.php';
    }
}
?>