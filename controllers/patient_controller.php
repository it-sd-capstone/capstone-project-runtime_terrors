<?php
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/Appointment.php';
require_once MODEL_PATH . '/Provider.php';

class PatientController {
    private $db;
    private $userModel;
    private $appointmentModel;

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
    }

    // Load Dashboard
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

    // Show Booking Form
    public function book() {
        // Get providers
        $rawProviders = $this->userModel->getAvailableProviders();
        
        // Format providers to include provider_name
        $providers = [];
        foreach ($rawProviders as $provider) {
            // Make sure to preserve user_id as provider_id
            $providers[] = [
                'provider_id' => $provider['user_id'], // This key was missing
                'provider_name' => $provider['first_name'] . ' ' . $provider['last_name'] . ' - ' . 
                                  ($provider['title'] ?? 'Practitioner'),
                'specialization' => $provider['specialization'] ?? '',
                'title' => $provider['title'] ?? ''
            ];
        }
        
        // Get services - fix the file name (Service vs Services)
        require_once MODEL_PATH . '/Services.php'; // Changed from '/Services.php'
        $serviceModel = new Service($this->db);
        $services = $serviceModel->getAllServices();
        
        // Pass both variables to the view
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

    // Confirm Booking
    public function confirmBooking() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $patient_id = $_SESSION['user_id'];
            $provider_id = intval($_POST['provider_id']);
            $appointment_date = htmlspecialchars($_POST['appointment_date']);
            $appointment_time = htmlspecialchars($_POST['appointment_time']);

            if ($provider_id && !empty($appointment_date) && !empty($appointment_time)) {
                $success = $this->appointmentModel->scheduleAppointment($patient_id, $provider_id, $appointment_date, $appointment_time);

                if ($success) {
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

            $success = $this->appointmentModel->rescheduleAppointment($appointment_id, $new_date, $new_time);

            if ($success) {
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

    // View Profile
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
        
        // Get patient-specific profile data
        try {
            if ($this->db instanceof mysqli) {
                $query = "SELECT * FROM patient_profiles WHERE patient_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $patientProfile = $result->fetch_assoc();
            } elseif ($this->db instanceof PDO) {
                $query = "SELECT * FROM patient_profiles WHERE patient_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$user_id]);
                $patientProfile = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Merge patient profile data if found
            if ($patientProfile) {
                $patient['medical_history'] = $patientProfile['medical_notes'] ?? '';
                // Add any other patient profile fields you need
            }
        } catch (Exception $e) {
            error_log("Error fetching patient profile: " . $e->getMessage());
        }
        
        // Load the view with the complete patient data
        include VIEW_PATH . '/patient/profile.php';
    }

    // Update Profile
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
        
        // Update patient profile data
        $profileUpdated = false;
        try {
            if ($this->db instanceof mysqli) {
                // Check if profile exists
                $checkQuery = "SELECT COUNT(*) as count FROM patient_profiles WHERE patient_id = ?";
                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->bind_param("i", $user_id);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                $exists = ($checkResult->fetch_assoc()['count'] > 0);
                
                if ($exists) {
                    $query = "UPDATE patient_profiles SET medical_notes = ? WHERE patient_id = ?";
                    $stmt = $this->db->prepare($query);
                    $stmt->bind_param("si", $medical_history, $user_id);
                } else {
                    $query = "INSERT INTO patient_profiles (patient_id, medical_notes) VALUES (?, ?)";
                    $stmt = $this->db->prepare($query);
                    $stmt->bind_param("is", $user_id, $medical_history);
                }
                $profileUpdated = $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                // Check if profile exists
                $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM patient_profiles WHERE patient_id = ?");
                $checkStmt->execute([$user_id]);
                $exists = ($checkStmt->fetchColumn() > 0);
                
                if ($exists) {
                    $stmt = $this->db->prepare("UPDATE patient_profiles SET medical_notes = ? WHERE patient_id = ?");
                    $profileUpdated = $stmt->execute([$medical_history, $user_id]);
                } else {
                    $stmt = $this->db->prepare("INSERT INTO patient_profiles (patient_id, medical_notes) VALUES (?, ?)");
                    $profileUpdated = $stmt->execute([$user_id, $medical_history]);
                }
            }
        } catch (Exception $e) {
            error_log("Error updating patient profile: " . $e->getMessage());
        }
        
        // Redirect with success or error message
        if ($userUpdated || $profileUpdated) {
            header('Location: ' . base_url('index.php/patient/profile?success=1'));
        } else {
            header('Location: ' . base_url('index.php/patient/profile?error=1'));
        }
        exit;
    }

    // Patient Search for Providers
    public function search() {
        // Get filter values from request
        $specialty = htmlspecialchars($_GET['specialty'] ?? '');
        $location = htmlspecialchars($_GET['location'] ?? '');
        
        // Get specialties in the format expected by the view (array of arrays with 'name' key)
        $specialties = [];
        try {
            if ($this->db instanceof mysqli) {
                $query = "SELECT DISTINCT specialization as name FROM provider_profiles WHERE specialization IS NOT NULL";
                $result = $this->db->query($query);
                while ($row = $result->fetch_assoc()) {
                    if (!empty($row['name'])) {
                        $specialties[] = $row;  // Each row has 'name' key
                    }
                }
            } elseif ($this->db instanceof PDO) {
                $query = "SELECT DISTINCT specialization as name FROM provider_profiles WHERE specialization IS NOT NULL";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $specialties = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Array of arrays with 'name' key
            }
        } catch (Exception $e) {
            error_log("Error getting specialties: " . $e->getMessage());
        }
        
        // Get providers based on filters
        $providers = $this->userModel->searchProviders($specialty, $location);
        
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
                // optional to make it be faster when they search to require more information
                if (empty($specialty) && empty($location)) {
                    error_log("Search called with no filters.");
                    header("Location: " . base_url("index.php/patient/search?error=Please enter search criteria"));
                    exit;
                }
                
                // Optionally set next_available_date if you have this data
                if (!isset($provider['next_available_date'])) {
                    // You could fetch this from your availability table if needed
                    // $provider['next_available_date'] = $this->getNextAvailableDate($provider['provider_id']);
                }
            }
        }
        
        include VIEW_PATH . '/patient/search.php';
    }
}
?>