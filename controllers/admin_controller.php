<?php
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
        $this->serviceModel = new Service($this->db);
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
                        // Use model methods for deletion
                        $this->appointmentModel->deleteAppointmentsByUser($userId);
                        
                        if ($user['role'] === 'provider') {
                            $this->providerModel->deleteProviderAvailability($userId);
                            $this->providerModel->deleteProviderProfile($userId);
                        } elseif ($user['role'] === 'patient') {
                            $this->userModel->deletePatientProfile($userId);
                        }
                        
                        $this->userModel->deleteUser($userId);
                        
                        // Log the activity
                        $this->activityLogModel->logUserDeletion($userId, $_SESSION['user_id']);
                        
                        // Commit is handled by the models
                        $_SESSION['success'] = "User has been permanently deleted";
                    } catch (Exception $e) {
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
                        $roleData = $this->providerModel->getProviderProfile($userId);
                    } elseif ($user['role'] === 'patient') {
                        $roleData = $this->userModel->getPatientProfile($userId);
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
                    $duration = $_POST['duration'] ?? 30; // Default duration
    
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
                            'is_active' => 1
                        ];
                        
                        // Use the service model to create the service
                        $result = $this->serviceModel->createService($serviceData);
                        
                        if ($result) {
                            $_SESSION['success'] = "Service added successfully";
                        } else {
                            $_SESSION['error'] = "Failed to add service";
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
                            $_SESSION['success'] = "Service updated successfully";
                        } else {
                            $_SESSION['error'] = "Failed to update service";
                        }
                    } else {
                        $_SESSION['error'] = implode("<br>", $errors);
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
                    $_SESSION['error'] = "Service not found";
                    header('Location: ' . base_url('index.php/admin/services'));
                    exit;
                }
            } elseif ($action === 'delete' && $id) {
                // Use the service model to delete the service
                $result = $this->serviceModel->deleteService($id);
                
                if ($result) {
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
        $services = $this->serviceModel->getAllServices();
        
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
                            $_SESSION['success'] = "Appointment added successfully";
                        } else {
                            $_SESSION['error'] = "Failed to add appointment";
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
                        // First get the existing appointment
                        $appointment = $this->appointmentModel->getAppointmentById($id);
                        
                        if (!$appointment) {
                            $_SESSION['error'] = "Appointment not found";
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
                            $_SESSION['success'] = "Appointment updated successfully";
                        } else {
                            $_SESSION['error'] = "Failed to update appointment";
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
                    // Get appointment details using the model
                    $appointment = $this->appointmentModel->getAppointmentById($id);
                    
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
                // Use appointment model to cancel the appointment
                $result = $this->appointmentModel->cancelAppointment($id, "Canceled by administrator");
                
                if ($result) {
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
        // Use the appointment model to get all appointments
        $appointments = $this->appointmentModel->getAllAppointments();
        
        // If the model doesn't have this method yet, fall back to the original code
        if (empty($appointments) && method_exists($this, 'db')) {
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
    
    public function providers() {
        // Get all providers with their profile details using the provider model
        $providers = $this->providerModel->getAllProvidersWithDetails();
        
        // If providers are empty, use a more specific provider method
        if (empty($providers)) {
            // This should be implemented in the Provider model
            $providers = $this->providerModel->getProvidersWithServiceAndAppointmentCounts();
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
            echo "Access denied. Only administrators can run tests.";
            exit;
        }
        
        // Get test name from query parameter
        $testName = isset($_GET['test']) ? $_GET['test'] : null;
        
        if (!$testName) {
            http_response_code(400);
            echo "No test specified.";
            exit;
        }
        
        // Sanitize test name to prevent directory traversal
        $testName = basename($testName);
        $testFile = APP_ROOT . '/tests/' . $testName . '.php';
        
        if (!file_exists($testFile)) {
            http_response_code(404);
            echo "Test file not found: " . htmlspecialchars($testName);
            exit;
        }
        
        // Capture output from the test
        ob_start();
        try {
            include $testFile;
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>Error Running Test</h4>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        $output = ob_get_clean();
        
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
        if (method_exists($this, $method) && in_array($method, [
            'getCount', 'getCountByRole', 'getCountByStatus', 
            'getTopServices', 'getTopProviders', 'getAvailableSlotsCount',
            'getBookedSlotsCount', 'getRecentActivity'
        ])) {
            return call_user_func_array([$this, $method], $args);
        }
        return null;
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
}
