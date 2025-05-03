<?php
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/Appointment.php';
require_once MODEL_PATH . '/Services.php';
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/Notification.php';


class ProviderController {
    protected $db;
    protected $providerModel;
    protected $appointmentModel;
    protected $serviceModel;
    protected $userModel; 
    protected $notificationModel;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'provider' && $_SESSION['role'] !== 'admin')) {
            error_log("Unauthorized access attempt.");
            header('Location: ' . base_url('index.php/auth?error=Unauthorized access'));
            exit;
        }

        $this->db = get_db();
        
        // Initialize models
        $this->providerModel = new Provider($this->db);
        $this->appointmentModel = new Appointment($this->db);
        $this->serviceModel = new Service($this->db);
        $this->userModel = new User($this->db); 
        $this->notificationModel = new Notification($this->db); 
    }

    // Load Provider Dashboard
    public function index() {
        $provider_id = $_SESSION['user_id'];
        error_log("Loading provider dashboard for ID: " . $provider_id);

        $providerData = $this->providerModel->getProviderData($provider_id);
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        $providerAvailability = $this->providerModel->getAvailability($provider_id);

        include VIEW_PATH . '/provider/index.php';
    }
    public function getProviderSchedules() {
        $provider_id = $_SESSION['user_id'] ?? null;
        if (!$provider_id) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
    
        // Get both regular and recurring schedules
        $schedules = $this->providerModel->getAvailability($provider_id);
        $recurringSchedules = $this->providerModel->getRecurringSchedules($provider_id);
        
        // Debug logging
        error_log("Regular schedules count: " . count($schedules));
        error_log("Recurring schedules count: " . count($recurringSchedules));
        
        // Format events for FullCalendar
        $events = [];
        
        // Format regular availability slots
        foreach ($schedules as $schedule) {
            $events[] = [
                'id' => $schedule['availability_id'], // Make sure this ID exists
                'title' => 'Available',
                'start' => $schedule['available_date'] . 'T' . $schedule['start_time'],
                'end' => $schedule['available_date'] . 'T' . $schedule['end_time'],
                'color' => '#28a745', // Green for available
                'extendedProps' => [
                    'type' => 'regular'
                ]
            ];
        }
        
        // Format recurring availability
        $dayOfWeekMap = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        
        foreach ($recurringSchedules as $recurring) {
            $dayIndex = $recurring['day_of_week'];
            if ($dayIndex < 0 || $dayIndex > 6) continue;
            
            $dayName = $dayOfWeekMap[$dayIndex];
            
            // Generate events for the next 4 weeks
            for ($i = 0; $i < 4; $i++) {
                $date = date('Y-m-d', strtotime("+$i week $dayName"));
                
                $events[] = [
                    'id' => 'recurring_' . $recurring['schedule_id'] . '_' . $i,
                    'title' => 'Recurring',
                    'start' => $date . 'T' . $recurring['start_time'],
                    'end' => $date . 'T' . $recurring['end_time'],
                    'color' => '#17a2b8', // Blue for recurring
                    'extendedProps' => [
                        'type' => 'recurring',
                        'original_id' => $recurring['schedule_id']
                    ]
                ];
            }
        }
        
        // Debug the final events array
        error_log("Total events generated: " . count($events));
        
        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }
    public function createRecurringSchedule($provider_id, $start_date, $end_date, $days_of_week, $start_time, $end_time) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO provider_recurring_schedules (provider_id, start_date, end_date, day_of_week, start_time, end_time) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            foreach ($days_of_week as $day) {
                $stmt->bind_param("ississ", $provider_id, $start_date, $end_date, $day, $start_time, $end_time);
                $stmt->execute();
            }
            return true;
        } catch (Exception $e) {
            error_log("Error inserting recurring schedule: " . $e->getMessage());
            return false;
        }
    }
    public function processUpdateAvailability() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'] ?? null;
            $availability_date = $_POST['availability_date'] ?? null;
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            $is_available = $_POST['is_available'] ?? 1;
    
            error_log("Saving Provider Availability: Date: $availability_date, Time: $start_time-$end_time");
    
            $success = $this->providerModel->storeAvailability($provider_id, $availability_date, $start_time, $end_time, $is_available);
    
            $_SESSION[$success ? 'success' : 'error'] = $success ? "Availability Updated!" : "Failed to Update Availability.";
    
            header("Location: " . base_url("index.php/provider/schedule"));
            exit;
        }
    }
    public function processRecurringSchedule() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'] ?? null;
            $start_date = $_POST['start_date'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            $days_of_week = $_POST['days_of_week'] ?? [];
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
    
            if (!$provider_id || !$start_date || !$end_date || empty($days_of_week) || !$start_time || !$end_time) {
                $_SESSION['error'] = "All required fields must be filled.";
                header("Location: " . base_url("index.php/provider/schedule"));
                exit;
            }
    
            // Ensure `$this->scheduleModel` exists in your controller
            if (!isset($this->scheduleModel)) {
                error_log("Error: `scheduleModel` is not initialized in ProviderController.");
                $_SESSION['error'] = "Internal error: schedule processing failed.";
                header("Location: " . base_url("index.php/provider/schedule"));
                exit;
            }
    
            $success = $this->scheduleModel->createRecurringSchedule(
                $provider_id, $start_date, $end_date, $days_of_week, $start_time, $end_time
            );
    
            $_SESSION[$success ? 'success' : 'error'] = $success 
                ? "Recurring schedule created successfully!" 
                : "Failed to create recurring schedule.";
    
            header("Location: " . base_url("index.php/provider/schedule"));
            exit;
        }
    }
    

    // Update Provider Profile
    public function updateProfile() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $first_name = htmlspecialchars(trim($_POST['first_name']));
            $last_name = htmlspecialchars(trim($_POST['last_name']));
            $specialty = htmlspecialchars(trim($_POST['specialty']));
            $phone = htmlspecialchars(trim($_POST['phone']));
            $bio = htmlspecialchars(trim($_POST['bio']));
    
            $success = $this->providerModel->updateProfile($provider_id, $first_name, $last_name, $specialty, $phone, $bio);
    
            if ($success) {
                header("Location: " . base_url("index.php/provider/profile?success=Profile updated"));
            } else {
                header("Location: " . base_url("index.php/provider/profile?error=Update failed"));
            }
            exit;
        }
    }

    // Password Change
    public function processPasswordChange() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $current_password = trim($_POST['current_password']);
            $new_password = trim($_POST['new_password']);
            $confirm_password = trim($_POST['confirm_password']);

            if ($new_password !== $confirm_password) {
                header('Location: ' . base_url('index.php/provider/profile?error=Passwords do not match'));
                exit;
            }

            $success = $this->providerModel->changePassword($provider_id, $current_password, $new_password);
            
            if ($success) {
                header('Location: ' . base_url('index.php/provider/profile?success=Password changed'));
            } else {
                header('Location: ' . base_url('index.php/provider/profile?error=Password update failed'));
            }
            exit;
        }
    }
    // Provider Availability Management
    public function manage_availability() {
        $provider_id = $_SESSION['user_id'];

        // Get the provider's current availability/schedules
        $schedules = $this->providerModel->getAvailability($provider_id);
        $recurringSchedules = $this->providerModel->getRecurringSchedules($provider_id);

        // Load the view
        include VIEW_PATH . '/provider/schedule.php';
    }
    public function getAvailableSlots($provider_id, $service_duration) {
        $available_slots = $this->providerModel->getAvailability($provider_id);
        
        // 🔍 Debugging Log
        error_log("Raw Availability Data: " . print_r($available_slots, true));
    
        $appointments = [];
    
        foreach ($available_slots as $slot) {
            $start = strtotime($slot['start_time']);
            $end = strtotime($slot['end_time']);
    
            while ($start + ($service_duration * 60) <= $end) {
                $appointments[] = [
                    'date' => $slot['availability_date'],
                    'start_time' => date("H:i", $start),
                    'end_time' => date("H:i", $start + ($service_duration * 60))
                ];
                $start += ($service_duration * 60);
            }
        }
    
        error_log("Processed Appointment Slots: " . print_r($appointments, true));
        
        header('Content-Type: application/json');
        echo json_encode($appointments, JSON_PRETTY_PRINT);
        exit;
    }

    // Provider Services (CRUD)
    public function services() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You must be logged in to access this page";
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }

        // Determine if this is an admin managing a provider's services
        $isAdminManaging = isset($_SESSION['admin_managing_provider_id']);
        $providerId = $isAdminManaging ? $_SESSION['admin_managing_provider_id'] : $_SESSION['user_id'];

        // If admin is managing, verify they are actually an admin
        if ($isAdminManaging && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
            unset($_SESSION['admin_managing_provider_id']);
            $_SESSION['error'] = "You don't have permission to manage provider services";
            header('Location: ' . base_url('index.php/admin'));
            exit;
        }

        // Get provider details
        $provider = $this->userModel->getUserById($providerId);

        if (!$provider || $provider['role'] !== 'provider') {
            $_SESSION['error'] = "Provider not found";
            if ($isAdminManaging) {
                unset($_SESSION['admin_managing_provider_id']);
                header('Location: ' . base_url('index.php/admin/providers'));
            } else {
                header('Location: ' . base_url('index.php/auth'));
            }
            exit;
        }

        // Get services for this provider
        $services = $this->serviceModel->getServicesByProvider($providerId);

        // After processing, clear the admin management session variable if it exists
        if ($isAdminManaging) {
            unset($_SESSION['admin_managing_provider_id']);
        }

        // Load the view with a flag indicating if this is admin management
        $isAdmin = $isAdminManaging;
        include VIEW_PATH . '/provider/services.php';
    }

    public function addService() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $service_id = intval($_POST['service_id']);
            $service_name = htmlspecialchars(trim($_POST['service_name']));
            $description = htmlspecialchars(trim($_POST['description']));
            $price = floatval($_POST['price']);
    
            $success = $this->providerModel->updateService($service_id, $provider_id, $service_name, $description, $price);
    
            if ($success) {
                header('Location: ' . base_url('index.php/provider/services?success=Service added'));
            } else {
                header('Location: ' . base_url('index.php/provider/services?error=Failed to add service'));
            }
    
            header("Content-Type: application/json");
            echo json_encode(["success" => $success]);
            exit;
        }
    }
    /**
     * Display provider notifications
     */
    public function notifications() {
        // Check if user is logged in and is a provider
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            $_SESSION['error'] = "You must be logged in as a provider to view notifications";
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        
        // Get notifications for this provider
        $notifications = $this->notificationModel->getUserNotifications($provider_id);
        
        // Get unread count
        $unreadCount = $this->notificationModel->getUnreadCount($provider_id);
        
        // Include the notifications view
        include VIEW_PATH . '/provider/notifications.php';
    }

    public function manage_services() {
        require_once MODEL_PATH . '/Services.php';
        $servicesModel = new Services($this->db);
    
        $services = $servicesModel->getAllServices(); // Fetch services from the database
        
        require VIEW_PATH . '/provider/services.php'; // Pass `$services` to the view
    }
    public function deleteService() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $service_id = intval($_POST['service_id']);

            $success = $this->providerModel->deleteService($service_id, $provider_id);

            if ($success) {
                header('Location: ' . base_url('index.php/provider/services?success=Service deleted'));
            } else {
                header('Location: ' . base_url('index.php/provider/services?error=Failed to delete service'));
            }
            exit;
        }
    }

    // Appointments Management
    public function appointments() {
        $provider_id = $_SESSION['user_id'];
        
        // Get appointments from model
        $appointments = $this->providerModel->getBookedAppointments($provider_id);

        // Now include the view with the processed data
        include VIEW_PATH . '/provider/appointments.php';
    }
    
    // Scheduling & Availability Management
    public function schedule() {
        $provider_id = $_SESSION['user_id'];
        
        // Get the provider's current availability/schedules
        $availability = $this->providerModel->getAvailability($provider_id);
        $recurringSchedules = $this->providerModel->getRecurringSchedules($provider_id);
        
        // Load the view
        include VIEW_PATH . '/provider/schedule.php';
    }

    // Scheduling & Availability Management
    public function addRecurringSchedule() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $day_of_week = intval($_POST['day_of_week']);
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($start_time) && !empty($end_time) && $end_time > $start_time) {
                $success = $this->providerModel->addRecurringSchedule($provider_id, $day_of_week, $start_time, $end_time, $is_active);
                if ($success) {
                    header('Location: ' . base_url('index.php/provider/schedule?success=Schedule added'));
                } else {
                    header('Location: ' . base_url('index.php/provider/schedule?error=Failed to add schedule'));
                }
                exit;
            }
        }
        header('Location: ' . base_url('index.php/provider/schedule?error=Invalid input'));
        exit;
    }

    public function updateSchedule() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
    
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['id']) || !isset($data['date']) || !isset($data['start_time']) || !isset($data['end_time'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing required data']);
            exit;
        }
        
        $eventId = $data['id'];
        $eventType = $data['type'] ?? 'regular';
        $date = $data['date'];
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];
        
        $success = false;
        if (strpos($eventId, 'recurring_') === 0) {
            // Handle recurring schedule updates
            $parts = explode('_', $eventId);
            $scheduleId = $parts[1] ?? null;
            
            if ($scheduleId) {
                // For recurring events, we might need to update the base recurring schedule
                $success = $this->providerModel->updateRecurringSchedule($scheduleId, $startTime, $endTime);
            }
        } else {
            // Handle regular availability updates
            $success = $this->providerModel->updateAvailabilitySlot($eventId, $date, $startTime, $endTime);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }
    // Add this method to your ProviderController class
    public function profile() {
        $provider_id = $_SESSION['user_id'];
        
        // Get the provider's profile data
        $providerData = $this->providerModel->getProviderData($provider_id);
        
        // Load the profile view
        include VIEW_PATH . '/provider/profile.php';
    }

    // Add this method to your ProviderController class
    public function reports() {
        $provider_id = $_SESSION['user_id'];
        
        // Get appointment statistics for reports
        $appointmentStats = $this->getAppointmentStatistics($provider_id);
        
        // You may want to get additional report data here
        // For example: revenue data, patient demographics, etc.
        
        // Load the reports view
        include VIEW_PATH . '/provider/reports.php';
    }

    // Helper method to calculate appointment statistics
    private function getAppointmentStatistics($provider_id) {
        // You'll need to implement appropriate methods in your Appointment model
        // or use direct queries if these don't exist
        
        $stats = [
            'total' => 0,
            'completed' => 0,
            'canceled' => 0,
            'no_show' => 0,
            'upcoming' => 0,
            // You can add more statistics as needed
        ];
        
        // Example query - adjust based on your actual database structure and model methods
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        
        if ($appointments) {
            $stats['total'] = count($appointments);
            
            foreach ($appointments as $appointment) {
                switch ($appointment['status']) {
                    case 'completed':
                        $stats['completed']++;
                        break;
                    case 'canceled':
                        $stats['canceled']++;
                        break;
                    case 'no_show':
                        $stats['no_show']++;
                        break;
                    case 'scheduled':
                    case 'confirmed':
                        // Check if appointment is in the future
                        if (strtotime($appointment['appointment_date']) > time()) {
                            $stats['upcoming']++;
                        }
                        break;
                }
            }
        }
        
        return $stats;
    }
    
}
?>