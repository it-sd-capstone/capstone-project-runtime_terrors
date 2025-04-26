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

    // Load Dashboard - No changes needed
    public function index() {
        $patient_id = $_SESSION['user_id'];
        $patient = $this->userModel->getUserById($patient_id);
        $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments($patient_id);
        $pastAppointments = $this->appointmentModel->getPastAppointments($patient_id);
        
        if (!$upcomingAppointments) $upcomingAppointments = [];
        if (!$pastAppointments) $pastAppointments = [];
        
        // Debug the appointment data structure
        if (count($upcomingAppointments) > 0) {
            error_log("Appointment data keys: " . implode(", ", array_keys($upcomingAppointments[0])));
        }
        
        include VIEW_PATH . '/patient/index.php';
    }

//  AddedSarah
//     // Show Booking Form
//     public function book() {
//         // Get providers
//         $rawProviders = $this->userModel->getAvailableProviders();
        
//         // Format providers to include provider_name
//         $providers = [];
//         foreach ($rawProviders as $provider) {
//             // Make sure to preserve user_id as provider_id
//             $providers[] = [
//                 'provider_id' => $provider['user_id'], // This key was missing
//                 'provider_name' => $provider['first_name'] . ' ' . $provider['last_name'] . ' - ' . 
//                                   ($provider['title'] ?? 'Practitioner'),
//                 'specialization' => $provider['specialization'] ?? '',
//                 'title' => $provider['title'] ?? ''
//             ];
//         }
        
//         // Get services - fix the file name (Service vs Services)
//         require_once MODEL_PATH . '/Services.php'; // Changed from '/Services.php'
//         $serviceModel = new Service($this->db);
//         $services = $serviceModel->getAllServices();

    public function book($providerId = null) {
        // If provider ID is passed, get that specific provider
        $provider = null;
        if ($providerId) {
            // Try both methods to ensure compatibility
            try {
                // First try getProviderById which works from your nav
                $provider = $this->providerModel->getProviderById($providerId);
            } catch (Error $e) {
                // If that fails, try getById which might be the correct method name
                try {
                    $provider = $this->providerModel->getById($providerId);
                } catch (Error $e2) {
                    // Log the error for debugging
                    error_log("Error getting provider: " . $e->getMessage() . " and " . $e2->getMessage());
                    
                    // If both methods fail, check if we can get the provider directly from the database
                    $stmt = $this->db->prepare("SELECT * FROM users u 
                        LEFT JOIN provider_profiles pp ON u.user_id = pp.provider_id 
                        WHERE u.user_id = ? AND u.role = 'provider'");
                    $stmt->bind_param("i", $providerId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result && $result->num_rows > 0) {
                        $provider = $result->fetch_assoc();
                    }
                }
            }
            
            if (!$provider) {
                $_SESSION['error'] = "Provider not found";
                header('Location: ' . base_url('index.php/patient/search'));
                exit;
            }
        }
        
        // Get all providers for dropdown selection
        $providers = $this->providerModel->getAllProvidersWithDetails();
        
        // Get services using the initialized service model
        $services = $this->serviceModel->getAllServices();
        
        // Log the number of services found
        error_log("Found " . count($services) . " services");
        
        // If provider was found, also get their specific services
        if ($provider) {
            $providerServices = $this->providerModel->getServices($providerId);
            if (!empty($providerServices)) {
                // Replace general services with provider-specific ones if available
                $services = $providerServices;
                error_log("Found " . count($providerServices) . " provider-specific services");
            }
        }
        
        // Pass variables to the view
        include VIEW_PATH . '/patient/book.php';
    }

    public function processBooking() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
                    
                    // Redirect to patient dashboard with success message
                    $_SESSION['success'] = "Your appointment has been booked successfully!";
                    header("Location: " . base_url("index.php/patient"));
                } else {
                    $_SESSION['error'] = "Failed to book appointment. Please try again.";
                    header("Location: " . base_url("index.php/patient/book?provider_id=" . $provider_id));
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

    // Rename from rescheduleAppointment() to reschedule()
    public function reschedule($appointment_id = null) {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        // Check if appointment ID is provided
        if (!$appointment_id) {
            $_SESSION['error'] = "No appointment specified for rescheduling";
            header('Location: ' . base_url('index.php/patient/history'));
            exit;
        }
        
        // Initialize models if not already done
        if (!isset($this->appointmentModel)) {
            require_once MODEL_PATH . '/Appointment.php';
            $this->appointmentModel = new Appointment($this->db);
        }
        
        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentById($appointment_id);
        
        // Check if appointment exists and belongs to the logged-in patient
        if (!$appointment || $appointment['patient_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Invalid appointment or you don't have permission to reschedule it";
            header('Location: ' . base_url('index.php/patient/history'));
            exit;
        }
        
        // Check if appointment is in a status that can be rescheduled
        $reschedulableStatuses = ['scheduled', 'confirmed'];
        if (!in_array($appointment['status'], $reschedulableStatuses)) {
            $_SESSION['error'] = "This appointment cannot be rescheduled due to its current status";
            header('Location: ' . base_url('index.php/patient/history'));
            exit;
        }
        
        // Debug the appointment data
        error_log("Appointment data for reschedule: " . print_r($appointment, true));
        
        // Include the view
        include VIEW_PATH . '/patient/reschedule.php';
    }

    // Process Reschedule
    public function processReschedule() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Invalid request method";
            header('Location: ' . base_url('index.php/patient/history'));
            exit;
        }
        
        // Get form data
        $appointment_id = $_POST['appointment_id'] ?? null;
        $provider_id = $_POST['provider_id'] ?? null;
        $service_id = $_POST['service_id'] ?? null;
        $new_date = $_POST['new_date'] ?? null;
        $new_time = $_POST['new_time'] ?? null;
        $reason = $_POST['reason'] ?? '';
        
        // Validate required fields
        if (!$appointment_id || !$provider_id || !$service_id || !$new_date || !$new_time) {
            $_SESSION['error'] = "All required fields must be filled out";
            header('Location: ' . base_url('index.php/patient/reschedule/' . $appointment_id));
            exit;
        }
        
        // Initialize models if not already done
        if (!isset($this->appointmentModel)) {
            require_once MODEL_PATH . '/Appointment.php';
            $this->appointmentModel = new Appointment($this->db);
        }
        
        // Get the current appointment
        $appointment = $this->appointmentModel->getAppointmentById($appointment_id);
        
        // Check if appointment exists and belongs to the logged-in patient
        if (!$appointment || $appointment['patient_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Invalid appointment or you don't have permission to reschedule it";
            header('Location: ' . base_url('index.php/patient/history'));
            exit;
        }
        
        // Format date and time
        $new_date = date('Y-m-d', strtotime($new_date));
        $new_time = date('H:i:s', strtotime($new_time));
        
        // Calculate end time based on service duration
        $service = $this->serviceModel->getServiceById($service_id);
        $duration = $service ? $service['duration'] : 30; // Default to 30 minutes
        $new_end_time = date('H:i:s', strtotime($new_time . ' +' . $duration . ' minutes'));
        
        // Check if the new slot is available
        if (!$this->appointmentModel->isSlotAvailable($provider_id, $new_date, $new_time)) {
            $_SESSION['error'] = "The selected time slot is not available. Please choose another time.";
            header('Location: ' . base_url('index.php/patient/reschedule/' . $appointment_id));
            exit;
        }
        
        // Reschedule the appointment
        $success = $this->appointmentModel->rescheduleAppointment(
            $appointment_id,
            $new_date,
            $new_time,
            $new_end_time
        );
        
        if ($success) {
            // Update the reason if provided
            if (!empty($reason)) {
                $this->appointmentModel->updateAppointmentNotes($appointment_id, $reason);
            }
            
            // Log the rescheduling
            $this->activityLogModel->logActivity(
                'appointment_rescheduled',
                "Patient rescheduled appointment #$appointment_id to $new_date at $new_time",
                $_SESSION['user_id']
            );
            
            $_SESSION['success'] = "Your appointment has been successfully rescheduled.";
            header('Location: ' . base_url('index.php/patient/history'));
            exit;
        } else {
            $_SESSION['error'] = "Failed to reschedule appointment. Please try again.";
            header('Location: ' . base_url('index.php/patient/reschedule/' . $appointment_id));
            exit;
        }
    }

    // Rename from cancelAppointment() to cancel()
    public function cancel($appointment_id = null) {
        $appointment = $this->appointmentModel->getAppointmentById($appointment_id);
        include VIEW_PATH . '/patient/cancel.php';
    }

    // Process Cancellation
    public function processCancel() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $appointment_id = intval($_POST['appointment_id']);
            $reason = htmlspecialchars($_POST['reason']);
            $success = $this->appointmentModel->cancelAppointment($appointment_id, $reason);
            
            if ($success) {
                // Log the cancellation
                $this->activityLogModel->logActivity('appointment_canceled', 
                    "Patient canceled appointment #$appointment_id", 
                    $_SESSION['user_id']);
                
                header("Location: " . base_url("index.php/patient?success=Appointment canceled"));
            } else {
                header("Location: " . base_url("index.php/patient/cancel?error=Cancellation failed"));
            }
            exit;
        }
    }

    // View Appointment History
    public function history() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        $patient_id = $_SESSION['user_id'];
        
        // Get upcoming appointments
        $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments($patient_id);
        
        // Get past appointments
        $pastAppointments = $this->appointmentModel->getPastAppointments($patient_id);
        
        // Include view
        include VIEW_PATH . '/patient/history.php';
    }
    
    /**
     * Helper method to normalize appointment data structure
     */
    private function normalizeAppointmentData(&$appointment) {
        // Create provider_name if it doesn't exist
        if (!isset($appointment['provider_name'])) {
            if (isset($appointment['provider_first_name']) && isset($appointment['provider_last_name'])) {
                $appointment['provider_name'] = $appointment['provider_first_name'] . ' ' . $appointment['provider_last_name'];
            } elseif (isset($appointment['first_name']) && isset($appointment['last_name'])) {
                $appointment['provider_name'] = $appointment['first_name'] . ' ' . $appointment['last_name'];
            } else {
                $appointment['provider_name'] = "Unknown Provider";
            }
        }
        
        // Create service_name if it doesn't exist
        if (!isset($appointment['service_name']) && isset($appointment['service_id'])) {
            // You might need to fetch the service name from the service model
            // $service = $this->serviceModel->getServiceById($appointment['service_id']);
            // $appointment['service_name'] = $service ? $service['name'] : "Unknown Service";
            $appointment['service_name'] = "Service #" . $appointment['service_id'];
        }
    }
    

    // View Profile - Use User model for patient profile
    public function profile() {
        // Get the current user ID from session
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$user_id) {
            // Redirect if not logged in
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        // Get basic user data
        $userData = $this->userModel->getUserById($user_id);
        
        // Get patient-specific profile data
        $patientData = $this->userModel->getPatientProfile($user_id);
        
        // Combine user and patient data
        $patient = array_merge($userData ?: [], $patientData ?: []);
        
        // Log the data for debugging
        error_log("Patient data for profile: " . print_r($patient, true));
        
        include VIEW_PATH . '/patient/profile.php';
    }

    // Update Profile - Use User model for patient profile update
    public function updateProfile() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to update your profile";
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            
            // Update basic user information
            $userData = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'phone' => $_POST['phone'] ?? ''
            ];
            
            $this->userModel->updateUser($userId, $userData);
            
            // Update patient-specific profile information
            $patientData = [
                'phone' => $_POST['phone'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'address' => $_POST['address'] ?? '',
                'emergency_contact' => $_POST['emergency_contact'] ?? '',
                'emergency_contact_phone' => $_POST['emergency_contact_phone'] ?? '',
                'medical_conditions' => $_POST['medical_conditions'] ?? '',
                'insurance_provider' => $_POST['insurance_provider'] ?? '',
                'insurance_policy_number' => $_POST['insurance_policy_number'] ?? ''
            ];
            
            $result = $this->userModel->updatePatientProfile($userId, $patientData);
            
            if ($result) {
                $_SESSION['success'] = "Profile updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update profile";
            }
            
            header('Location: ' . base_url('index.php/patient/profile'));
            exit;
        }
        
        // Get current user data
        $userId = $_SESSION['user_id'];
        $userData = $this->userModel->getUserById($userId);
        $patientData = $this->userModel->getPatientProfile($userId);
        
        // Combine user and patient data
        $patient = array_merge($userData ?: [], $patientData ?: []);
        
        // Debug the data being passed to the view
        error_log("Patient data for profile form: " . print_r($patient, true));
        
        // Display the profile edit form
        include VIEW_PATH . '/patient/profile.php';
    }
    

    // Patient Search for Providers - Use Provider model
    public function search() {
        // Get filter values from request
        $specialty = $_GET['specialty'] ?? '';
        $location = $_GET['location'] ?? '';
        
        // Get specialties using Provider model
        $specialties = $this->providerModel->getDistinctSpecializations();
        
        // Only do validation if form was submitted
        $formSubmitted = isset($_GET['search_submitted']);
        
        // If form was submitted but no criteria entered, show error
        if ($formSubmitted && empty($specialty) && empty($location)) {
            $error = "Please enter at least one search criteria";
        } else {
            $error = $_GET['error'] ?? null;
        }
        
        // Get providers based on filters using Provider model
        // If no filters, just show all providers or a limited set
        $providers = $this->providerModel->searchProviders($specialty, $location);
        
        // Format provider data to ensure all required keys exist
        if (!empty($providers)) {
            foreach ($providers as &$provider) {
                // Format provider name if not already set
                if (!isset($provider['name'])) {
                    $provider['name'] = ($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? '');
                }
                // Ensure specialty is set
                if (!isset($provider['specialty'])) {
                    $provider['specialty'] = $provider['specialization'] ?? 'General';
                }
                // Ensure location is set
                if (!isset($provider['location'])) {
                    $provider['location'] = 'Local Area';  // Default or you could use a field from your database
                }
                // Map user_id to provider_id if needed
                if (!isset($provider['provider_id']) && isset($provider['user_id'])) {
                    $provider['provider_id'] = $provider['user_id'];
                }
            }
        }
        
        // Pass variables to the view
        include VIEW_PATH . '/patient/search.php';
    }

    /**
     * View a provider's profile
     * 
     * @param int $providerId The ID of the provider to view
     */
    public function viewProvider($providerId = null) {
        // Check if provider ID is provided
        if (!$providerId) {
            $_SESSION['error'] = "No provider specified";
            header('Location: ' . base_url('index.php/patient/search'));
            exit;
        }
        
        // Get provider details using the existing getById method
        $provider = $this->providerModel->getById($providerId);
        
        // Check if provider exists
        if (!$provider) {
            $_SESSION['error'] = "Provider not found";
            header('Location: ' . base_url('index.php/patient/search'));
            exit;
        }
        
        // Get provider's services
        $services = $this->providerModel->getServices($providerId);
        
        // Get provider's availability
        $availability = $this->providerModel->getAvailability($providerId);
        
        // Include the view
        include VIEW_PATH . '/patient/view_provider.php';
    }
}
?>
