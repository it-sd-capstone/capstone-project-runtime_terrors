<?php
require_once MODEL_PATH . '/User.php';

class AdminController {
    protected $db;
    protected $userModel;
    protected $adminModel;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
        
        // Debug message to check connection type
        error_log("Using MySQLi connection in admin_Controller");
    }
    
    // ✅ Admin Dashboard Overview
    public function index() {
        // Get stats using direct database queries instead of non-existent model
        $stats = [
            'totalUsers' => $this->getCount('users'),
            'totalPatients' => $this->getCountByRole('patient'),
            'totalProviders' => $this->getCountByRole('provider'),
            'totalAdmins' => $this->getCountByRole('admin'),
            'totalAppointments' => $this->getCount('appointments'),
            'scheduledAppointments' => $this->getCountByStatus('scheduled'),
            'confirmedAppointments' => $this->getCountByStatus('confirmed'),
            'completedAppointments' => $this->getCountByStatus('completed'),
            'canceledAppointments' => $this->getCountByStatus('canceled'),
            'noShowAppointments' => $this->getCountByStatus('no_show'),
            'totalServices' => $this->getCount('services')
        ];
        
        // Add service usage metrics
        $stats['topServices'] = $this->getTopServices(5);
        
        // Add provider availability summary
        $stats['totalAvailableSlots'] = $this->getAvailableSlotsCount();
        $stats['bookedSlots'] = $this->getBookedSlotsCount();
        $stats['availabilityRate'] = ($stats['totalAvailableSlots'] > 0) ?
            round(($stats['bookedSlots'] / $stats['totalAvailableSlots']) * 100) : 0;
        $stats['topProviders'] = $this->getTopProviders(5);
        
        // Get recent activity for the dashboard
        $stats['recentActivity'] = $this->getRecentActivity(10);
        
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
    /**
     * Get top services by usage
     * @param int $limit Number of services to return
     * @return array Top services with usage counts
     */
    private function getTopServices($limit = 5) {
        try {
            $query = "SELECT s.service_id, s.name, COUNT(a.appointment_id) as usage_count 
                    FROM services s
                    LEFT JOIN appointments a ON s.service_id = a.service_id
                    GROUP BY s.service_id, s.name
                    ORDER BY usage_count DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($query);
            // MySQLi uses bind_param instead of bindValue
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            
            // MySQLi requires you to get the result first
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Database error in getTopServices: " . $e->getMessage());
            return [];
        }
    }

    private function getBookedSlotsCount() {
        try {
            // Join appointments to availability based on provider, date and time overlap
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT a.appointment_id) as count 
                FROM appointments a
                JOIN provider_availability pa ON 
                    a.provider_id = pa.provider_id AND
                    a.appointment_date = pa.available_date AND
                    a.start_time >= pa.start_time AND
                    a.end_time <= pa.end_time
                WHERE a.status NOT IN ('canceled', 'no_show')
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return $row['count'];
            }
            return 0;
        } catch (Exception $e) {
            error_log("Database error in getBookedSlotsCount: " . $e->getMessage());
            return 0;
        }
    }
    
    private function getTopProviders($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    a.provider_id, 
                    CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                    COUNT(a.appointment_id) as appointment_count
                FROM 
                    appointments a
                    JOIN users u ON a.provider_id = u.user_id
                WHERE 
                    a.status NOT IN ('canceled', 'no_show')
                GROUP BY 
                    a.provider_id, provider_name
                ORDER BY 
                    appointment_count DESC
                LIMIT ?
            ");
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Database error in getTopProviders: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAvailableSlotsCount() {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM provider_availability
                WHERE is_available = 1
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return $row['count'];
            }
            return 0;
        } catch (Exception $e) {
            error_log("Database error in getAvailableSlotsCount: " . $e->getMessage());
            return 0;
        }
    }
    

    /**
     * Get recent system activity
     * @param int $limit Number of activities to return
     * @return array Recent activities
     */
    private function getRecentActivity($limit = 10) {
        try {
            // This assumes you have an activity_log table
            // Adjust the query based on your actual schema
            $query = "SELECT a.activity_id, a.activity_type, a.description, 
                    a.created_at as date, CONCAT(u.first_name, ' ', u.last_name) as user
                    FROM activity_log a
                    LEFT JOIN users u ON a.user_id = u.user_id
                    ORDER BY a.created_at DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($query);
            // MySQLi uses bind_param instead of bindValue
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            
            // MySQLi requires you to get the result first
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Database error in getRecentActivity: " . $e->getMessage());
            return [];
        }
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
    
    // ✅ Manage Users - Enhanced version
    public function users() {
        // Initialize User model if not already done
        if (!$this->userModel) {
            $this->userModel = new User($this->db);
        }
        
        // Get action and ID from URL parameters
        $segments = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
        $action = $segments[2] ?? 'list'; // admin/users/[action]
        $userId = $segments[3] ?? null;   // admin/users/[action]/[id]
        
        error_log("Users method called with action: $action, userId: $userId");
        
        // Check if user is admin for all actions except view/list
        if ($action != 'list' && !$this->isUserAdmin()) {
            $_SESSION['error'] = "You don't have permission to access this page";
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        // Handle different actions
        switch($action) {
            case 'delete':
                if (!$userId) {
                    $_SESSION['error'] = "User ID is required";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                // Check if user exists
                try {
                    $user = $this->userModel->getUserById($userId);
                    if (!$user) {
                        $_SESSION['error'] = "User not found";
                        header('Location: ' . base_url('index.php/admin/users'));
                        exit;
                    }
                    
                    // Make sure admin can't delete themselves
                    if ($userId == $_SESSION['user_id']) {
                        $_SESSION['error'] = "You cannot delete your own account";
                        header('Location: ' . base_url('index.php/admin/users'));
                        exit;
                    }
                    
                    // Begin transaction for safe deletion
                    $this->db->begin_transaction();
                    
                    try {
                        // Delete related records first (adjust these based on your database schema)
                        // Delete appointments
                        $stmt = $this->db->prepare("DELETE FROM appointments WHERE patient_id = ? OR provider_id = ?");
                        $stmt->bind_param("ii", $userId, $userId);
                        $stmt->execute();
                        
                        // Delete provider availability if applicable
                        if ($user['role'] === 'provider') {
                            $stmt = $this->db->prepare("DELETE FROM provider_availability WHERE provider_id = ?");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                        }
                        
                        // Delete profile data if applicable
                        if ($user['role'] === 'provider') {
                            $stmt = $this->db->prepare("DELETE FROM provider_profiles WHERE provider_id = ?");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                        } elseif ($user['role'] === 'patient') {
                            $stmt = $this->db->prepare("DELETE FROM patient_profiles WHERE patient_id = ?");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                        }
                        
                        // Finally delete the user
                        $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        
                        // If we got here, everything succeeded
                        $this->db->commit();
                        
                        // Log the deletion
                        error_log("User {$user['first_name']} {$user['last_name']} (ID: {$userId}) was deleted by admin ID: {$_SESSION['user_id']}");
                        
                        $_SESSION['success'] = "User has been permanently deleted";
                    } catch (Exception $e) {
                        // Something went wrong, rollback changes
                        $this->db->rollback();
                        error_log("Error deleting user ID {$userId}: " . $e->getMessage());
                        $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
                    }
                    
                    // Redirect back to user list
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                    
                } catch (Exception $e) {
                    error_log("Error in users/delete: " . $e->getMessage());
                    $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'edit':
                if (!$userId) {
                    $_SESSION['error'] = "User ID is required";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                try {
                    $user = $this->userModel->getUserById($userId);
                    if (!$user) {
                        $_SESSION['error'] = "User not found";
                        header('Location: ' . base_url('index.php/admin/users'));
                        exit;
                    }
                    include VIEW_PATH . '/admin/user_edit.php';
                } catch (Exception $e) {
                    error_log("Error in users/edit: " . $e->getMessage());
                    $_SESSION['error'] = "Error loading user: " . $e->getMessage();
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'view':
                if (!$userId) {
                    $_SESSION['error'] = "User ID is required";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                try {
                    $user = $this->userModel->getUserById($userId);
                    if (!$user) {
                        $_SESSION['error'] = "User not found";
                        header('Location: ' . base_url('index.php/admin/users'));
                        exit;
                    }
                    
                    // Load role-specific data if needed
                    $roleData = [];
                    if ($user['role'] === 'provider') {
                        // Get provider-specific data
                        $stmt = $this->db->prepare("SELECT * FROM provider_profiles WHERE provider_id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $roleData = $result->fetch_assoc();
                    } elseif ($user['role'] === 'patient') {
                        // Get patient-specific data
                        $stmt = $this->db->prepare("SELECT * FROM patient_profiles WHERE patient_id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $roleData = $result->fetch_assoc();
                    }
                    
                    include VIEW_PATH . '/admin/user_view.php';
                } catch (Exception $e) {
                    error_log("Error in users/view: " . $e->getMessage());
                    $_SESSION['error'] = "Error loading user: " . $e->getMessage();
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'update':
                if (!$userId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $_SESSION['error'] = "Invalid request";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                // Validate form data
                $userData = [
                    'first_name' => $_POST['first_name'] ?? '',
                    'last_name' => $_POST['last_name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'role' => $_POST['role'] ?? '',
                    'is_active' => (int)($_POST['is_active'] ?? 1)
                ];
                
                try {
                    // Check if email already exists but belongs to a different user
                    if ($this->userModel->isEmailTakenByOther($userData['email'], $userId)) {
                        $_SESSION['error'] = "Email is already in use by another user";
                        header('Location: ' . base_url('index.php/admin/users/edit/' . $userId));
                        exit;
                    }
                    
                    $result = $this->userModel->updateUser($userId, $userData);
                    
                    // Handle password update if provided
                    if (!empty($_POST['password'])) {
                        $passwordChangeRequired = isset($_POST['password_change_required']) ? 1 : 0;
                        $this->userModel->updatePassword($userId, $_POST['password'], $passwordChangeRequired);
                    }
                    
                    $_SESSION['success'] = "User updated successfully";
                    header('Location: ' . base_url('index.php/admin/users/edit/' . $userId));
                    exit;
                } catch (Exception $e) {
                    error_log("Error in users/update: " . $e->getMessage());
                    $_SESSION['error'] = "Error updating user: " . $e->getMessage();
                    header('Location: ' . base_url('index.php/admin/users/edit/' . $userId));
                    exit;
                }
                break;
                
            case 'add':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $_SESSION['error'] = "Invalid request";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                // Validate form data
                $userData = [
                    'first_name' => $_POST['first_name'] ?? '',
                    'last_name' => $_POST['last_name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'password' => $_POST['password'] ?? '',
                    'role' => $_POST['role'] ?? 'patient'
                ];
                
                // Validate data
                $errors = [];
                if (empty($userData['first_name'])) $errors[] = "First name is required";
                if (empty($userData['last_name'])) $errors[] = "Last name is required";
                if (empty($userData['email'])) $errors[] = "Email is required";
                if (empty($userData['password'])) $errors[] = "Password is required";
                
                if (!empty($errors)) {
                    $_SESSION['error'] = implode("<br>", $errors);
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                try {
                    $result = $this->userModel->register(
                        $userData['email'],
                        password_hash($userData['password'], PASSWORD_DEFAULT),
                        $userData['first_name'],
                        $userData['last_name'],
                        '', // Phone
                        $userData['role']
                    );
                    
                    $_SESSION['success'] = "User created successfully";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                } catch (Exception $e) {
                    error_log("Error in users/add: " . $e->getMessage());
                    $_SESSION['error'] = "Error creating user: " . $e->getMessage();
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'deactivate':
                if (!$userId) {
                    $_SESSION['error'] = "User ID is required";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                // Make sure admin can't deactivate themselves
                if ($userId == $_SESSION['user_id']) {
                    $_SESSION['error'] = "You cannot deactivate your own account";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                try {
                    $result = $this->userModel->updateUser($userId, ['is_active' => 0]);
                    $_SESSION['success'] = "User deactivated successfully";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                } catch (Exception $e) {
                    error_log("Error in users/deactivate: " . $e->getMessage());
                    $_SESSION['error'] = "Error deactivating user: " . $e->getMessage();
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'activate':
                if (!$userId) {
                    $_SESSION['error'] = "User ID is required";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                try {
                    $result = $this->userModel->updateUser($userId, ['is_active' => 1]);
                    $_SESSION['success'] = "User activated successfully";
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                } catch (Exception $e) {
                    error_log("Error in users/activate: " . $e->getMessage());
                    $_SESSION['error'] = "Error activating user: " . $e->getMessage();
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'list':
            default:
                // Get search parameters
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                $role = isset($_GET['role']) ? trim($_GET['role']) : '';
                $status = isset($_GET['status']) ? trim($_GET['status']) : '';
                
                // Build query conditions
                $conditions = [];
                $params = [];
                
                if (!empty($search)) {
                    $conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }
                
                if (!empty($role)) {
                    $conditions[] = "role = ?";
                    $params[] = $role;
                }
                
                if ($status !== '') {
                    $conditions[] = "is_active = ?";
                    $params[] = ($status === 'active') ? 1 : 0;
                }
                
                // Combine conditions
                $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";
                
                // Get users with filters
                $users = $this->userModel->getAllUsersWithFilters($whereClause, $params);
                
                // Load view with data
                include VIEW_PATH . '/admin/users.php';
                break;
        }
    }

    // ✅ Manage Services
    public function services($action = null, $id = null) {
        // If action is specified (add, edit, delete)
        if ($action) {
            if ($action === 'add') {
                // Handle form submission for adding a new service
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $name = $_POST['name'] ?? '';
                    $description = $_POST['description'] ?? '';
                    $price = $_POST['price'] ?? 0;

                    // Basic validation
                    $errors = [];
                    if (empty($name)) {
                        $errors[] = "Service name is required";
                    }
                    if (empty($description)) {
                        $errors[] = "Description is required";
                    }
                    if (!is_numeric($price) || $price <= 0) {
                        $errors[] = "Price must be a positive number";
                    }

                    if (empty($errors)) {
                        // Insert new service
                        $query = "INSERT INTO services (name, description, price) VALUES (?, ?, ?)";
                        $stmt = $this->db->prepare($query);
                        $stmt->bind_param("ssd", $name, $description, $price);
                        
                        if ($stmt->execute()) {
                            $_SESSION['success'] = "Service added successfully";
                        } else {
                            $_SESSION['error'] = "Failed to add service: " . $this->db->error;
                        }
                    } else {
                        $_SESSION['error'] = implode("<br>", $errors);
                    }
                    
                    // Redirect back to services page
                    header('Location: ' . base_url('index.php/admin/services'));
                    exit;
                }
            } elseif ($action === 'edit' && $id) {
                // Handle form submission for editing a service
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $name = $_POST['name'] ?? '';
                    $description = $_POST['description'] ?? '';
                    $price = $_POST['price'] ?? 0;

                    // Basic validation
                    $errors = [];
                    if (empty($name)) {
                        $errors[] = "Service name is required";
                    }
                    if (empty($description)) {
                        $errors[] = "Description is required";
                    }
                    if (!is_numeric($price) || $price <= 0) {
                        $errors[] = "Price must be a positive number";
                    }

                    if (empty($errors)) {
                        // Update service
                        $query = "UPDATE services SET name = ?, description = ?, price = ? WHERE service_id = ?";
                        $stmt = $this->db->prepare($query);
                        $stmt->bind_param("ssdi", $name, $description, $price, $id);
                        
                        if ($stmt->execute()) {
                            $_SESSION['success'] = "Service updated successfully";
                        } else {
                            $_SESSION['error'] = "Failed to update service: " . $this->db->error;
                        }
                    } else {
                        $_SESSION['error'] = implode("<br>", $errors);
                    }
                    
                    // Redirect back to services page
                    header('Location: ' . base_url('index.php/admin/services'));
                    exit;
                }

                // Get service details for editing
                $query = "SELECT * FROM services WHERE service_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $service = $result->fetch_assoc();
                    
                    // Display edit form
                    $data = [
                        'service' => $service
                    ];
                    include VIEW_PATH . '/admin/edit_service.php';
                    return;
                } else {
                    $_SESSION['error'] = "Service not found";
                    header('Location: ' . base_url('index.php/admin/services'));
                    exit;
                }
            } elseif ($action === 'delete' && $id) {
                // Delete service
                $query = "DELETE FROM services WHERE service_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $_SESSION['success'] = "Service deleted successfully";
                } else {
                    $_SESSION['error'] = "Failed to delete service or service not found";
                }
                
                // Redirect back to services page
                header('Location: ' . base_url('index.php/admin/services'));
                exit;
            }
        }
        
        // Get all services for display
        $query = "SELECT * FROM services ORDER BY name";
        $result = $this->db->query($query);
        $services = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
        }
        
        $data = [
            'services' => $services
        ];
        
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
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            
            // Validate inputs
            $errors = [];
            
            if (empty($name)) {
                $errors[] = "Service name is required";
            }
            
            if (empty($description)) {
                $errors[] = "Description is required";
            }
            
            if (empty($price) || !is_numeric($price)) {
                $errors[] = "Valid price is required";
            }
            
            // If no errors, insert service
            if (empty($errors)) {
                $query = "INSERT INTO services (name, description, price) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ssd", $name, $description, $price);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Service added successfully";
                    header('Location: ' . base_url('index.php/admin/services'));
                    exit;
                } else {
                    $errors[] = "Error adding service: " . $this->db->error;
                }
            }
            
            // If there are errors, include them in the view
            $_SESSION['errors'] = $errors;
        }
        
        // Redirect back to services page
        header('Location: ' . base_url('index.php/admin/services'));
        exit;
    }
    /**
     * Get all patients (users with role 'patient')
     */
    public function getPatients() {
        try {
            $query = "
                SELECT user_id, CONCAT(first_name, ' ', last_name) AS full_name
                FROM users
                WHERE role = 'patient' AND is_active = 1
                ORDER BY first_name, last_name
            ";
            error_log("Executing query: " . $query);
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            // If using MySQLi
            $result = $stmt->get_result();
            if (!$result) {
                error_log("MySQLi error: " . $this->db->error);
                return [];
            }
            
            $patients = [];
            while ($row = $result->fetch_assoc()) {
                $patients[] = $row;
            }
            
            error_log("Found " . count($patients) . " patients");
            return $patients;
        } catch (Exception $e) {
            error_log("Exception in getPatients: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Get all healthcare providers (users with role 'provider')
     */
    public function getProviders() {
        try {
            $stmt = $this->db->prepare("
                SELECT user_id, CONCAT(first_name, ' ', last_name) AS full_name
                FROM users
                WHERE role = 'provider' AND is_active = 1
                ORDER BY first_name, last_name
            ");
            $stmt->execute();
            
            // If using MySQLi
            $result = $stmt->get_result();
            $providers = [];
            while ($row = $result->fetch_assoc()) {
                $providers[] = $row;
            }
            return $providers;
            
            // If using PDO, uncomment this and comment the above code
            // return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching providers: " . $e->getMessage());
            return [];
        }
    }

   /**
     * Get all available services
     */
    public function getServices() {
        try {
            $stmt = $this->db->prepare("
                SELECT service_id, name, description, duration, price
                FROM services
                WHERE is_active = 1 OR is_active IS NULL
                ORDER BY name
            ");
            $stmt->execute();
            
            // If using MySQLi
            $result = $stmt->get_result();
            $services = [];
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
            
            error_log("Found " . count($services) . " services");
            return $services;
        } catch (Exception $e) {
            error_log("Error fetching services: " . $e->getMessage());
            return [];
        }
    }

    // ✅ Manage Appointments
    public function appointments($action = 'list', $id = null) {
        // If action is specified (add, edit, cancel)
        if ($action) {
            if ($action === 'add') {
                // Handle form submission for adding a new appointment
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $patient_id = $_POST['patient_id'] ?? '';
                    $provider_id = $_POST['provider_id'] ?? '';
                    $service_id = $_POST['service_id'] ?? '';
                    $appointment_date = $_POST['appointment_date'] ?? '';
                    $start_time = $_POST['appointment_time'] ?? ''; // Form field name
                    $status = $_POST['status'] ?? 'scheduled'; // Changed from 'pending' to 'scheduled'
                    $type = $_POST['type'] ?? 'in_person'; // Added type field
                    $notes = $_POST['notes'] ?? '';
                    $reason = $_POST['reason'] ?? '';
                    
                    // Calculate end time (30 minutes after start time)
                    $end_time = date('H:i:s', strtotime($start_time . ' +30 minutes'));
                    
                    // Basic validation
                    $errors = [];
                    if (empty($patient_id)) {
                        $errors[] = "Patient is required";
                    }
                    if (empty($provider_id)) {
                        $errors[] = "Provider is required";
                    }
                    if (empty($service_id)) {
                        $errors[] = "Service is required";
                    }
                    if (empty($appointment_date)) {
                        $errors[] = "Appointment date is required";
                    }
                    if (empty($start_time)) { // Fixed variable name
                        $errors[] = "Appointment time is required";
                    }
                    if (empty($errors)) {
                        // Check if provider is available at that time
                        $datetime = $appointment_date . ' ' . $start_time;
                        
                        // Insert new appointment with all required fields
                        $query = "INSERT INTO appointments (
                            patient_id, provider_id, service_id, 
                            appointment_date, start_time, end_time,
                            status, type, notes, reason
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->db->prepare($query);
                        $stmt->bind_param(
                            "iiisssssss", 
                            $patient_id, $provider_id, $service_id,
                            $appointment_date, $start_time, $end_time,
                            $status, $type, $notes, $reason
                        );
                        
                        if ($stmt->execute()) {
                            $_SESSION['success'] = "Appointment added successfully";
                        } else {
                            $_SESSION['error'] = "Failed to add appointment: " . $this->db->error;
                        }
                    } else {
                        $_SESSION['error'] = implode("<br>", $errors);
                    }
                    
                    // Redirect back to appointments page
                    header('Location: ' . base_url('index.php/admin/appointments'));
                    exit;
                }
            } elseif ($action === 'edit' && $id) {
                // Handle form submission for editing an appointment
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $patient_id = $_POST['patient_id'] ?? '';
                    $provider_id = $_POST['provider_id'] ?? '';
                    $service_id = $_POST['service_id'] ?? '';
                    $appointment_date = $_POST['appointment_date'] ?? '';
                    $start_time = $_POST['appointment_time'] ?? ''; // Fixed variable name
                    $status = $_POST['status'] ?? 'scheduled'; // Changed from 'pending' to 'scheduled'
                    $type = $_POST['type'] ?? 'in_person'; // Added type field
                    $notes = $_POST['notes'] ?? '';
                    $reason = $_POST['reason'] ?? '';
                    
                    // Calculate end time (30 minutes after start time)
                    $end_time = date('H:i:s', strtotime($start_time . ' +30 minutes'));
                    
                    // Basic validation
                    $errors = [];
                    if (empty($patient_id)) {
                        $errors[] = "Patient is required";
                    }
                    if (empty($provider_id)) {
                        $errors[] = "Provider is required";
                    }
                    if (empty($service_id)) {
                        $errors[] = "Service is required";
                    }
                    if (empty($appointment_date)) {
                        $errors[] = "Appointment date is required";
                    }
                    if (empty($start_time)) { // Fixed variable name
                        $errors[] = "Appointment time is required";
                    }
                    if (empty($errors)) {
                        // Update appointment with all required fields
                        $query = "UPDATE appointments SET 
                            patient_id = ?, provider_id = ?, service_id = ?,
                            appointment_date = ?, start_time = ?, end_time = ?, 
                            status = ?, type = ?, notes = ?, reason = ? 
                            WHERE appointment_id = ?";
                        $stmt = $this->db->prepare($query);
                        $stmt->bind_param(
                            "iiissssssi", 
                            $patient_id, $provider_id, $service_id,
                            $appointment_date, $start_time, $end_time,
                            $status, $type, $notes, $reason, $id
                        );
                        
                        if ($stmt->execute()) {
                            $_SESSION['success'] = "Appointment updated successfully";
                        } else {
                            $_SESSION['error'] = "Failed to update appointment: " . $this->db->error;
                        }
                    } else {
                        $_SESSION['error'] = implode("<br>", $errors);
                    }
                    
                    // Redirect back to appointments page
                    header('Location: ' . base_url('index.php/admin/appointments'));
                    exit;
                }
                // Get appointment details for editing
                try {
                    // Get appointment details
                    $appointmentModel = new Appointment($this->db);
                    $appointment = $appointmentModel->getAppointmentById($id);
                    
                    if (!$appointment) {
                        $_SESSION['error'] = "Appointment not found";
                        header('Location: ' . base_url('index.php/admin/appointments'));
                        exit;
                    }
                    
                    // Get dropdown data - add debugging
                    $patients = $this->getPatients();
                    error_log("Patients data: " . print_r($patients, true));
                    
                    $providers = $this->getProviders();
                    error_log("Providers data: " . print_r($providers, true));
                    
                    $services = $this->getServices();
                    error_log("Services data: " . print_r($services, true));
                    
                    // Load the view
                    include VIEW_PATH . '/admin/edit_appointment.php';
                    return;
                } catch (Exception $e) {
                    error_log("Error in appointments edit: " . $e->getMessage());
                    $_SESSION['error'] = "Error loading appointment form: " . $e->getMessage();
                    header('Location: ' . base_url('index.php/admin/appointments'));
                    exit;
                }
            } elseif ($action === 'cancel' && $id) {
                // Cancel appointment (update status to canceled)
                $query = "UPDATE appointments SET status = 'canceled', canceled_at = NOW() WHERE appointment_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $_SESSION['success'] = "Appointment canceled successfully";
                } else {
                    $_SESSION['error'] = "Failed to cancel appointment or appointment not found";
                }
                
                // Redirect back to appointments page
                header('Location: ' . base_url('index.php/admin/appointments'));
                exit;
            }
        }
        
        // Get all appointments for display with patient and provider names
        $query = "SELECT a.*,
            CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
            CONCAT(pr.first_name, ' ', pr.last_name) AS provider_name,
            s.name AS service_name
            FROM appointments a
            JOIN users p ON a.patient_id = p.user_id
            JOIN users pr ON a.provider_id = pr.user_id
            JOIN services s ON a.service_id = s.service_id
            ORDER BY a.appointment_date DESC, a.start_time DESC";
        $result = $this->db->query($query);
        $appointments = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
        
        // Get lists of patients, providers, and services for the add form
        $patients = $this->getPatients();
        $providers = $this->getProviders();
        $services = $this->getServices();
        
        $data = [
            'appointments' => $appointments,
            'patients' => $patients,
            'providers' => $providers,
            'services' => $services
        ];
        
        include VIEW_PATH . '/admin/appointments.php';
    }

    
    public function updateAppointmentStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id']) && isset($_POST['status'])) {
            $appointmentId = intval($_POST['appointment_id']);
            $status = $_POST['status'];
            
            // Update valid statuses to match your database schema
            $validStatuses = ['scheduled', 'confirmed', 'completed', 'canceled', 'no_show'];
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
                    // Add the service
                    $addStmt = $this->db->prepare("
                        INSERT INTO provider_services
                        (provider_id, service_id, custom_duration, notes)
                        VALUES (?, ?, ?, ?)
                    ");
                    $addStmt->bind_param("iiis", $providerId, $serviceId, $customDuration, $customNotes);
                    $success = $addStmt->execute();
                    
                    if ($success) {
                        header('Location: ' . base_url('index.php/admin/providers/services/' . $providerId . '?success=added'));
                        exit;
                    }
                } else {
                    header('Location: ' . base_url('index.php/admin/providers/services/' . $providerId . '?error=already_exists'));
                    exit;
                }
            } elseif (isset($_POST['remove_service']) && isset($_POST['provider_service_id'])) {
                $providerServiceId = $_POST['provider_service_id'];
                
                // Remove the service
                $removeStmt = $this->db->prepare("
                    DELETE FROM provider_services
                    WHERE id = ? AND provider_id = ?
                ");
                $removeStmt->bind_param("ii", $providerServiceId, $providerId);
                $success = $removeStmt->execute();
                
                if ($success) {
                    header('Location: ' . base_url('index.php/admin/providers/services/' . $providerId . '?success=removed'));
                    exit;
                }
            }
        }
        
        include VIEW_PATH . '/admin/provider_services.php';
    }
    
    public function editProvider($providerId = null) {
        if (!$providerId && isset($_GET['id'])) {
            $providerId = $_GET['id'];
        }
        
        if (!$providerId) {
            header('Location: ' . base_url('index.php/admin/providers'));
            exit;
        }
        
        // Get provider details
        $stmt = $this->db->prepare("
            SELECT u.*, pp.*
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
        
        // Process form submission
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $specialization = $_POST['specialization'] ?? '';
            $title = $_POST['title'] ?? '';
            $bio = $_POST['bio'] ?? '';
            $acceptingNewPatients = isset($_POST['accepting_new_patients']) ? 1 : 0;
            $maxPatientsPerDay = $_POST['max_patients_per_day'] ?? null;
            
            // Validate basic info
            if (empty($firstName) || empty($lastName) || empty($email)) {
                $error = 'First name, last name, and email are required';
            } else {
                // Check if email is taken by another user
                $emailStmt = $this->db->prepare("
                    SELECT user_id FROM users
                    WHERE email = ? AND user_id != ?
                ");
                $emailStmt->bind_param("si", $email, $providerId);
                $emailStmt->execute();
                $emailResult = $emailStmt->get_result();
                
                if ($emailResult->num_rows > 0) {
                    $error = 'Email is already taken by another user';
                } else {
                    // Update user record
                    $this->db->begin_transaction();
                    
                    try {
                        // Update basic user info
                        $userStmt = $this->db->prepare("
                            UPDATE users SET
                            first_name = ?,
                            last_name = ?,
                            email = ?,
                            phone = ?
                            WHERE user_id = ?
                        ");
                        $userStmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $providerId);
                        $userStmt->execute();
                        
                        // Update provider profile
                        $profileStmt = $this->db->prepare("
                            UPDATE provider_profiles SET
                            specialization = ?,
                            title = ?,
                            bio = ?,
                            accepting_new_patients = ?,
                            max_patients_per_day = ?
                            WHERE provider_id = ?
                        ");
                        $profileStmt->bind_param("sssiii", $specialization, $title, $bio, $acceptingNewPatients, $maxPatientsPerDay, $providerId);
                        $profileStmt->execute();
                        
                        // Handle password change if provided
                        if (!empty($_POST['password'])) {
                            $password = $_POST['password'];
                            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                            
                            $passwordStmt = $this->db->prepare("
                                UPDATE users SET
                                password_hash = ?,
                                password_change_required = 0
                                WHERE user_id = ?
                            ");
                            $passwordStmt->bind_param("si", $passwordHash, $providerId);
                            $passwordStmt->execute();
                        }
                        
                        // Handle profile image upload if provided
                        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                            $uploadDir = UPLOAD_PATH . '/profiles/';
                            
                            // Create directory if it doesn't exist
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            
                            $fileName = $providerId . '_' . time() . '_' . basename($_FILES['profile_image']['name']);
                            $uploadFile = $uploadDir . $fileName;
                            
                            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
                                // Update profile image path in database
                                $imageStmt = $this->db->prepare("
                                    UPDATE provider_profiles SET
                                    profile_image = ?
                                    WHERE provider_id = ?
                                ");
                                $imagePath = 'uploads/profiles/' . $fileName;
                                $imageStmt->bind_param("si", $imagePath, $providerId);
                                $imageStmt->execute();
                            }
                        }
                        
                        $this->db->commit();
                        $success = 'Provider information updated successfully';
                        
                        // Refresh provider data
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $provider = $result->fetch_assoc();
                    } catch (Exception $e) {
                        $this->db->rollback();
                        $error = 'Error updating provider: ' . $e->getMessage();
                    }
                }
            }
        }
        
        include VIEW_PATH . '/admin/edit_provider.php';
    }
    
    public function manageAvailability($providerId = null) {
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
        
        // Get existing availability
        $availability = [];
        $availStmt = $this->db->prepare("
            SELECT * FROM provider_availability
            WHERE provider_id = ?
            ORDER BY availability_date, start_time
        ");
        $availStmt->bind_param("i", $providerId);
        $availStmt->execute();
        $availResult = $availStmt->get_result();
        
        if ($availResult) {
            while ($row = $availResult->fetch_assoc()) {
                $availability[] = $row;
            }
        }
        
        // Process form submission
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_availability'])) {
                // Add new availability
                $availabilityDate = $_POST['availability_date'] ?? null;
                $startTime = $_POST['start_time'] ?? null;
                $endTime = $_POST['end_time'] ?? null;
                $maxAppointments = $_POST['max_appointments'] ?? 1;
                
                if (empty($availabilityDate) || empty($startTime) || empty($endTime)) {
                    $error = 'Date, start time and end time are required';
                } else {
                    // Validate time format and range
                    if (strtotime($startTime) >= strtotime($endTime)) {
                        $error = 'Start time must be before end time';
                    } else {
                        // Check for overlapping availability
                        $overlapStmt = $this->db->prepare("
                            SELECT * FROM provider_availability
                            WHERE provider_id = ? AND availability_date = ? AND
                            ((start_time <= ? AND end_time > ?) OR
                             (start_time < ? AND end_time >= ?) OR
                             (start_time >= ? AND end_time <= ?))
                        ");
                        $overlapStmt->bind_param("isssssss", $providerId, $availabilityDate, $endTime, $startTime, $endTime, $startTime, $startTime, $endTime);
                        $overlapStmt->execute();
                        $overlapResult = $overlapStmt->get_result();
                        
                        if ($overlapResult->num_rows > 0) {
                            $error = 'This availability overlaps with an existing time slot';
                        } else {
                            // Insert new availability
                            $insertStmt = $this->db->prepare("
                                INSERT INTO provider_availability
                                (provider_id, availability_date, start_time, end_time, max_appointments)
                                VALUES (?, ?, ?, ?, ?)
                            ");
                            $insertStmt->bind_param("isssi", $providerId, $availabilityDate, $startTime, $endTime, $maxAppointments);
                            $success = $insertStmt->execute();
                            
                            if ($success) {
                                $success = 'Availability added successfully';
                                
                                // Refresh availability list
                                $availStmt->execute();
                                $availResult = $availStmt->get_result();
                                $availability = [];
                                if ($availResult) {
                                    while ($row = $availResult->fetch_assoc()) {
                                        $availability[] = $row;
                                    }
                                }
                            } else {
                                $error = 'Failed to add availability: ' . $this->db->error;
                            }
                        }
                    }
                }
            } elseif (isset($_POST['delete_availability']) && isset($_POST['availability_id'])) {
                // Delete availability
                $availabilityId = $_POST['availability_id'];
                
                // Check if there are any appointments booked for this slot
                $apptStmt = $this->db->prepare("
                    SELECT COUNT(*) as count FROM appointments
                    WHERE availability_id = ?
                ");
                $apptStmt->bind_param("i", $availabilityId);
                $apptStmt->execute();
                $apptResult = $apptStmt->get_result();
                $apptCount = $apptResult->fetch_assoc()['count'];
                
                if ($apptCount > 0) {
                    $error = 'Cannot delete availability with booked appointments';
                } else {
                    // Delete the availability
                    $deleteStmt = $this->db->prepare("
                        DELETE FROM provider_availability
                        WHERE availability_id = ? AND provider_id = ?
                    ");
                    $deleteStmt->bind_param("ii", $availabilityId, $providerId);
                    $success = $deleteStmt->execute();
                    
                    if ($success) {
                        $success = 'Availability deleted successfully';
                        
                        // Refresh availability list
                        $availStmt->execute();
                        $availResult = $availStmt->get_result();
                        $availability = [];
                        if ($availResult) {
                            while ($row = $availResult->fetch_assoc()) {
                                $availability[] = $row;
                            }
                        }
                    } else {
                        $error = 'Failed to delete availability: ' . $this->db->error;
                    }
                }
            }
        }
        
        include VIEW_PATH . '/admin/manage_availability.php';
    }
    
    private function isUserAdmin() {
        return isset($_SESSION['user_id'], $_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}
