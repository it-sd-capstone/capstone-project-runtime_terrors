<?php
require_once __DIR__ . '/../helpers/system_notifications.php';
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/ActivityLog.php';
require_once MODEL_PATH . '/Appointment.php';
require_once MODEL_PATH . '/Services.php';
require_once MODEL_PATH . '/Provider.php';

class AdminController {
    protected $db;
    protected $userModel;
    protected $adminModel;
    protected $activityLogModel;
    protected $appointmentModel;
    protected $serviceModel;
    protected $providerModel;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
        
        // Initialize models
        $this->userModel = new User($this->db);
        $this->activityLogModel = new ActivityLog($this->db);
        $this->appointmentModel = new Appointment($this->db);
        $this->serviceModel = new Services($this->db);
        $this->providerModel = new Provider($this->db);
        
        error_log("Using models in AdminController");
    }    
    
    // ✅ Admin Dashboard Overview
    public function index() {
        // Get stats using models instead of direct queries
        $stats = [
            'totalUsers' => $this->userModel->getTotalCount(),
            'totalPatients' => $this->userModel->getCountByRole('patient'),
            'totalProviders' => $this->userModel->getCountByRole('provider'),
            'totalAdmins' => $this->userModel->getCountByRole('admin'),
            'totalAppointments' => $this->appointmentModel->getTotalCount(),
            'scheduledAppointments' => $this->appointmentModel->getCountByStatus('scheduled'),
            'confirmedAppointments' => $this->appointmentModel->getCountByStatus('confirmed'),
            'completedAppointments' => $this->appointmentModel->getCountByStatus('completed'),
            'canceledAppointments' => $this->appointmentModel->getCountByStatus('canceled'),
            'noShowAppointments' => $this->appointmentModel->getCountByStatus('no_show'),
            'totalServices' => $this->serviceModel->getTotalCount()
        ];
        
        // Add service usage metrics
        $stats['topServices'] = $this->serviceModel->getTopServicesByUsage(5);
        
        // Add provider availability summary
        $stats['totalAvailableSlots'] = $this->providerModel->getAvailableSlotsCount();
        $stats['bookedSlots'] = $this->appointmentModel->getBookedSlotsCount();
        $stats['availabilityRate'] = ($stats['totalAvailableSlots'] > 0) ?
            round(($stats['bookedSlots'] / $stats['totalAvailableSlots']) * 100) : 0;
        $stats['topProviders'] = $this->providerModel->getTopProviders(5);
        
        // Get recent activity for the dashboard
        $stats['recentActivity'] = $this->activityLogModel->getRecentActivity(10);
        
        include VIEW_PATH . '/admin/index.php';
    }
    
    /**
     * Get appointment analytics data for charts
     * 
     * @param string $period 'weekly', 'monthly', or 'yearly'
     * @return json JSON response with appointment data
     */
    public function getAppointmentAnalytics($period = 'monthly') {
        // Log analytics request as system notification
        $notification = new Notification($this->db);
        $notification->create([
            'subject' => 'Analytics Generated',
            'message' => "Appointment analytics generated for period: " . $period,
            'type' => 'analytics_generated',
            'is_system' => 1,
            'audience' => 'admin'
        ]);

        // Log that the method was called
        error_log("AdminController::getAppointmentAnalytics called with period: " . $period);
        
        // Validate period parameter
        $validPeriods = ['weekly', 'monthly', 'yearly'];
        if (!in_array($period, $validPeriods)) {
            $period = 'monthly';
        }
        
        // Use your framework's method to load the Appointment model
        // Instead of $this->load->model('Appointment')
        $appointmentModel = new Appointment($this->db);
        
        // Get appointment data
        $appointmentsByPeriod = $appointmentModel->getAppointmentsByPeriod($period);
        $statusCounts = $appointmentModel->getAppointmentStatusCounts();
        
        // Format data for charts
        $trendLabels = array_column($appointmentsByPeriod, 'period_name');
        $trendData = array_column($appointmentsByPeriod, 'appointment_count');
        
        $statusLabels = array_keys($statusCounts);
        $statusData = array_values($statusCounts);
        
        // Prepare response data
        $responseData = [
            'success' => true,
            'period' => $period,
            'trends' => [
                'labels' => $trendLabels,
                'data' => $trendData
            ],
            'status' => [
                'labels' => $statusLabels,
                'data' => $statusData
            ]
        ];
        
        // Set content type header
        header('Content-Type: application/json');
        
        // Return JSON response
        echo json_encode($responseData);
        exit;
    }

    // ✅ Manage Users - Enhanced version
    public function users() {
        // Get the full REQUEST_URI
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Log the original REQUEST_URI for debugging
        error_log("Original REQUEST_URI: " . $uri);
        
        // Parse the URI properly (this preserves slashes)
        $path = parse_url($uri, PHP_URL_PATH);
        error_log("Parsed path: " . $path);

        
        // Extract the part after "index.php" regardless of where it appears in the URL
        if (strpos($path, 'index.php') !== false) {
            $parts = explode('index.php', $path, 2);
            $path = $parts[1] ?? '';
        }
        

        $segments = array_values(array_filter(explode('/', $path)));
        error_log("Segments: " . print_r($segments, true));
        
        // Determine action and userID based on segments
        // URLs should now be correctly parsed like: /admin/users/view/33

        $action = isset($segments[2]) ? $segments[2] : 'list';
        $userId = isset($segments[3]) ? $segments[3] : null;
        error_log("Final action: $action, userId: $userId");

        // Check if user is admin for all actions except view/list
        if ($action != 'list' && !$this->isUserAdmin()) {
            set_flash_message('error', "You don't have permission to access this page", 'auth_login');
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }

        // Handle different actions
        switch($action) {
            case 'delete':
                if (!$userId) {
                    set_flash_message('error', "User ID is required", 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                // Check if user exists
                try {
                    $user = $this->userModel->getUserById($userId);
                    if (!$user) {
                    set_flash_message('error', "User not found", 'admin_users');
                        header('Location: ' . base_url('index.php/admin/users'));
                        exit;
                    }
                    
                    // Make sure admin can't delete themselves
                    if ($userId == $_SESSION['user_id']) {
                    set_flash_message('error', "You cannot delete your own account", 'admin_users');
                        header('Location: ' . base_url('index.php/admin/users'));
                        exit;
                    }
                    
                    // Replace multiple delete calls with one comprehensive deletion
                    $success = $this->userModel->deleteUserComprehensive($userId);

                    if ($success) {
                        // Log the activity
                        $this->activityLogModel->logUserDeletion($userId, $_SESSION['user_id']);
                    set_flash_message('success', "User has been permanently deleted", 'admin_users');
                    } else {
                    set_flash_message('error', "Failed to delete user. Check server logs for details.", 'admin_users');
                    }
                    
                    // Redirect back to user list
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                    
                } catch (Exception $e) {
                    error_log("Error in users/delete: " . $e->getMessage());
                    set_flash_message('error', "Error deleting user: " . $e->getMessage(), 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'edit':
                if (!$userId) {
                set_flash_message('error', "User ID is required", 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                try {
                    $user = $this->userModel->getUserById($userId);
                    if (!$user) {
                    set_flash_message('error', "User not found", 'admin_users');
                        header('Location: ' . base_url('index.php/admin/users'));
                        exit;
                    }
                    include VIEW_PATH . '/admin/user_edit.php';
                } catch (Exception $e) {
                    error_log("Error in users/edit: " . $e->getMessage());
                    set_flash_message('error', "Error loading user: " . $e->getMessage(), 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'view':
                if (!$userId) {
                    set_flash_message('error', "User ID is required", 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                try {
                    $user = $this->userModel->getUserById($userId);
                    error_log("User data for ID $userId: " . print_r($user, true));
                    if (!$user) {
                        set_flash_message('error', "User not found", 'admin_users');
                        header('Location: ' . base_url('index.php/admin/users'));
                        exit;
                    }
                    
                    // Load role-specific data if needed
                    $roleData = [];
                    if ($user['role'] === 'provider') {
                        $roleData = $this->providerModel->getProviderById($userId);
                    } elseif ($user['role'] === 'patient') {
                        $roleData = $this->userModel->getPatientProfile($userId);
                    }
                    
                    include VIEW_PATH . '/admin/user_view.php';
                } catch (Exception $e) {
                    error_log("Error in users/view: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
                    set_flash_message('error', "Error loading user: " . $e->getMessage(), 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'update':
                if (!$userId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                set_flash_message('error', "Invalid request", 'admin_users');
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
                    set_flash_message('error', "Email is already in use by another user", 'admin_users');
                        header('Location: ' . base_url('index.php/admin/users/edit/' . $userId));
                        exit;
                    }
                    
                    $result = $this->userModel->updateUser($userId, $userData);
                    
                    // Handle password update if provided
                    if (!empty($_POST['password'])) {
                        $passwordChangeRequired = isset($_POST['password_change_required']) ? 1 : 0;
                        $this->userModel->updatePassword($userId, $_POST['password'], $passwordChangeRequired);
                    }
                    
                    set_flash_message('success', "User updated successfully", 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users/edit/' . $userId));
                    exit;
                } catch (Exception $e) {
                    error_log("Error in users/update: " . $e->getMessage());
                    set_flash_message('error', "Error updating user: " . $e->getMessage(), 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users/edit/' . $userId));
                    exit;
                }
                break;
                
            case 'add':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    set_flash_message('error', "Invalid request", 'admin_users');
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
                    set_flash_message('error', implode("<br>", $errors), 'admin_users');
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
                    
                    set_flash_message('success', "User created successfully", 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                } catch (Exception $e) {
                    error_log("Error in users/add: " . $e->getMessage());
                    set_flash_message('error', "Error creating user: " . $e->getMessage(), 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'deactivate':
                if (!$userId) {
                    set_flash_message('error', "User ID is required", 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                // Make sure admin can't deactivate themselves
                if ($userId == $_SESSION['user_id']) {
                    set_flash_message('error', "You cannot deactivate your own account", 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                try {
                    $result = $this->userModel->updateUser($userId, ['is_active' => 0]);
                    set_flash_message('success', "User deactivated successfully", 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                } catch (Exception $e) {
                    error_log("Error in users/deactivate: " . $e->getMessage());
                    set_flash_message('error', "Error deactivating user: " . $e->getMessage(), 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                break;
                
            case 'activate':
                if (!$userId) {
                    set_flash_message('error', "User ID is required", 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                }
                
                try {
                    $result = $this->userModel->updateUser($userId, ['is_active' => 1]);
                    set_flash_message('success', "User activated successfully", 'admin_users');
                    header('Location: ' . base_url('index.php/admin/users'));
                    exit;
                } catch (Exception $e) {
                    error_log("Error in users/activate: " . $e->getMessage());
                    set_flash_message('error', "Error activating user: " . $e->getMessage(), 'admin_users');
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

    // Manage Services
    public function services($action = null, $id = null) {
        error_log("AdminController::services called with action: " . ($action ?? 'null') . 
              ", id: " . ($id ?? 'null') . 
              ", method: " . $_SERVER['REQUEST_METHOD']);
              
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("POST data received: " . json_encode($_POST));
        }
        
        // If action is specified (add, edit, delete)
        if ($action === 'add') {
            // Handle form submission for adding a new service
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                error_log("Processing add service form submission");
                
                // Verify CSRF token
                if (!verify_csrf_token()) {
                    error_log("CSRF token verification failed");
                    set_flash_message('error', "Security validation failed. Please try again.", 'admin_services');
                    header('Location: ' . base_url('index.php/admin/services'));
                    exit;
                }
                
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $price = $_POST['price'] ?? 0;
                $duration = $_POST['duration'] ?? 30; // Default duration
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                error_log("Service data: name=$name, price=$price, duration=$duration");
                
                // Basic validation
                $errors = [];
                if (empty($name)) {
                    $errors[] = "Service name is required";
                }
                if (empty($description)) {
                    $errors[] = "Description is required";
                }
                if (!is_numeric($price) || $price < 0) {
                    $errors[] = "Price must be a non-negative number";
                }
                
                if (empty($errors)) {
                    // Create service data array
                    $serviceData = [
                        'name' => $name,
                        'description' => $description,
                        'price' => $price,
                        'duration' => $duration,
                        'is_active' => $is_active
                    ];
                    
                    error_log("Creating service with data: " . json_encode($serviceData));
                    
                    // Use the service model to create the service
                    $result = $this->serviceModel->createService($serviceData);
                    
                    error_log("Create service result: " . ($result ? "success ($result)" : "failure"));
                    
                    if ($result) {
                    set_flash_message('success', "Service added successfully", 'admin_services');
                    } else {
                    set_flash_message('error', "Failed to add service", 'admin_services');
                    }
                } else {
                    error_log("Validation errors: " . implode(", ", $errors));
                    set_flash_message('error', implode("<br>", $errors), 'admin_services');
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
                $duration = $_POST['duration'] ?? 30;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
    
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
                    // Create service data array
                    $serviceData = [
                        'name' => $name,
                        'description' => $description,
                        'price' => $price,
                        'duration' => $duration,
                        'is_active' => $is_active
                    ];
                        
                    // Use the service model to update the service
                    $result = $this->serviceModel->updateService($id, $serviceData);
                        
                    if ($result) {
                    set_flash_message('success', "Service updated successfully", 'admin_services');
                    } else {
                    set_flash_message('error', "Failed to update service", 'admin_services');
                    }
                } else {
                    set_flash_message('error', implode("<br>", $errors), 'admin_services');
                }
                    
                // Redirect back to services page
                header('Location: ' . base_url('index.php/admin/services'));
                exit;
            }
    
            // Get service details for editing
            $service = $this->serviceModel->getServiceById($id);
            
            if ($service) {
                // Display edit form
                include VIEW_PATH . '/admin/edit_service.php';
                return;
            } else {
            set_flash_message('error', "Service not found", 'admin_services');
                header('Location: ' . base_url('index.php/admin/services'));
                exit;
            }
        } elseif ($action === 'delete' && $id) {
            // Only process delete on POST requests
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Verify CSRF token
                // if (!verify_csrf_token()) {
                //     error_log("CSRF token verification failed for delete");
                //     set_flash_message('error', "Security validation failed. Please try again.", 'admin_services');
                //     header('Location: ' . base_url('index.php/admin/services'));
                //     exit;
                // }
                
                // Use the service model to delete the service
                $result = $this->serviceModel->deleteService($id);
                
                if ($result) {
                set_flash_message('success', "Service deleted successfully", 'admin_services');
                } else {
                set_flash_message('error', "Failed to delete service or service not found", 'admin_services');
                }
                
                // Redirect back to services page
                header('Location: ' . base_url('index.php/admin/services'));
                exit;
            } else {
                // If accessed with GET, just redirect to services page
                set_flash_message('error', "Invalid request method for delete", 'admin_services');
                header('Location: ' . base_url('index.php/admin/services'));
                exit;
            }
        }
        
        // Display the main services page
        
        // Get all services for display
        $services = $this->serviceModel->getAllServices();
        error_log("Found " . count($services) . " services");
        
        include VIEW_PATH . '/admin/services.php';
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
                        // Use the appointment model to schedule the appointment
                        $result = $this->appointmentModel->scheduleAppointment(
                            $patient_id, 
                            $provider_id, 
                            $service_id,
                            $appointment_date, 
                            $start_time, 
                            $end_time,
                            $type, 
                            $notes, 
                            $reason
                        );
                        
                        if ($result) {
                        set_flash_message('success', "Appointment added successfully", 'admin_appointments');
                        } else {
                        set_flash_message('error', "Failed to add appointment", 'admin_appointments');
                        }
                    } else {
                        set_flash_message('error', implode("<br>", $errors), 'admin_appointments');
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
                        // First get the existing appointment
                        $appointment = $this->appointmentModel->getById($id);
                        
                        if (!$appointment) {
                        set_flash_message('error', "Appointment not found", 'admin_appointments');
                            header('Location: ' . base_url('index.php/admin/appointments'));
                            exit;
                        }
                        
                        // Use the appointment model to update all appointment fields
                        $appointmentData = [
                            'patient_id' => $patient_id,
                            'provider_id' => $provider_id,
                            'service_id' => $service_id,
                            'appointment_date' => $appointment_date,
                            'start_time' => $start_time,
                            'end_time' => $end_time,
                            'status' => $status,
                            'type' => $type,
                            'notes' => $notes,
                            'reason' => $reason
                        ];

                        $result = $this->appointmentModel->updateAppointment($id, $appointmentData);

                        if ($result) {
                        set_flash_message('success', "Appointment updated successfully", 'admin_appointments');
                        } else {
                        set_flash_message('error', "Failed to update appointment", 'admin_appointments');
                        }
                    } else {
                        set_flash_message('error', implode("<br>", $errors), 'admin_appointments');
                    }
                    
                    // Redirect back to appointments page
                    header('Location: ' . base_url('index.php/admin/appointments'));
                    exit;
                }
                // Get appointment details for editing
                try {
                    // Get appointment details using the model
                    $appointment = $this->appointmentModel->getById($id);
                    
                    if (!$appointment) {
                        set_flash_message('error', "Appointment not found", 'admin_appointments');
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
            set_flash_message('error', "Error loading appointment form: " . $e->getMessage(), 'admin_appointments');
                    header('Location: ' . base_url('index.php/admin/appointments'));
                    exit;
                }
            } elseif ($action === 'cancel' && $id) {
                // Use appointment model to cancel the appointment
                $result = $this->appointmentModel->cancelAppointment($id, "Canceled by administrator");
                
                if ($result) {
            set_flash_message('success', "Appointment canceled successfully", 'admin_appointments');
                } else {
            set_flash_message('error', "Failed to cancel appointment or appointment not found", 'admin_appointments');
                }
                
                // Redirect back to appointments page
                header('Location: ' . base_url('index.php/admin/appointments'));
                exit;
            }
        }
        
        // Get all appointments for display with patient and provider names
        // Use the appointment model to get all appointments
        $appointments = $this->appointmentModel->getAllAppointments();
        
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
    
    public function providers() {
        // Get provider_id from query if present
        $provider_id = $_GET['provider_id'] ?? null;
        $provider = null;
        
        // Debug: Check all providers in the users table with role 'provider'
        $allProvidersQuery = $this->db->prepare("
            SELECT user_id, email, first_name, last_name, is_active 
            FROM users 
            WHERE role = 'provider'
        ");
        $allProvidersQuery->execute();
        $allProviders = $allProvidersQuery->get_result()->fetch_all(MYSQLI_ASSOC);
        error_log("All providers in users table: " . json_encode($allProviders));
        
        // Always fetch all providers for the list
        $providers = $this->providerModel->getAll();
        error_log("Providers returned by getAll(): " . json_encode($providers));
        
        // Check if Tammy Lee is in the providers array
        $foundTammy = false;
        foreach ($providers as $p) {
            if ($p['email'] === 'provider5@example.com') {
                $foundTammy = true;
                break;
            }
        }
        error_log("Tammy Lee found in providers array: " . ($foundTammy ? "Yes" : "No"));
        
        // If a specific provider is requested, fetch it
        if ($provider_id) {
            $provider = $this->providerModel->getById($provider_id);
        }
        
        // Enhance provider data with service and appointment counts
        foreach ($providers as &$provider) {
            // Get count of services for this provider
            $services = $this->providerModel->getProviderServices($provider['user_id']);
            $provider['service_count'] = count($services);
            
            // Get upcoming appointments for this provider
            $appointments = $this->providerModel->getBookedAppointments($provider['user_id']);
            // Filter for only upcoming appointments
            $upcomingAppointments = array_filter($appointments, function($appt) {
                return strtotime($appt['appointment_date']) >= strtotime(date('Y-m-d'));
            });
            $provider['appointment_count'] = count($upcomingAppointments);
        }
        
        include VIEW_PATH . '/admin/providers.php';
    }
    
    /**
     * Run a test from the tests directory
     */
    public function runTest() {
        // Check if user is admin
        if (!$this->isUserAdmin()) {
            http_response_code(403);
            echo "<div class='alert alert-danger'>Access denied. Only administrators can run tests.</div>";
            exit;
        }
        
        // Get test name from query parameter
        $testName = isset($_GET['test']) ? $_GET['test'] : null;
        
        if (!$testName) {
            http_response_code(400);
            echo "<div class='alert alert-danger'>No test specified.</div>";
            exit;
        }
        
        // Sanitize test name to prevent directory traversal
        $testName = basename($testName);
        $testFile = APP_ROOT . '/tests/' . $testName . '.php';
        
        if (!file_exists($testFile)) {
            http_response_code(404);
            echo "<div class='alert alert-danger'>";
            echo "<h4>Test File Not Found</h4>";
            echo "<p>Test file not found: " . htmlspecialchars($testName) . "</p>";
            echo "</div>";
            exit;
        }
        
        // Set error handler to capture PHP errors
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            // Only handle errors that match the error_reporting setting
            if (!(error_reporting() & $errno)) {
                return false;
            }
            
            $errorType = match($errno) {
                E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => 'Fatal Error',
                E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => 'Warning',
                E_NOTICE, E_USER_NOTICE => 'Notice',
                E_DEPRECATED, E_USER_DEPRECATED => 'Deprecated',
                default => 'Unknown Error'
            };
            
            echo "<div class='alert alert-danger'>";
            echo "<h4>{$errorType}</h4>";
            echo "<p>" . htmlspecialchars($errstr) . "</p>";
            echo "<p>File: " . htmlspecialchars($errfile) . " on line " . $errline . "</p>";
            echo "</div>";
            
            // Don't execute PHP's internal error handler
            return true;
        });
        
        // Start output buffering
        ob_start();
        
        try {
            // Include the test file
            include $testFile;
            
            // If no output was generated but the test didn't throw an exception,
            // we'll consider it a success
            if (ob_get_length() == 0) {
                echo "<div class='alert alert-success'>";
                echo "<h4>Test Completed</h4>";
                echo "<p>The test completed successfully with no output.</p>";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>Exception</h4>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";
        } catch (Error $e) {
            // Also catch PHP 7+ errors
            echo "<div class='alert alert-danger'>";
            echo "<h4>Fatal Error</h4>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";
        }
        
        // Get the output
        $output = ob_get_clean();
        
        // Restore the previous error handler
        restore_error_handler();
        
        // Return the output
        echo $output;
    }
    
    /**
     * Helper method for tests to access private statistics methods
     * @param string $method Method name to call
     * @param array $args Arguments to pass to the method
     * @return mixed Result of the method call
     */
    public function getTestData($method, $args = []) {
        switch ($method) {
            case 'getCount':
                $table = $args[0] ?? '';
                // Use the appropriate model's getTotalCount method
                if ($table === 'users') {
                    return $this->userModel->getTotalCount();
                } elseif ($table === 'appointments') {
                    return $this->appointmentModel->getTotalCount();
                } elseif ($table === 'services') {
                    return $this->serviceModel->getTotalCount();
                }
                return 0;
        
            case 'getCountByRole':
                $role = $args[0] ?? '';
                return $this->userModel->getCountByRole($role);
        
            case 'getCountByStatus':
                $status = $args[0] ?? '';
                return $this->appointmentModel->getCountByStatus($status);
        
            case 'getTopServices':
                $limit = $args[0] ?? 5;
                return $this->serviceModel->getTopServicesByUsage($limit);
        
            case 'getTopProviders':
                $limit = $args[0] ?? 5;
                return $this->providerModel->getTopProviders($limit);
        
            case 'getAvailableSlotsCount':
                return $this->providerModel->getAvailableSlotsCount();
        
            case 'getBookedSlotsCount':
                return $this->appointmentModel->getBookedSlotsCount();
        
            case 'getRecentActivity':
                $limit = $args[0] ?? 10;
                return $this->activityLogModel->getRecentActivity($limit);
        
            default:
                return null;
        }
    }
    
    private function isUserAdmin() {
        return isset($_SESSION['user_id'], $_SESSION['role']) && $_SESSION['role'] === 'admin';
    }    
    private function getPatients() {
        return $this->userModel->getUsersByRole('patient');
    }

    private function getProviders() {
        return $this->userModel->getUsersByRole('provider');
    }

    private function getServices() {
        return $this->serviceModel->getAllServices();
    }
    
   /**
     * Add a new provider
     */
    public function addProvider() {

        // Check if user is admin
        if (!$this->isUserAdmin()) {
            set_flash_message('error', "You don't have permission to access this page", 'auth_login');
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $userData = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'role' => 'provider', // Force role to be provider
                'is_verified' => true  // Set is_verified to true for providers
            ];
            
            // Generate a secure random password if none provided
            if (empty($_POST['password'])) {
                $generatedPassword = bin2hex(random_bytes(4)); // 8 character password
                $userData['password'] = $generatedPassword; // Store to display once
            } else {
                $password = $_POST['password'];
                $userData['password'] = $password;
            }
            
            // Provider-specific data
            $providerData = [
                'specialization' => $_POST['specialization'] ?? '',
                'title' => $_POST['title'] ?? '',
                'bio' => $_POST['bio'] ?? '',
                'accepting_new_patients' => isset($_POST['accepting_new_patients']) ? 1 : 0,
                'max_patients_per_day' => $_POST['max_patients_per_day'] ?? 0
            ];
            
            // Validate data
            $errors = [];
            if (empty($userData['first_name'])) $errors[] = "First name is required";
            if (empty($userData['last_name'])) $errors[] = "Last name is required";
            if (empty($userData['email'])) $errors[] = "Email is required";
            
            // Check for duplicate email before proceeding
            if (!empty($userData['email']) && $this->userModel->emailExists($userData['email'])) {
                $errors[] = "Email already registered. Please use a different email address.";
            }
            
            if (!empty($errors)) {
            set_flash_message('error', implode("<br>", $errors), 'admin_add_provider');
                // Store form data in session for refilling the form
                $_SESSION['form_data'] = $userData + $providerData;
                header('Location: ' . base_url('index.php/admin/addProvider'));
                exit;
            }
            
            try {
                // Begin transaction
                $this->db->begin_transaction();
                
                // Register the user first with is_verified set to true
                $result = $this->userModel->register(
                    $userData['email'],
                    password_hash($userData['password'], PASSWORD_DEFAULT),
                    $userData['first_name'],
                    $userData['last_name'],
                    $userData['phone'],
                    'provider',
                    true,  // Set is_verified to true
                    true   // skipProfileCreation
                );
                
                // Handle the result array properly
                if (isset($result['user_id'])) {
                    $userId = $result['user_id'];
                    error_log("User created with ID: " . $userId);
                } else if (isset($result['error'])) {
                    throw new Exception($result['error']);
                } else {
                    throw new Exception("Unknown error during user registration");
                }
                
                // Add debug logging
                error_log("User created with ID: " . $userId);
                
                // Create provider profile with detailed logging
                error_log("Creating provider profile with data: " . print_r($providerData, true));
                $profileCreated = $this->providerModel->createProviderProfile($userId, $providerData);
                
                if (!$profileCreated) {
                    throw new Exception("Failed to create provider profile");
                }
                // Add debug logging
                error_log("Provider profile created successfully");
                
                // Log the activity
                $this->activityLogModel->logActivity(
                    'provider_created',
                    "Admin created new provider: {$userData['first_name']} {$userData['last_name']}",
                    $_SESSION['user_id']
                );
                // Commit transaction
                $this->db->commit();
                
                // Store the password in the session specifically for display
            set_flash_message('success', "Provider created successfully! Temporary password: <strong>" . $userData['password'] . "</strong>", 'admin_providers');
                $_SESSION['show_password'] = true; // Add a flag to indicate password should be shown
                
                header('Location: ' . base_url('index.php/admin/providers'));
                exit;
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $this->db->rollback();
                error_log("Error creating provider: " . $e->getMessage());
            set_flash_message('error', "Error creating provider: " . $e->getMessage(), 'admin_add_provider');
                // Store form data in session for refilling the form
                $_SESSION['form_data'] = $userData + $providerData;
                header('Location: ' . base_url('index.php/admin/addProvider'));
                exit;
            }
        }
        
        // Display the add provider form
        include VIEW_PATH . '/admin/add_provider.php';
    }

        
    public function toggleAcceptingPatients() {
        if (!$this->isUserAdmin()) {
            set_flash_message('error', "Unauthorized access", 'admin_providers');
            header('Location: ' . base_url('index.php/admin/providers'));
            exit;
        }

        $providerId = $_GET['provider_id'] ?? $_POST['provider_id'] ?? null;
        $accepting = $_GET['accepting'] ?? $_POST['accepting'] ?? null;

        if (!$providerId || $accepting === null) {
            set_flash_message('error', "Provider ID and accepting status are required", 'admin_providers');
            header('Location: ' . base_url('index.php/admin/providers'));
            exit;
        }

        $accepting = (int)$accepting ? 1 : 0;

        $result = $this->providerModel->setAcceptingNewPatients($providerId, $accepting);

        if ($result) {
            $_SESSION['success'] = $accepting
                ? "Provider is now accepting new patients."
                : "Provider is no longer accepting new patients.";
        } else {
            set_flash_message('error', "Failed to update accepting new patients status.", 'admin_providers');
        }

        header('Location: ' . base_url('index.php/admin/providers'));
        exit;
    }

    public function toggleUserStatus() {
        if (!$this->isUserAdmin()) {
            set_flash_message('error', "Unauthorized access", 'admin_users');
            header('Location: ' . base_url('index.php/admin/users'));
            exit;
        }

        $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
        $isActive = $_GET['is_active'] ?? $_POST['is_active'] ?? null;

        if (!$userId || $isActive === null) {
            set_flash_message('error', "User ID and status are required", 'admin_users');
            header('Location: ' . base_url('index.php/admin/users'));
            exit;
        }

        $isActive = (int)$isActive ? 1 : 0;

        $result = $this->userModel->updateUser($userId, ['is_active' => $isActive]);

        if ($result) {
            $_SESSION['success'] = $isActive
                ? "User activated successfully."
                : "User deactivated successfully.";
        } else {
            set_flash_message('error', "Failed to update user status.", 'admin_users');
        }

        header('Location: ' . base_url('index.php/admin/users'));
        exit;
    }


    /**
     * View a provider's availability schedule
     *
     * @return void
     */
    public function viewAvailability() {
        // Check if user is admin
        if (!$this->isUserAdmin()) {
            set_flash_message('error', "You don't have permission to access this page", 'auth_login');
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        // First, check if ID is passed as query parameter (most reliable method)
        $providerId = isset($_GET['id']) ? $_GET['id'] : null;
        
        // If not found in query, try to extract from URL path (fallback)
        if (!$providerId) {
            $path = '';
            if (!empty($_SERVER['PATH_INFO'])) {
                $path = $_SERVER['PATH_INFO'];
            } elseif (!empty($_SERVER['REQUEST_URI'])) {
                $path = $_SERVER['REQUEST_URI'];
                if (($pos = strpos($path, '?')) !== false) {
                    $path = substr($path, 0, $pos);
                }
            }
            
            if (preg_match('/\/(\d+)(\/|$)/', $path, $matches)) {
                $providerId = $matches[1];
            }
        }
        
        if (!$providerId) {
            set_flash_message('error', "Provider ID is required", 'admin_providers');
            header('Location: ' . base_url('index.php/admin/providers'));
            exit;
        }
        
        // Get provider details
        $provider = $this->userModel->getUserById($providerId);
        $providerProfile = $this->providerModel->getProviderById($providerId);
        
        if (!$provider || $provider['role'] !== 'provider') {
            set_flash_message('error', "Provider not found or user is not a provider", 'admin_providers');
            header('Location: ' . base_url('index.php/admin/providers'));
            exit;
        }
        
        // Merge the provider arrays to have all provider data in one array
        if ($providerProfile) {
            $provider = array_merge($provider, $providerProfile);
        }
        
        // Debug the provider data
        error_log("Provider data: " . json_encode($provider));
        
        // Get recurring schedules using your existing method
        $recurringSchedules = $this->providerModel->getRecurringSchedules($providerId);
        error_log("Provider recurring schedules: " . json_encode($recurringSchedules));
        
        // Keep the original availability variable for backward compatibility
        $availability = $this->providerModel->getProviderAvailability($providerId);
        
        // Get provider's upcoming appointments
        $appointments = $this->appointmentModel->getProviderAppointments($providerId, 'upcoming');
        
        // Use the correct filename with the typo "avaliability" (notice the extra 'i')
        include VIEW_PATH . '/admin/provider_avaliability.php';
    }

    /**
     * Manage services offered by a provider
     *
     * @return void
     */
    public function manageProviderServices() {
        // Check if user is admin
        if (!$this->isUserAdmin()) {
            set_flash_message('error', "You don't have permission to access this page", 'auth_login');
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        // First, check if ID is passed as query parameter (most reliable method)
        $providerId = isset($_GET['id']) ? $_GET['id'] : null;
        
        // If not found in query, try to extract from URL path
        if (!$providerId) {
            $path = '';
            // Try PATH_INFO first
            if (!empty($_SERVER['PATH_INFO'])) {
                $path = $_SERVER['PATH_INFO'];
            }
            // If PATH_INFO not available, try REQUEST_URI
            elseif (!empty($_SERVER['REQUEST_URI'])) {
                $path = $_SERVER['REQUEST_URI'];
                
                // Remove query string if present
                if (($pos = strpos($path, '?')) !== false) {
                    $path = substr($path, 0, $pos);
                }
            }
            
            // Extract numeric ID from path
            if (preg_match('/\/(\d+)(\/|$)/', $path, $matches)) {
                $providerId = $matches[1];
            }
        }
        
        // If provider ID still not found, redirect with error
        if (!$providerId) {
            set_flash_message('error', "Provider ID is required", 'admin_providers');
            header('Location: ' . base_url('index.php/admin/providers'));
            exit;
        }
        
        // Get provider details
        $provider = $this->userModel->getUserById($providerId);
        
        if (!$provider || $provider['role'] !== 'provider') {
            set_flash_message('error', "Provider not found or user is not a provider", 'admin_providers');
            header('Location: ' . base_url('index.php/admin/providers'));
            exit;
        }
        
        // Handle form submission for adding/removing services
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'add_service') {
                $serviceId = $_POST['service_id'] ?? null;
                // These are the only fields we have in our database
                $customDuration = !empty($_POST['custom_duration']) ? (int)$_POST['custom_duration'] : null;
                $customNotes = !empty($_POST['custom_notes']) ? trim($_POST['custom_notes']) : null;
                
                if (!$serviceId) {
                    // Redirect with error - using query parameter format
                    header('Location: ' . base_url('index.php/admin/manageProviderServices?id=' . $providerId . '&error=add_failed'));
                    exit;
                } else {
                    // Add service to provider with only the fields our database supports
                    $result = $this->providerModel->addServiceToProvider(
                        $providerId,
                        $serviceId,
                        $customDuration,
                        $customNotes
                    );
                    
                    if ($result) {
                        // Redirect with success - using query parameter format
                        header('Location: ' . base_url('index.php/admin/manageProviderServices?id=' . $providerId . '&success=added'));
                    } else {
                        // Redirect with error - using query parameter format
                        header('Location: ' . base_url('index.php/admin/manageProviderServices?id=' . $providerId . '&error=add_failed'));
                    }
                    exit;
                }
            } elseif ($action === 'remove_service') {
                $serviceId = $_POST['service_id'] ?? null;
                
                if (!$serviceId) {
                    // Redirect with error - using query parameter format
                    header('Location: ' . base_url('index.php/admin/manageProviderServices?id=' . $providerId . '&error=remove_failed'));
                    exit;
                } else {
                    // Remove service from provider
                    $result = $this->providerModel->removeServiceFromProvider($providerId, $serviceId);
                    
                    if ($result) {
                        // Redirect with success - using query parameter format
                        header('Location: ' . base_url('index.php/admin/manageProviderServices?id=' . $providerId . '&success=removed'));
                    } else {
                        // Redirect with error - using query parameter format
                        header('Location: ' . base_url('index.php/admin/manageProviderServices?id=' . $providerId . '&error=remove_failed'));
                    }
                    exit;
                }
            }
            // Removed update_price action as it's not supported by our current database structure
        }
        
        // Get services offered by this provider
        $services = $this->providerModel->getProviderServices($providerId);
        
        // Get all available services for adding
        $allServices = $this->serviceModel->getAllServices();
        
        // Create a list of service IDs already offered by the provider
        $providerServiceIds = array_column($services, 'service_id');
        
        // Filter out services already offered by the provider
        $availableServices = array_filter($allServices, function($service) use ($providerServiceIds) {
            return !in_array($service['service_id'], $providerServiceIds);
        });
        
        include VIEW_PATH . '/admin/provider_services.php';
    }
}