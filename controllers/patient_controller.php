<?php
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/Appointment.php';
require_once '../config/Database.php';

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

        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User($this->db);
        $this->appointmentModel = new Appointment($this->db);
    }

    // Load Dashboard
    public function index() {
        $patient_id = $_SESSION['user_id'];
        $patient = $this->userModel->getPatientById($patient_id);
        $appointments = $this->appointmentModel->getUpcomingAppointments($patient_id);
        include VIEW_PATH . '/patient/index.php';
    }

    // Show Booking Form
    public function bookAppointment() {
        $providers = $this->userModel->getAvailableProviders();
        include VIEW_PATH . '/patient/book.php';
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

    // Load Reschedule Form
    public function rescheduleAppointment($appointment_id) {
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

    // Cancel Appointment
    public function cancelAppointment($appointment_id) {
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
        $patient_id = $_SESSION['user_id'];
        $patient = $this->userModel->getPatientById($patient_id);
        include VIEW_PATH . '/patient/profile.php';
    }

    // Update Profile
    public function updateProfile() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $patient_id = $_SESSION['user_id'];
            $data = [
                'first_name' => htmlspecialchars(trim($_POST['first_name'])),
                'last_name' => htmlspecialchars(trim($_POST['last_name'])),
                'phone' => htmlspecialchars(trim($_POST['phone']))
            ];

            $success = $this->userModel->updateUser($patient_id, $data);

            if ($success) {
                header("Location: " . base_url("index.php/patient/profile?success=Profile updated"));
            } else {
                header("Location: " . base_url("index.php/patient/profile?error=Update failed"));
            }
            exit;
        }
    }

    // Patient Search for Providers
    public function search() {
        $specialty = htmlspecialchars($_GET['specialty'] ?? '');
        $location = htmlspecialchars($_GET['location'] ?? '');
        $providers = $this->userModel->searchProviders($specialty, $location);
        include VIEW_PATH . '/patient/search.php';
    }
}
?>