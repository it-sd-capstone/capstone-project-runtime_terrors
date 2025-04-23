<?php
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/Appointment.php';
require_once '../config/Database.php';

class ProviderController {
    private $db;
    private $providerModel;
    private $appointmentModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->providerModel = new Provider($this->db);
        $this->appointmentModel = new Appointment($this->db);
    }

    // ✅ Provider Dashboard (No Sessions, Retrieves Provider Directly)
    public function index($provider_id) {
        $provider = $this->providerModel->getProviderById($provider_id);
        $provider_availability = $this->providerModel->getAvailability($provider_id);
        $appointments = $this->appointmentModel->getByProvider($provider_id);

        include VIEW_PATH . '/provider/index.php';
    }

    // ✅ Load Provider Schedule Page
    public function schedule($provider_id) {
        include VIEW_PATH . '/provider/schedule.php';
    }

    // ✅ Fetch Availability for FullCalendar.js
    public function getProviderSchedules($provider_id) {
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