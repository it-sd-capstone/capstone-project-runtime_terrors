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
    
        // ✅ Load Provider Dashboard
        public function index($provider_id) {
            $provider = $this->providerModel->getProviderById($provider_id);
            $appointments = $this->appointmentModel->getByProvider($provider_id);
            include VIEW_PATH . '/provider/index.php';
        }
    
        // ✅ Display Schedule Settings
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
    
        // ✅ View Appointments
        public function appointments($provider_id) {
            $appointments = $this->appointmentModel->getByProvider($provider_id);
            include VIEW_PATH . '/provider/appointments.php';
        }
    
        // ✅ Show Appointment Requests
        public function requests($provider_id) {
            $requests = $this->appointmentModel->getRequests($provider_id);
            include VIEW_PATH . '/provider/requests.php';
        }
    
        // ✅ Approve Appointment Request
        public function approveRequest($request_id) {
            $this->appointmentModel->approveRequest($request_id);
            header("Location: /provider/requests");
            exit;
        }
    
        // ✅ Decline Appointment Request
        public function declineRequest($request_id) {
            $this->appointmentModel->declineRequest($request_id);
            header("Location: /provider/requests");
            exit;
        }
    
        // ✅ Manage Services
        public function services($provider_id) {
            $services = $this->providerModel->getServices($provider_id);
            include VIEW_PATH . '/provider/services.php';
        }
    
        // ✅ Add New Service
        public function addService() {
            include VIEW_PATH . '/provider/addService.php';
        }
    
        public function processAddService() {
            $provider_id = $_POST['provider_id'];
            $service_name = $_POST['name'];
            $duration = $_POST['duration'];
            $cost = $_POST['cost'];
    
            $this->providerModel->addService($provider_id, $service_name, $duration, $cost);
            header("Location: /provider/services");
            exit;
        }
    
        // ✅ Delete Service
        public function deleteService($service_id) {
            include VIEW_PATH . '/provider/deleteService.php';
        }
    
        public function processDeleteService() {
            $service_id = $_POST['service_id'];
            $this->providerModel->deleteService($service_id);
            header("Location: /provider/services");
            exit;
        }
    
        // ✅ View & Edit Profile
        public function profile($provider_id) {
            $provider = $this->providerModel->getProviderById($provider_id);
            include VIEW_PATH . '/provider/profile.php';
        }
    
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
    
        // ✅ Show Notifications
        public function notifications($provider_id) {
            $notifications = $this->providerModel->getNotifications($provider_id);
            include VIEW_PATH . '/provider/notifications.php';
        }
    }
    ?>    private $db;
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
        $available_slots = $this->providerModel->getAvailableSlots($provider_id); // ✅ Ensure this is being set
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