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

    // Load Provider Dashboard
    public function index($provider_id) {
        if (!$provider_id) {
            die("Error: Provider ID missing.");
        }

        $provider = $this->providerModel->getProviderById($provider_id);
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        include VIEW_PATH . '/provider/index.php';
    }

    // Display Schedule Settings
    public function schedule($provider_id) {
        if (!$provider_id) {
            die("Error: Provider ID missing.");
        }

        $available_slots = $this->providerModel->getAvailableSlots($provider_id);
        include VIEW_PATH . '/provider/schedule.php';
    }

    // Fetch Availability for FullCalendar.js
    public function getProviderSchedules($provider_id) {
        if (!$provider_id) {
            die("Error: Provider ID missing.");
        }

        header("Content-Type: application/json");
        $schedules = $this->providerModel->getAvailability($provider_id);

        $events = [];
        foreach ($schedules as $slot) {
            $events[] = [
                'title' => 'Available',
                'start' => $slot['availability_date'] . 'T' . $slot['start_time'],
                'end'   => $slot['availability_date'] . 'T' . $slot['end_time']
            ];
        }

        echo json_encode($events);
        exit;
    }
}
?>