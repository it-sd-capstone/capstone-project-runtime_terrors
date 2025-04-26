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

    // Show Booking Form - Use Provider model instead of User model
    public function book() {
        // Get providers from Provider model instead of User model
        $providers = $this->providerModel->getAllProvidersWithDetails();
        
        // Get services using the initialized service model
        $services = $this->serviceModel->getAllServices();
        
        // Pass both variables to the view
        include VIEW_PATH . '/patient/book.php';
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
        $pastAppointments = $this->appointmentModel->getPastAppointments($patient_id);
        $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments($patient_id);
        include VIEW_PATH . '/patient/history.php';
    }

    // View Profile - Use User model for patient profile
    public function profile() {
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$user_id) {
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        // Get basic user data
        $userData = $this->userModel->getUserById($user_id);
        
        // Get patient profile data using User model
        $patientProfile = $this->userModel->getPatientProfile($user_id);
        
        // Combine data for the view
        $patient = [
            'user_id' => $user_id,
            'first_name' => $userData['first_name'] ?? '',
            'last_name' => $userData['last_name'] ?? '',
            'email' => $userData['email'] ?? '',
            'phone' => $userData['phone'] ?? '',
            'medical_history' => $patientProfile['medical_notes'] ?? ''
        ];
        
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
        $specialty = htmlspecialchars($_GET['specialty'] ?? '');
        $location = htmlspecialchars($_GET['location'] ?? '');
        
        // Get specialties using Provider model
        $specialties = $this->providerModel->getDistinctSpecializations();
        
        // Get providers based on filters using Provider model
        $providers = $this->providerModel->searchProviders($specialty, $location);
        
        include VIEW_PATH . '/patient/search.php';
    }
}
?>
