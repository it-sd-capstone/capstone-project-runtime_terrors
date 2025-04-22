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
            header('Location: ' . base_url('index.php/auth'));
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
    
    // Database count methods (as fallback if model methods fail)
    private function getCount($table) {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM $table");
        if ($stmt) {
            $result = $stmt->fetch_assoc();
            return $result['count'];
        }
        return 0;
    }
    
    private function getCountByRole($role) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['count'];
        }
        return 0;
    }
    
    private function getCountByStatus($status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM appointments WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['count'];
        }
        return 0;
    }
    
    // ✅ Manage Users
    public function users() {
        // Try to get users from model first
        try {
            $users = $this->adminModel->getAllUsers();
        } catch (Exception $e) {
            // Fallback to direct query if model fails
            $users = [];
            $stmt = $this->db->query("SELECT * FROM users ORDER BY role, last_name, first_name");
            if ($stmt) {
                while ($row = $stmt->fetch_assoc()) {
                    $users[] = $row;
                }
            }
        }
        
        include VIEW_PATH . '/admin/users.php';
    }
    
    public function toggleUserStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            $userId = intval($_POST['user_id']);
            
            try {
                // Try to use the model method
                $success = $this->adminModel->toggleUserStatus($userId);
            } catch (Exception $e) {
                // Fallback to direct database operations
                $success = false;
                
                // Get current status
                $stmt = $this->db->prepare("SELECT is_active FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    $newStatus = $user['is_active'] ? 0 : 1; // Toggle status
                    
                    // Update status
                    $updateStmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE user_id = ?");
                    $updateStmt->bind_param("ii", $newStatus, $userId);
                    $success = $updateStmt->execute();
                }
            }
            
            if ($success) {
                header('Location: ' . base_url('index.php/admin/users?success=updated'));
                exit;
            }
        }
        
        header('Location: ' . base_url('index.php/admin/users?error=update_failed'));
        exit;
    }
    
    // ✅ Manage Services
    public function services() {
        try {
            $services = $this->adminModel->getAllServices();
        } catch (Exception $e) {
            // Fallback to direct query
            $services = [];
            $stmt = $this->db->query("SELECT * FROM services ORDER BY name");
            if ($stmt) {
                while ($row = $stmt->fetch_assoc()) {
                    $services[] = $row;
                }
            }
        }
        
        include VIEW_PATH . '/admin/services.php';
    }
    
    public function toggleServiceStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
            $serviceId = intval($_POST['service_id']);
            
            try {
                $success = $this->adminModel->toggleServiceStatus($serviceId);
            } catch (Exception $e) {
                $success = false;
                
                // Get current status
                $stmt = $this->db->prepare("SELECT is_active FROM services WHERE service_id = ?");
                $stmt->bind_param("i", $serviceId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $service = $result->fetch_assoc();
                    $newStatus = $service['is_active'] ? 0 : 1; // Toggle status
                    
                    // Update status
                    $updateStmt = $this->db->prepare("UPDATE services SET is_active = ? WHERE service_id = ?");
                    $updateStmt->bind_param("ii", $newStatus, $serviceId);
                    $success = $updateStmt->execute();
                }
            }
            
            if ($success) {
                header('Location: ' . base_url('index.php/admin/services?success=updated'));
                exit;
            }
        }
        
        header('Location: ' . base_url('index.php/admin/services?error=update_failed'));
        exit;
    }
    
    public function addService() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $duration = intval($_POST['duration'] ?? 30);
            
            if (empty($name)) {
                header('Location: ' . base_url('index.php/admin/services?error=missing_name'));
                exit;
            }
            
            try {
                $success = $this->adminModel->addService($name, $description, $duration);
            } catch (Exception $e) {
                $success = false;
                
                // Fallback to direct database operation
                $stmt = $this->db->prepare("INSERT INTO services (name, description, duration) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $name, $description, $duration);
                $success = $stmt->execute();
            }
            
            if ($success) {
                header('Location: ' . base_url('index.php/admin/services?success=added'));
                exit;
            } else {
                header('Location: ' . base_url('index.php/admin/services?error=add_failed'));
                exit;
            }
        }
        
        include VIEW_PATH . '/admin/add_service.php';
    }
    
    // ✅ Manage Appointments
    public function appointments() {
        try {
            $appointments = $this->adminModel->getAllAppointments();
        } catch (Exception $e) {
            // Fallback to direct query
            $appointments = [];
            $stmt = $this->db->query("
                SELECT a.*,
                       p.first_name as patient_first_name, p.last_name as patient_last_name,
                       pr.first_name as provider_first_name, pr.last_name as provider_last_name,
                       s.name as service_name, s.duration as service_duration
                FROM appointments a
                JOIN users p ON a.patient_id = p.user_id
                JOIN users pr ON a.provider_id = pr.user_id
                JOIN services s ON a.service_id = s.service_id
                ORDER BY a.appointment_date DESC, a.start_time
            ");
            
            if ($stmt) {
                while ($row = $stmt->fetch_assoc()) {
                    $appointments[] = $row;
                }
            }
        }
        
        include VIEW_PATH . '/admin/appointments.php';
    }
    
    public function updateAppointmentStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id']) && isset($_POST['status'])) {
            $appointmentId = intval($_POST['appointment_id']);
            $status = $_POST['status'];
            
            // Validate status
            $validStatuses = ['pending', 'confirmed', 'completed', 'canceled'];
            if (!in_array($status, $validStatuses)) {
                header('Location: ' . base_url('index.php/admin/appointments?error=invalid_status'));
                exit;
            }
            
            try {
                $success = $this->adminModel->updateAppointmentStatus($appointmentId, $status);
            } catch (Exception $e) {
                $success = false;
                
                // Fallback to direct database operation
                $stmt = $this->db->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
                $stmt->bind_param("si", $status, $appointmentId);
                $success = $stmt->execute();
            }
            
            if ($success) {
                header('Location: ' . base_url('index.php/admin/appointments?success=updated'));
                exit;
            } else {
                header('Location: ' . base_url('index.php/admin/appointments?error=update_failed'));
                exit;
            }
        }
    }
}
