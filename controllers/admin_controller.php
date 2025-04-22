<?php
require_once MODEL_PATH . '/User.php';

class AdminController {
    private $db;
    private $userModel;
    
    public function __construct() {
        // Replace Session class with native PHP session handling
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Replace Session::isLoggedIn() with direct $_SESSION check
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        // Use get_db() function instead of Database::getConnection()
        $this->db = get_db();
        
        // Use User model instead of non-existent Admin model
        $this->userModel = new User($this->db);
    }
    
    // ✅ Admin Dashboard Overview
    public function index() {
        // Get stats using direct database queries instead of non-existent model
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
    
    public function addProvider() {
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form data
            $email = $_POST['email'] ?? '';
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $specialization = $_POST['specialization'] ?? '';
            $title = $_POST['title'] ?? '';
            $bio = $_POST['bio'] ?? '';
            
            // Basic validation
            if (empty($email) || empty($firstName) || empty($lastName)) {
                $error = 'Email, first name and last name are required';
            } else {
                // Check if email exists
                $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error = 'Email already registered';
                } else {
                    // Generate a secure temporary password
                    $tempPassword = bin2hex(random_bytes(4)); // 8 character random password
                    $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
                    
                    // Start transaction
                    $this->db->begin_transaction();
                    
                    try {
                        // Insert user
                        $stmt = $this->db->prepare("
                            INSERT INTO users 
                            (email, password_hash, first_name, last_name, phone, role, is_active, password_change_required) 
                            VALUES (?, ?, ?, ?, ?, 'provider', 1, 1)
                        ");
                        $stmt->bind_param("sssss", $email, $passwordHash, $firstName, $lastName, $phone);
                        $stmt->execute();
                        
                        $userId = $this->db->insert_id;
                        
                        // Create provider profile
                        $stmt = $this->db->prepare("
                            INSERT INTO provider_profiles 
                            (provider_id, specialization, title, bio, accepting_new_patients) 
                            VALUES (?, ?, ?, ?, 1)
                        ");
                        $stmt->bind_param("isss", $userId, $specialization, $title, $bio);
                        $stmt->execute();
                        
                        // Create notification for the provider
                        $subject = "Your Provider Account Has Been Created";
                        $message = "Hello $firstName $lastName,\n\n" .
                                   "An account has been created for you as a provider in our appointment system.\n\n" .
                                   "Your temporary login credentials are:\n" .
                                   "Email: $email\n" .
                                   "Password: $tempPassword\n\n" .
                                   "Please login and change your password as soon as possible at: " . 
                                   base_url('index.php/auth') . "\n\n" .
                                   "Thank you,\n" .
                                   "Appointment System Admin";
                        
                        $stmt = $this->db->prepare("
                            INSERT INTO notifications 
                            (user_id, subject, message, type, status, created_at) 
                            VALUES (?, ?, ?, 'email', 'pending', NOW())
                        ");
                        $stmt->bind_param("iss", $userId, $subject, $message);
                        $stmt->execute();
                        
                        $this->db->commit();
                        
                        // Display the temporary password to admin
                        $success = "Provider account created successfully! Temporary password: <strong>$tempPassword</strong>";
                    } catch (Exception $e) {
                        $this->db->rollback();
                        $error = "Error creating provider: " . $e->getMessage();
                    }
                }
            }
        }
        
        // Get all services for the checkboxes
        $services = [];
        $stmt = $this->db->query("SELECT service_id, name FROM services WHERE is_active = 1");
        if ($stmt) {
            while ($row = $stmt->fetch_assoc()) {
                $services[] = $row;
            }
        }
        
        include VIEW_PATH . '/admin/add_provider.php';
    }
    
    public function providers() {
        // Get all providers with their profile details
        $providers = [];
        $stmt = $this->db->query("
            SELECT u.*, pp.specialization, pp.title, pp.accepting_new_patients, 
                   pp.max_patients_per_day, pp.profile_image
            FROM users u
            LEFT JOIN provider_profiles pp ON u.user_id = pp.provider_id
            WHERE u.role = 'provider'
            ORDER BY u.last_name, u.first_name
        ");
        
        if ($stmt) {
            while ($row = $stmt->fetch_assoc()) {
                // Get count of services offered by this provider
                $serviceStmt = $this->db->prepare("
                    SELECT COUNT(*) as service_count 
                    FROM provider_services 
                    WHERE provider_id = ?
                ");
                $serviceStmt->bind_param("i", $row['user_id']);
                $serviceStmt->execute();
                $serviceResult = $serviceStmt->get_result();
                $serviceCount = $serviceResult->fetch_assoc()['service_count'] ?? 0;
                $row['service_count'] = $serviceCount;
                
                // Get upcoming appointment count
                $apptStmt = $this->db->prepare("
                    SELECT COUNT(*) as appointment_count 
                    FROM appointments 
                    WHERE provider_id = ? AND appointment_date >= CURDATE()
                ");
                $apptStmt->bind_param("i", $row['user_id']);
                $apptStmt->execute();
                $apptResult = $apptStmt->get_result();
                $appointmentCount = $apptResult->fetch_assoc()['appointment_count'] ?? 0;
                $row['appointment_count'] = $appointmentCount;
                
                $providers[] = $row;
            }
        }
        
        include VIEW_PATH . '/admin/providers.php';
    }
    
    public function manageProviderServices($providerId = null) {
        if (!$providerId && isset($_GET['id'])) {
            $providerId = $_GET['id'];
        }
        
        if (!$providerId) {
            header('Location: ' . base_url('index.php/admin/providers'));
            exit;
        }
        
        // Get provider details
        $stmt = $this->db->prepare("
            SELECT u.*, pp.specialization, pp.title 
            FROM users u
            LEFT JOIN provider_profiles pp ON u.user_id = pp.provider_id
            WHERE u.user_id = ? AND u.role = 'provider'
        ");
        $stmt->bind_param("i", $providerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result || $result->num_rows === 0) {
            header('Location: ' . base_url('index.php/admin/providers'));
            exit;
        }
        
        $provider = $result->fetch_assoc();
        
        // Get all available services
        $services = [];
        $serviceStmt = $this->db->query("SELECT * FROM services ORDER BY name");
        if ($serviceStmt) {
            while ($row = $serviceStmt->fetch_assoc()) {
                $services[] = $row;
            }
        }
        
        // Get provider's current services
        $providerServices = [];
        $psStmt = $this->db->prepare("
            SELECT ps.*, s.name, s.duration 
            FROM provider_services ps
            JOIN services s ON ps.service_id = s.service_id
            WHERE ps.provider_id = ?
        ");
        $psStmt->bind_param("i", $providerId);
        $psStmt->execute();
        $psResult = $psStmt->get_result();
        
        if ($psResult) {
            while ($row = $psResult->fetch_assoc()) {
                $providerServices[] = $row;
            }
        }
        
        // Process form submission to add/remove services
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_service']) && isset($_POST['service_id'])) {
                $serviceId = $_POST['service_id'];
                $customDuration = $_POST['custom_duration'] ?? null;
                $customNotes = $_POST['custom_notes'] ?? null;
                
                // Check if service already exists for this provider
                $checkStmt = $this->db->prepare("
                    SELECT * FROM provider_services 
                    WHERE provider_id = ? AND service_id = ?
                ");
                $checkStmt->bind_param("ii", $providerId, $serviceId);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                
                if ($checkResult->num_rows === 0) {
                    // Add service
                    $addStmt = $this->db->prepare("
                        INSERT INTO provider_services (provider_id, service_id, custom_duration, custom_notes)
                        VALUES (?, ?, ?, ?)
                    ");
                    $addStmt->bind_param("iiis", $providerId, $serviceId, $customDuration, $customNotes);
                    
                    if ($addStmt->execute()) {
                        header('Location: ' . base_url('index.php/admin/manageProviderServices?id=' . $providerId . '&success=added'));
                        exit;
                    }
                }
            } elseif (isset($_POST['remove_service']) && isset($_POST['provider_service_id'])) {
                $providerServiceId = $_POST['provider_service_id'];
                
                // Remove service
                $removeStmt = $this->db->prepare("
                    DELETE FROM provider_services 
                    WHERE provider_service_id = ? AND provider_id = ?
                ");
                $removeStmt->bind_param("ii", $providerServiceId, $providerId);
                
                if ($removeStmt->execute()) {
                    header('Location: ' . base_url('index.php/admin/manageProviderServices?id=' . $providerId . '&success=removed'));
                    exit;
                }
            }
        }
        
        include VIEW_PATH . '/admin/provider_services.php';
    }
    
    public function toggleAcceptingPatients() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['provider_id'])) {
            $providerId = $_POST['provider_id'];
            
            // Get current status
            $stmt = $this->db->prepare("
                SELECT accepting_new_patients 
                FROM provider_profiles 
                WHERE provider_id = ?
            ");
            $stmt->bind_param("i", $providerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $profile = $result->fetch_assoc();
                $newStatus = $profile['accepting_new_patients'] ? 0 : 1; // Toggle status
                
                // Update status
                $updateStmt = $this->db->prepare("
                    UPDATE provider_profiles 
                    SET accepting_new_patients = ? 
                    WHERE provider_id = ?
                ");
                $updateStmt->bind_param("ii", $newStatus, $providerId);
                
                if ($updateStmt->execute()) {
                    header('Location: ' . base_url('index.php/admin/providers?success=updated'));
                    exit;
                }
            }
            
            header('Location: ' . base_url('index.php/admin/providers?error=update_failed'));
            exit;
        }
    }
}
