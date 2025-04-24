<?php
require_once MODEL_PATH . '/Appointment.php';
require_once '../core/Session.php';
require_once '../core/Database.php';

class PatientController {
    private $db;
    private $appointmentModel;

    public function __construct() {
        Session::start();
        $this->db = Database::getConnection();

        if (!Session::isLoggedIn() || $_SESSION['role'] !== 'patient') {
            header("Location: /auth/login");
            exit;
        }

        $this->appointmentModel = new Appointment($this->db);
    }

    // ✅ Display available providers for booking
    public function book() {
        $availableProviders = $this->appointmentModel->getAvailableProviders();
        include VIEW_PATH . '/auth/book.php';
    }

    // ✅ Book an appointment
    public function bookAppointment() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $patient_id = $_SESSION['user_id'];
            $provider_id = $_POST['provider_id'];
            $date = $_POST['appointment_date'];
            $start_time = $_POST['appointment_time'];
            $end_time = date("H:i:s", strtotime($start_time) + (30 * 60));

            if (!$this->appointmentModel->isSlotAvailable($provider_id, $date, $start_time, $end_time)) {
                header("Location: /auth/book?error=Slot unavailable");
                exit;
            }

            $this->appointmentModel->create($patient_id, $provider_id, $date, $start_time, $end_time);
            header("Location: /appointments/history?success=Appointment booked");
            exit;
        }
    }

    // ✅ Show appointment history
    public function history() {
        $patient_id = $_SESSION['user_id'];
        $appointments = $this->appointmentModel->getByPatient($patient_id);
        include VIEW_PATH . '/appointments/history.php';
    }

    // ✅ Cancel an appointment
    public function cancelAppointment() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $appointment_id = $_POST['appointment_id'];
            $this->appointmentModel->updateStatus($appointment_id, "canceled");
            header("Location: /appointments/history?success=Appointment canceled");
            exit;
        }
    }
}
?>