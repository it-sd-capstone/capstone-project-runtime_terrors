<?php
class AppointmentController {
    private $db;
    private $appointmentModel;
    private $notificationModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->appointmentModel = new Appointment($this->db);
        $this->notificationModel = new Notification($this->db);
    }

    // Book an appointment
    public function book() {
        $providers = $this->appointmentModel->getProviders();
        $services = $this->appointmentModel->getServices();
        include VIEW_PATH . '/patient/book.php';
    }

    public function processBooking() {
        $patient_id = $_POST['patient_id'];
        $provider_id = $_POST['provider_id'];
        $service_id = $_POST['service_id'];
        $appointment_date = $_POST['appointment_date'];
        $start_time = $_POST['start_time'];
        $end_time = date('H:i:s', strtotime($start_time) + (30 * 60));

        $this->appointmentModel->bookAppointment($patient_id, $provider_id, $service_id, $appointment_date, $start_time, $end_time, $_POST['notes']);

        // Send notification
        $this->notificationModel->createNotification($patient_id, null, "Appointment Scheduled", "Your appointment has been booked.", "email");

        header("Location: /patient/index");
        exit;
    }

    // Reschedule an appointment
    public function processReschedule() {
        $appointment_id = $_POST['appointment_id'];
        $new_date = $_POST['new_date'];
        $new_time = $_POST['new_time'];

        $this->appointmentModel->rescheduleAppointment($appointment_id, $new_date, $new_time);

        // Send notification
        $this->notificationModel->createNotification($_POST['patient_id'], $appointment_id, "Appointment Rescheduled", "Your appointment has been rescheduled.", "email");

        header("Location: /patient/index");
        exit;
    }

    // Cancel an appointment
    public function processCancel() {
        $appointment_id = $_POST['appointment_id'];
        $reason = $_POST['reason'];

        $this->appointmentModel->cancelAppointment($appointment_id, $reason, $_SESSION['user_id']);

        // Log cancellation in history
        $this->notificationModel->createNotification($_SESSION['user_id'], $appointment_id, "Appointment Cancelled", "Your appointment has been cancelled.", "email");

        header("Location: /patient/index");
        exit;
    }
}
?>