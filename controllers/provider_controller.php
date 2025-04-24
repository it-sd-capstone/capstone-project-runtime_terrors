<?php
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/Appointment.php';
require_once '../config/Database.php';

class ProviderController {
    private $db;
    private $providerModel;
    private $appointmentModel;
    private $notificationModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->providerModel = new Provider($this->db);
        $this->appointmentModel = new Appointment($this->db);
        $this->notificationModel = new Notification($this->db);
    }

    // Load provider dashboard
    public function index($provider_id) {
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        include VIEW_PATH . '/provider/index.php';
    }

    // Manage availability
    public function schedule($provider_id) {
        $availability = $this->providerModel->getAvailability($provider_id);
        include VIEW_PATH . '/provider/schedule.php';
    }

    // Get upcoming appointments
    public function appointments($provider_id) {
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        include VIEW_PATH . '/provider/appointments.php';
    }

    // Manage services
    public function services($provider_id) {
        $services = $this->providerModel->getServices($provider_id);
        include VIEW_PATH . '/provider/services.php';
    }

    // Update availability
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