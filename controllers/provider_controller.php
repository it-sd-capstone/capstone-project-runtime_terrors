<?php
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/Appointment.php';
require_once '../core/Session.php';
require_once '../core/Database.php';

class ProviderController {
    private $db;
    private $providerModel;
    private $appointmentModel;

    public function __construct() {
        Session::start();
        $this->db = Database::getConnection();

        if (!Session::isLoggedIn() || $_SESSION['role'] !== 'provider') {
            header("Location: /auth/login");
            exit;
        }

        $this->providerModel = new Provider($this->db);
        $this->appointmentModel = new Appointment($this->db);
    }

    // ✅ Provider Dashboard
    public function index() {
        $provider_id = $_SESSION['user_id'];
        $provider_availability = $this->providerModel->getAvailability($provider_id);
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        
        include VIEW_PATH . '/provider/index.php';
    }

    // ✅ Update Provider Profile
    public function updateProfile() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $name = trim($_POST['name']);
            $specialty = trim($_POST['specialty']);
            $bio = trim($_POST['bio']);
            
            if (!empty($name) && !empty($specialty)) {
                $this->providerModel->updateProfile($_SESSION['user_id'], $name, $specialty, $bio);
                header("Location: /provider/profile?success=Profile updated");
                exit;
            }
        }
        header("Location: /provider/profile?error=Invalid input");
        exit;
    }

    // ✅ Manage Provider Availability
    public function addAvailability() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $date = $_POST['availability_date'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            
            if ($end_time > $start_time) {
                $this->providerModel->addAvailability($provider_id, $date, $start_time, $end_time);
                header("Location: /provider/schedule?success=Availability added");
                exit;
            }
        }
        header("Location: /provider/schedule?error=Invalid input");
        exit;
    }

    // ✅ Manage Recurring Schedule
    public function addRecurringSchedule() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $day_of_week = intval($_POST['day_of_week']);
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];

            if ($end_time > $start_time) {
                $this->providerModel->addRecurringSchedule($provider_id, $day_of_week, $start_time, $end_time);
                header("Location: /provider/schedule?success=Schedule added");
                exit;
            }
        }
        header("Location: /provider/schedule?error=Invalid input");
        exit;
    }

    // ✅ Fetch Availability for FullCalendar.js
    public function getProviderSchedules() {
        header("Content-Type: application/json");
        $provider_id = $_SESSION['user_id'];
        $schedules = $this->providerModel->getAvailability($provider_id);
        
        echo json_encode($schedules);
        exit;
    }
}
?>