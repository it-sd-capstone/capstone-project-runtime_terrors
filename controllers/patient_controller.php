<?php
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/Appointment.php';
require_once '../config/Database.php';

class PatientController {
    private $db;
    private $userModel;
    private $appointmentModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User($this->db);
        $this->appointmentModel = new Appointment($this->db);
    }

    // Load Dashboard
    public function index($patient_id) {
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
        $patient_id = $_POST['patient_id'];
        $provider_id = $_POST['provider_id'];
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];

        $this->appointmentModel->scheduleAppointment($patient_id, $provider_id, $appointment_date, $appointment_time);
        header("Location: /patient");
        exit;
    }

    // Load Reschedule Form
    public function rescheduleAppointment($appointment_id) {
        include VIEW_PATH . '/patient/reschedule.php';
    }

    // Process Reschedule
    public function processReschedule() {
        $appointment_id = $_POST['appointment_id'];
        $new_date = $_POST['new_date'];
        $new_time = $_POST['new_time'];

        $this->appointmentModel->rescheduleAppointment($appointment_id, $new_date, $new_time);
        header("Location: /patient");
        exit;
    }

    // View Appointment History
    public function history($patient_id) {
        $pastAppointments = $this->appointmentModel->getPastAppointments($patient_id);
        include VIEW_PATH . '/patient/history.php';
    }

    // View Profile
    public function profile($patient_id) {
        $patient = $this->userModel->getPatientById($patient_id);
        include VIEW_PATH . '/patient/profile.php';
    }

    // Update Profile
    public function updateProfile() {
        $user_id = $_POST['user_id'];
        $data = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'phone' => $_POST['phone']
        ];

        $this->userModel->updateUser($user_id, $data);
        header("Location: /patient/profile");
        exit;
    }
}