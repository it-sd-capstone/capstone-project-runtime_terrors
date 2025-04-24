<?php
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/Appointment.php';
require_once '../config/Database.php';

class ProviderController {
    private $db;
    private $providerModel;
    private $appointmentModel;
    private $userModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->providerModel = new Provider($this->db);
        $this->appointmentModel = new Appointment($this->db);
        $this->userModel = new User($this->db);
    }

    // Load provider dashboard with provider & appointment data
    public function index($provider_id) {
        $provider = $this->providerModel->getProviderById($provider_id);
        $appointments = $this->appointmentModel->getByProvider($provider_id);

        include VIEW_PATH . '/provider/index.php';
    }

    // View full details of a specific appointment
    public function viewAppointment($appointment_id) {
        $appointment = $this->appointmentModel->getById($appointment_id);
        include VIEW_PATH . '/provider/view.php';
    }

    // Reschedule an appointment
    public function rescheduleAppointment() {
        $appointment_id = $_POST['appointment_id'];
        $new_date = $_POST['new_date'];
        $new_time = $_POST['new_time'];

        $this->appointmentModel->rescheduleAppointment($appointment_id, $new_date, $new_time);

        header("Location: /provider/appointments");
        exit;
    }

    // Cancel an appointment
    public function cancelAppointment() {
        $appointment_id = $_POST['appointment_id'];
        $reason = $_POST['reason'];

        $this->appointmentModel->cancelAppointment($appointment_id, $reason, $_SESSION['user_id']);

        header("Location: /provider/appointments");
        exit;
    }

    // Manage provider availability
    public function schedule($provider_id) {
        $availability = $this->providerModel->getAvailability($provider_id);
        include VIEW_PATH . '/provider/schedule.php';
    }

    public function updateAvailability() {
        $provider_id = $_POST['provider_id'];
        $availability_date = $_POST['availability_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $is_available = $_POST['is_available'];

        $this->providerModel->updateAvailability($provider_id, $availability_date, $start_time, $end_time, $is_available);
        header("Location: /provider/schedule");
        exit;
    }

    // Manage provider services
    public function services($provider_id) {
        $services = $this->providerModel->getServices($provider_id);
        include VIEW_PATH . '/provider/services.php';
    }

    // Update provider profile
    public function updateProfile() {
        $provider_id = $_POST['provider_id'];
        $data = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'specialty' => $_POST['specialty'],
            'phone' => $_POST['phone']
        ];

        $this->providerModel->updateProvider($provider_id, $data);
        header("Location: /provider/profile");
        exit;
    }
}
?>