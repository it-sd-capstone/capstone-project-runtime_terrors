<?php
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/Appointment.php';
require_once '../config/Database.php';

class PatientController {
    private $db;
    private $patientModel;
    private $appointmentModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->patientModel = new Patient($this->db);
        $this->appointmentModel = new Appointment($this->db);
    }

    // ✅ Load Patient Dashboard
    public function index($patient_id) {
        $patient = $this->patientModel->getPatientById($patient_id);
        $appointments = $this->appointmentModel->getUpcomingAppointments($patient_id);
        $stats = $this->appointmentModel->getAppointmentStats($patient_id);

        include VIEW_PATH . '/patient/patient_dashboard.php';
    }

    // ✅ Cancel Appointment
    public function cancelAppointment($appointment_id) {
        $this->appointmentModel->cancelAppointment($appointment_id);
        header("Location: /patient");
        exit;
    }

    // ✅ Reschedule Appointment
    public function rescheduleAppointment($appointment_id) {
        include VIEW_PATH . '/patient/reschedule.php';
    }

    // ✅ Book New Appointment
    public function bookAppointment() {
        include VIEW_PATH . '/patient/book.php';
    }
}
?>