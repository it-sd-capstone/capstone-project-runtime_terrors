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
            $provider = $this->providerModel->getProviderById($providerId);
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
        
        // Pass variables to the view
        include VIEW_PATH . '/patient/book.php';
    }

    public function processBooking() {
        require_once MODEL_PATH . '/Appointments.php';
        $appointmentModel = new Appointments($this->db);
    
        $patient_id = $_SESSION['user_id'];
        $provider_id = $_POST['provider_id'];
        $date = $_POST['appointment_date'];
        $time_slot = $_POST['start_time'];
    
        // Check if slot is already booked
        if ($appointmentModel->isSlotTaken($provider_id, $date, $time_slot)) {
            header("Location: " . base_url("index.php/patient/book?provider_id=$provider_id&error=Time slot unavailable"));
            exit;
        }
    
        $success = $appointmentModel->createAppointment($patient_id, $provider_id, $date, $time_slot);
    
        if ($success) {
            header("Location: " . base_url("index.php/patient/appointments?success=Appointment booked"));
        } else {
            header("Location: " . base_url("index.php/patient/book?provider_id=$provider_id&error=Booking failed"));
        }
        exit;
    }

    // Confirm Booking - Add service_id parameter
    public function confirmBooking() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $patient_id = $_SESSION['user_id'];
            $provider_id = intval($_POST['provider_id']);
            $service_id = intval($_POST['service_id'] ?? 0);
            $appointment_date = htmlspecialchars($_POST['appointment_date']);
            $appointment_time = htmlspecialchars($_POST['appointment_time']);
            $type = htmlspecialchars($_POST['type'] ?? 'in_person');
            $notes = htmlspecialchars($_POST['notes'] ?? '');
            $reason = htmlspecialchars($_POST['reason'] ?? '');
            
            if ($provider_id && !empty($appointment_date) && !empty($appointment_time)) {
                // Calculate end time based on service duration
                $service = $this->serviceModel->getServiceById($service_id);
                $duration = $service ? $service['duration'] : 30; // Default to 30 minutes
                $end_time = date('H:i:s', strtotime($appointment_time . ' +' . $duration . ' minutes'));
                
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
                
                if ($success) {
                    // Log the appointment creation
                    $this->activityLogModel->logActivity('appointment_created', 
                        "Patient scheduled appointment with provider #$provider_id", 
                        $patient_id);
                    
                    header("Location: " . base_url("index.php/patient?success=Appointment booked"));
                } else {
                    header("Location: " . base_url("index.php/patient/book?error=Booking failed"));
                }
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
        $appointment = $this->appointmentModel->getAppointmentById($appointment_id);
        include VIEW_PATH . '/patient/reschedule.php';
    }

    // Process Reschedule
    public function processReschedule() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $appointment_id = intval($_POST['appointment_id']);
            $new_date = htmlspecialchars($_POST['new_date']);
            $new_time = htmlspecialchars($_POST['new_time']);
            
            // Get the appointment to calculate end time
            $appointment = $this->appointmentModel->getAppointmentById($appointment_id);
            
            // Calculate end time based on service duration
            $service = $this->serviceModel->getServiceById($appointment['service_id']);
            $duration = $service ? $service['duration'] : 30; // Default to 30 minutes
            $new_end_time = date('H:i:s', strtotime($new_time . ' +' . $duration . ' minutes'));
            
            $success = $this->appointmentModel->rescheduleAppointment($appointment_id, $new_date, $new_time, $new_end_time);
            
            if ($success) {
                // Log the rescheduling
                $this->activityLogModel->logActivity('appointment_rescheduled', 
                    "Patient rescheduled appointment #$appointment_id", 
                    $_SESSION['user_id']);
                
                header("Location: " . base_url("index.php/patient?success=Appointment rescheduled"));
            } else {
                header("Location: " . base_url("index.php/patient/reschedule?error=Reschedule failed"));
            }
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
        $patient_id = $_SESSION['user_id'];
        
        // Get appointments
        $pastAppointments = $this->appointmentModel->getPastAppointments($patient_id);
        $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments($patient_id);
        
        // Normalize provider names for past appointments
        foreach ($pastAppointments as &$appointment) {
            $this->normalizeAppointmentData($appointment);
        }
        
        // Normalize provider names for upcoming appointments
        foreach ($upcomingAppointments as &$appointment) {
            $this->normalizeAppointmentData($appointment);
        }
        
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
        
        // Initialize the patient array with defaults to avoid null values
        $patient = [
            'user_id' => $user_id,
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'medical_history' => ''
        ];
        
        // Get basic user data
        $userData = $this->userModel->getUserById($user_id);
        if ($userData) {
            // Merge user data into patient array
            $patient['first_name'] = $userData['first_name'] ?? '';
            $patient['last_name'] = $userData['last_name'] ?? '';
            $patient['email'] = $userData['email'] ?? '';
            $patient['phone'] = $userData['phone'] ?? '';
        }
        
      try {
           // Get patient-specific profile data using User model
          $patientProfile = $this->userModel->getPatientProfile($user_id);
            
            // Merge patient profile data if found
            if ($patientProfile) {
                $patient['medical_history'] = $patientProfile['medical_notes'] ?? '';
                // Add any other patient profile fields you need
            }
        } catch (Exception $e) {
            error_log("Error fetching patient profile: " . $e->getMessage());
        }
        
        include VIEW_PATH . '/patient/profile.php';
    }

    // Update Profile - Use User model for patient profile update
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url('index.php/patient/profile'));
            exit;
        }
        
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$user_id) {
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        // Get form data
        $first_name = htmlspecialchars($_POST['first_name'] ?? '');
        $last_name = htmlspecialchars($_POST['last_name'] ?? '');
        $phone = htmlspecialchars($_POST['phone'] ?? '');
        $medical_history = htmlspecialchars($_POST['medical_history'] ?? '');
        
        // Update user data
        $userUpdated = $this->userModel->updateUser($user_id, [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone
        ]);
        
        // Update patient profile data using User model
        $profileUpdated = $this->userModel->updatePatientProfile($user_id, [
            'medical_notes' => $medical_history
        ]);
        
        if ($userUpdated || $profileUpdated) {
            // Log the profile update
            $this->activityLogModel->logActivity('profile_updated', 
                'Patient updated their profile', 
                $user_id);
            
            header('Location: ' . base_url('index.php/patient/profile?success=1'));
        } else {
            header('Location: ' . base_url('index.php/patient/profile?error=1'));
        }
        exit;
    }

    // Patient Search for Providers - Use Provider model
    public function search() {
        // Get filter values from request
        $specialty = $_GET['specialty'] ?? '';
        $location = $_GET['location'] ?? '';
        
        // Get specialties using Provider model
        $specialties = $this->providerModel->getDistinctSpecializations();
        
        // Only apply validation if form was submitted
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
}
?>
