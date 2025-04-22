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

    // ✅ Fetch provider profile details
    public function profile() {
        $providerData = $this->providerModel->getProviderData($_SESSION['user_id']);
        include VIEW_PATH . '/provider/profile.php';
    }

    // ✅ Update provider profile
    public function updateProfile() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $name = trim($_POST['name']);
            $specialty = trim($_POST['specialty']);
            $bio = trim($_POST['bio']);

            if (!empty($name)) {
                $this->providerModel->updateProfile($_SESSION['user_id'], $name, $specialty, $bio);
                header("Location: /provider/profile?success=Profile updated");
                exit;
            }
        }
        header("Location: /provider/profile?error=Invalid input");
        exit;
    }

    // ✅ Validate appointment slot before booking
    public function validateAppointment() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_POST['provider_id'];
            $date = $_POST['appointment_date'];
            $start_time = $_POST['appointment_time'];
            $end_time = date("H:i:s", strtotime($start_time) + (30 * 60));

            if (!$this->providerModel->isSlotAvailable($provider_id, $date, $start_time, $end_time)) {
                header("Location: /patient/book?error=Slot unavailable");
                exit;
            }

            header("Location: /patient/book?success=Slot available");
            exit;
        }
    }

    // ✅ Provider Service Management (CRUD)
    public function services() {
        $provider_id = $_SESSION['user_id'];
        $providerServices = $this->providerModel->getServices($provider_id);
        include VIEW_PATH . '/provider/services.php';
    }

    public function addService() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $service_name = trim($_POST['service_name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);

            if (!empty($service_name)) {
                $this->providerModel->addService($provider_id, $service_name, $description, $price);
                header("Location: /provider/services?success=Service added");
                exit;
            }
        }
        header("Location: /provider/services?error=Invalid input");
        exit;
    }

    public function deleteService() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $service_id = intval($_POST['service_id']);

            if ($this->providerModel->deleteService($service_id, $provider_id)) {
                header("Location: /provider/services?success=Service deleted");
            } else {
                header("Location: /provider/services?error=Failed to delete");
            }
            exit;
        }
    }

    // ✅ Manage Recurring Schedules (CRUD)
    public function addRecurringSchedule() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $day_of_week = intval($_POST['day_of_week']);
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($start_time) && !empty($end_time) && $end_time > $start_time) {
                $this->providerModel->addRecurringSchedule($provider_id, $day_of_week, $start_time, $end_time, $is_active);
                header("Location: /provider/schedule?success=Schedule added");
                exit;
            }
        }
        header("Location: /provider/schedule?error=Invalid input");
        exit;
    }

    public function getRecurringSchedule() {
        $provider_id = $_SESSION['user_id'];
        $recurringSchedules = $this->providerModel->getRecurringSchedule($provider_id);
        include VIEW_PATH . '/provider/schedule.php';
    }

    public function deleteRecurringSchedule() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $schedule_id = intval($_POST['schedule_id']);

            if ($this->providerModel->deleteRecurringSchedule($schedule_id, $provider_id)) {
                header("Location: /provider/schedule?success=Schedule deleted");
            } else {
                header("Location: /provider/schedule?error=Failed to delete");
            }
            exit;
        }
    }
}
?>