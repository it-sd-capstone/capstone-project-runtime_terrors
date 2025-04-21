<?php
class AdminController {
    private $db;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
        
        // Check if user is logged in and has admin role
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            // Redirect to login
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
    }
    
    public function index() {
        // Admin dashboard
        
        // Get system stats
        $stats = [
            'totalUsers' => $this->getCount('users'),
            'totalPatients' => $this->getCountByRole('patient'),
            'totalProviders' => $this->getCountByRole('provider'),
            'totalAppointments' => $this->getCount('appointments'),
            'pendingAppointments' => $this->getCountByStatus('pending'),
            'completedAppointments' => $this->getCountByStatus('completed'),
            'canceledAppointments' => $this->getCountByStatus('canceled'),
            'totalServices' => $this->getCount('services')
        ];
        
        include VIEW_PATH . '/admin/index.php';
    }
    
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
    
    public function users() {
        // Get all users
        $users = [];
        $stmt = $this->db->query("SELECT * FROM users ORDER BY role, last_name, first_name");
        if ($stmt) {
            while ($row = $stmt->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        include VIEW_PATH . '/admin/users.php';
    }
    
    public function services() {
        // Get all services
        $services = [];
        $stmt = $this->db->query("SELECT * FROM services ORDER BY name");
        if ($stmt) {
            while ($row = $stmt->fetch_assoc()) {
                $services[] = $row;
            }
        }
        
        include VIEW_PATH . '/admin/services.php';
    }
    
    public function appointments() {
        // Get all appointments with details
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
        
        include VIEW_PATH . '/admin/appointments.php';
    }
    
    public function toggleUserStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            $userId = $_POST['user_id'];
            
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
                
                if ($updateStmt->execute()) {
                    header('Location: ' . base_url('index.php/admin/users?success=updated'));
                    exit;
                }
            }
            
            header('Location: ' . base_url('index.php/admin/users?error=update_failed'));
            exit;
        }
    }
    
    public function toggleServiceStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
            $serviceId = $_POST['service_id'];
            
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
                
                if ($updateStmt->execute()) {
                    header('Location: ' . base_url('index.php/admin/services?success=updated'));
                    exit;
                }
            }
            
            header('Location: ' . base_url('index.php/admin/services?error=update_failed'));
            exit;
        }
    }
    
    public function addService() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $duration = $_POST['duration'] ?? 30;
            
            if (empty($name)) {
                header('Location: ' . base_url('index.php/admin/services?error=missing_name'));
                exit;
            }
            
            $stmt = $this->db->prepare("INSERT INTO services (name, description, duration) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $name, $description, $duration);
            
            if ($stmt->execute()) {
                header('Location: ' . base_url('index.php/admin/services?success=added'));
                exit;
            } else {
                header('Location: ' . base_url('index.php/admin/services?error=add_failed'));
                exit;
            }
        }
        
        include VIEW_PATH . '/admin/add_service.php';
    }
    
    public function updateAppointmentStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id']) && isset($_POST['status'])) {
            $appointmentId = $_POST['appointment_id'];
            $status = $_POST['status'];
            
            // Validate status
            $validStatuses = ['pending', 'confirmed', 'completed', 'canceled'];
            if (!in_array($status, $validStatuses)) {
                header('Location: ' . base_url('index.php/admin/appointments?error=invalid_status'));
                exit;
            }
            
            $stmt = $this->db->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            $stmt->bind_param("si", $status, $appointmentId);
            
            if ($stmt->execute()) {
                header('Location: ' . base_url('index.php/admin/appointments?success=updated'));
                exit;
            } else {
                header('Location: ' . base_url('index.php/admin/appointments?error=update_failed'));
                exit;
            }
        }
    }
}
