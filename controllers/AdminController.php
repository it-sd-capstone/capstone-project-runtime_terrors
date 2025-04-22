<?php
require_once MODEL_PATH . '/Admin.php';
require_once '../core/Session.php';
require_once '../core/Database.php';

class AdminController {
    private $db;
    private $adminModel;

    public function __construct() {
        Session::start();
        $this->db = Database::getConnection();

        if (!Session::isLoggedIn() || $_SESSION['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        $this->adminModel = new Admin($this->db);
    }

    // ✅ Admin Dashboard Overview
    public function index() {
        $stats = [
            'totalUsers' => $this->adminModel->getCount('users'),
            'totalPatients' => $this->adminModel->getCountByRole('patient'),
            'totalProviders' => $this->adminModel->getCountByRole('provider'),
            'totalAppointments' => $this->adminModel->getCount('appointments'),
            'pendingAppointments' => $this->adminModel->getCountByStatus('pending'),
            'completedAppointments' => $this->adminModel->getCountByStatus('completed'),
            'canceledAppointments' => $this->adminModel->getCountByStatus('canceled'),
            'totalServices' => $this->adminModel->getCount('services')
        ];
        include VIEW_PATH . '/admin/index.php';
    }

    // ✅ Manage Users
    public function users() {
        $users = $this->adminModel->getAllUsers();
        include VIEW_PATH . '/admin/users.php';
    }

    public function toggleUserStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            $userId = intval($_POST['user_id']);
            $this->adminModel->toggleUserStatus($userId);
            header("Location: /admin/users?success=updated");
            exit;
        }
        header("Location: /admin/users?error=update_failed");
        exit;
    }

    // ✅ Manage Services
    public function services() {
        $services = $this->adminModel->getAllServices();
        include VIEW_PATH . '/admin/services.php';
    }

    public function toggleServiceStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
            $serviceId = intval($_POST['service_id']);
            $this->adminModel->toggleServiceStatus($serviceId);
            header("Location: /admin/services?success=updated");
            exit;
        }
        header("Location: /admin/services?error=update_failed");
        exit;
    }

    public function addService() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $duration = intval($_POST['duration'] ?? 30);

            if (!empty($name)) {
                $this->adminModel->addService($name, $description, $duration);
                header("Location: /admin/services?success=added");
                exit;
            }
        }
        header("Location: /admin/services?error=missing_name");
        exit;
    }

    // ✅ Manage Appointments
    public function appointments() {
        $appointments = $this->adminModel->getAllAppointments();
        include VIEW_PATH . '/admin/appointments.php';
    }

    public function updateAppointmentStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id']) && isset($_POST['status'])) {
            $appointmentId = intval($_POST['appointment_id']);
            $status = $_POST['status'];

            $validStatuses = ['pending', 'confirmed', 'completed', 'canceled'];
            if (!in_array($status, $validStatuses)) {
                header("Location: /admin/appointments?error=invalid_status");
                exit;
            }

            $this->adminModel->updateAppointmentStatus($appointmentId, $status);
            header("Location: /admin/appointments?success=updated");
            exit;
        }
        header("Location: /admin/appointments?error=update_failed");
        exit;
    }
}
?>