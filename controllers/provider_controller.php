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
    protected $scheduleModel;
    
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
        $this->serviceModel = new Services($this->db);
        $this->userModel = new User($this->db); 
        $this->notificationModel = new Notification($this->db); 
    }

    // Load Provider Dashboard
    public function index() {
        // Make sure we have a valid session with a provider ID
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
            return;
        }
        
        $provider_id = $_SESSION['user_id'];
        error_log("Loading provider dashboard for ID: " . $provider_id);
        
        // Get provider data
        $providerData = $this->providerModel->getProviderData($provider_id);
        
        // Get upcoming appointments specifically for the dashboard
        $appointments = $this->appointmentModel->getUpcomingAppointmentsByProvider($provider_id);
        
        // Also get provider availability
        $providerAvailability = $this->providerModel->getAvailability($provider_id);
        
        // Add statistics for the dashboard
        $appointmentStats = [
            'upcoming' => count($appointments),
            'total' => $this->appointmentModel->getByProvider($provider_id) ? count($this->appointmentModel->getByProvider($provider_id)) : 0,
            'completed' => 0,
            'canceled' => 0
        ];
        
        // Load the view
        include VIEW_PATH . '/provider/index.php';
    }
    
    /**
     * Handle editing a provider service (custom duration and notes)
     */
    public function editProviderService() {
        // Check if user is logged in and is a provider
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            $_SESSION['error'] = "Unauthorized access";
            redirect('auth');
            return;
        }
        
        // Verify CSRF token if implemented
        if (function_exists('verify_csrf_token') && !verify_csrf_token()) {
            $_SESSION['error'] = "Invalid form submission";
            redirect('provider/services');
            return;
        }
        
        // Get the provider ID from the session
        $provider_id = $_SESSION['user_id'];
        
        // Check if form was submitted and contains required fields
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['provider_service_id'])) {
            // Get the provider service ID
            $provider_service_id = intval($_POST['provider_service_id']);
            
            // Get custom duration and notes
            $custom_duration = isset($_POST['custom_duration']) && !empty($_POST['custom_duration']) 
                            ? intval($_POST['custom_duration']) 
                            : null;
            $custom_notes = isset($_POST['custom_notes']) ? trim($_POST['custom_notes']) : '';
            
            // Initialize the provider services model if not already available
            if (!isset($this->providerServicesModel)) {
                $this->providerServicesModel = new ProviderServices($this->db);
            }
            
            // Update the provider service
            $success = $this->providerServicesModel->updateService(
                $provider_service_id, 
                $provider_id, 
                $custom_duration, 
                $custom_notes
            );
            
            if ($success) {
                $_SESSION['success'] = "Service updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update service";
            }
        } else {
            $_SESSION['error'] = "Invalid request";
        }
        
        // Redirect back to the services page
        redirect('provider/services');
    }
    /**
     * Handle deleting a provider service (removing a service from provider's offerings)
     */
    public function deleteProviderService() {
        // Check if user is logged in and is a provider
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            $_SESSION['error'] = "Unauthorized access";
            redirect('auth');
            return;
        }
        
        // Verify CSRF token if implemented
        if (function_exists('verify_csrf_token') && !verify_csrf_token()) {
            $_SESSION['error'] = "Invalid form submission";
            redirect('provider/services');
            return;
        }
        
        // Get the provider ID from the session
        $provider_id = $_SESSION['user_id'];
        
        // Check if form was submitted and contains required fields
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['provider_service_id'])) {
            // Get the provider service ID
            $provider_service_id = intval($_POST['provider_service_id']);
            
            // Initialize the provider services model if not already available
            if (!isset($this->providerServicesModel)) {
                $this->providerServicesModel = new ProviderServices($this->db);
            }
            
            // Delete the provider service association
            $success = $this->providerServicesModel->deleteProviderService($provider_service_id, $provider_id);
            
            if ($success) {
                $_SESSION['success'] = "Service removed from your offerings";
            } else {
                $_SESSION['error'] = "Failed to remove service";
            }
        } else {
            $_SESSION['error'] = "Invalid request";
        }
        
        // Redirect back to the services page
        redirect('provider/services');
    }

    public function processService() {
        error_log("processService called with POST: " . print_r($_POST, true));
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provider_id = $_SESSION['user_id'];
            $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;
            $service_name = htmlspecialchars(trim($_POST['service_name']));
            $description = htmlspecialchars(trim($_POST['description']));
            $price = floatval($_POST['price']);
    
            // You can add validation here as needed
    
            if ($service_id) {
                // Update existing service
                $success = $this->providerModel->updateService($service_id, $provider_id, $service_name, $description, $price);
                $message = $success ? "Service updated successfully" : "Failed to update service";
            } else {
                // Add new service
                $success = $this->providerModel->addService($provider_id, $service_name, $description, $price);
                $message = $success ? "Service added successfully" : "Failed to add service";
            }
    
            error_log("Service update result: " . var_export($success, true));
    
            if ($success) {
                $_SESSION['success'] = $message;
            } else {
                $_SESSION['error'] = $message;
            }
            header('Location: ' . base_url('index.php/provider/services'));
            exit;
        }
        // If not POST, redirect to services page
        header('Location: ' . base_url('index.php/provider/services'));
        exit;
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
    
        // Add try-catch for recurring schedules
        try {
            $recurringSchedules = $this->providerModel->getRecurringSchedules($provider_id);
        } catch (Exception $e) {
            error_log("Error fetching recurring schedules: " . $e->getMessage());
            // Create default recurring schedules as fallback
            $recurringSchedules = [];
        
            // Default recurring schedules for Monday, Wednesday, Friday
            $daysOfWeek = [1, 3, 5]; 
            foreach ($daysOfWeek as $index => $day) {
                $recurringSchedules[] = [
                    'schedule_id' => 'default_' . ($index + 1),
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'status' => 'active'
                ];
            }
        }
        
        // Debug logging
        error_log("Regular schedules count: " . count($schedules));
        error_log("Recurring schedules count: " . count($recurringSchedules));
        
        // Format events for FullCalendar
        $events = [];
        
        // Format regular availability slots
        foreach ($schedules as $schedule) {
            $event = [
                // Try different possible ID field names, or generate a unique ID if none found
                'id' => $schedule['availability_id'] ?? $schedule['id'] ?? $schedule['schedule_id'] ?? ('reg_' . uniqid()),
                'title' => 'Available',
                // Use null coalescing operator to try alternative date/time field names
                'start' => ($schedule['availability_date'] ?? $schedule['date'] ?? $schedule['schedule_date'] ?? date('Y-m-d')) . ' ' . 
                           ($schedule['start_time'] ?? '09:00:00'),
                'end' => ($schedule['availability_date'] ?? $schedule['date'] ?? $schedule['schedule_date'] ?? date('Y-m-d')) . ' ' . 
                        ($schedule['end_time'] ?? '17:00:00'),
                'color' => '#28a745'
            ];
        
            // Add the event to the events array (this was missing!)
            $events[] = $event;
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
            $max_appointments = $_POST['max_appointments'] ?? 0;
            $is_available = $_POST['is_available'] ?? 1;
    
            // Log the received data for debugging
            error_log("Received Availability Data: " . json_encode(compact(
                'provider_id', 'availability_date', 'start_time', 'end_time', 'max_appointments', 'is_available'
            ), JSON_PRETTY_PRINT));
    
            // Ensure no missing values
            if (!$provider_id || !$availability_date || !$start_time || !$end_time) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
    
            $success = $this->providerModel->addAvailability([
                'provider_id' => $provider_id,
                'availability_date' => $availability_date,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'max_appointments' => $max_appointments,
                'is_available' => $is_available // <-- THIS FIXES THE BUG
            ]);
    
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Availability updated successfully!' : 'Failed to update availability.'
            ], JSON_PRETTY_PRINT);
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
    public function getAvailableSlots($provider_id = null, $date = null, $service_duration = null) {
        // Get parameters if not provided directly
        if ($provider_id === null) {
            $provider_id = $_GET['provider_id'] ?? null;
        }
        if ($date === null) {
            $date = $_GET['date'] ?? date('Y-m-d'); // Default to today
        }
        if ($service_duration === null) {
            $service_duration = $_GET['duration'] ?? 30; // Default to 30 min
        }
        
        $exclude_appointment_id = $_GET['appointment_id'] ?? null;
        
        // Validation
        if (!$provider_id) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Provider ID is required']);
            exit;
        }
        
        // 🔍 Debugging Log
        error_log("Getting available slots for provider: $provider_id, date: $date, duration: $service_duration");
        
        // Call the provider model method with the correct parameters
        $available_slots = $this->providerModel->getAvailableSlots(
            $provider_id,
            $date,
            $service_duration,
            $exclude_appointment_id
        );
        
        error_log("Raw Availability Data: " . print_r($available_slots, true));
        
        // Format slots for the response if needed
        $appointments = [];
        
        // Convert the provider model's output format to what the frontend expects
        foreach ($available_slots as $slot) {
            // The model already returns slots in the format:
            // { id, start, end, title, color }
            // This is the format needed by FullCalendar
            $appointments[] = $slot;
        }
        
        error_log("Processed Appointment Slots: " . print_r($appointments, true));
        
        header('Content-Type: application/json');
        echo json_encode($appointments, JSON_PRETTY_PRINT);
        exit;
    }
     /**
     * Export provider's appointments to CSV
     */
    public function exportAppointmentsToCSV() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }

        $provider_id = $_SESSION['user_id'];
        $appointments = $this->appointmentModel->getByProvider($provider_id);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="appointments.csv"');

        $output = fopen('php://output', 'w');
        // Write CSV header
        fputcsv($output, ['Appointment ID', 'Patient Name', 'Service', 'Date', 'Start Time', 'End Time', 'Status']);

        foreach ($appointments as $appointment) {
            fputcsv($output, [
                $appointment['id'] ?? $appointment['appointment_id'] ?? '',
                $appointment['patient_name'] ?? $appointment['first_name'] ?? '',
                $appointment['service_name'] ?? $appointment['service'] ?? '',
                $appointment['appointment_date'] ?? '',
                $appointment['start_time'] ?? '',
                $appointment['end_time'] ?? '',
                $appointment['status'] ?? ''
            ]);
        }
        fclose($output);
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
        
          // Initialize events array to avoid undefined variable warning
        $events = [];
        
        // Get the provider's current availability/schedules
        $availability = $this->providerModel->getAvailability($provider_id);
        $recurringSchedules = $this->providerModel->getRecurringSchedules($provider_id);
        
        // 1. Get the individual available slots using the same method patients see
        $providerModel = new Provider(get_db()); // Assuming get_db() returns your database connection
        // Use a default service duration or get the minimum service duration
        $service_duration = 30; // Or get from services table
        $currentDate = date('Y-m-d');
        $availableSlots = $providerModel->getAvailableSlots($_SESSION['user_id'], $currentDate, $service_duration);
        
        // 2. Convert these slots to calendar events
        foreach ($availableSlots as $slot) {
            $events[] = [
                'id' => 'avail_' . uniqid(),
                'title' => date('g:ia', strtotime($slot['start_time'])) . ' Available',
                'start' => $slot['date'] . ' ' . $slot['start_time'],
                'end' => $slot['date'] . ' ' . $slot['end_time'],
                'color' => '#28a745', // Green
                'className' => 'available-slot' // Add a class for styling
            ];
        }
        
        // Get regular availability slots
        $regularAvailability = [];
        try {
            $stmt = $this->db->prepare("
                SELECT provider_id, availability_date, start_time, end_time
                FROM provider_availability
                WHERE provider_id = ? AND availability_date >= CURDATE()
            ");
            $provider_id = $_SESSION['user_id'];
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                // Add each regular availability slot as an event
                $start_dt = $row['availability_date'] . ' ' . $row['start_time'];
                $end_dt = $row['availability_date'] . ' ' . $row['end_time'];
                
                $events[] = [
                    'id' => 'avail_' . md5($start_dt . $end_dt),
                    'title' => date('g:ia', strtotime($row['start_time'])) . ' - ' . 
                              date('g:ia', strtotime($row['end_time'])) . ' Available',
                    'start' => $row['availability_date'] . 'T' . $row['start_time'],
                    'end' => $row['availability_date'] . 'T' . $row['end_time'],
                    'color' => '#28a745', // Green
                    'className' => 'regular-availability'
                ];
            }
        } catch (Exception $e) {
            error_log("Error fetching regular availability: " . $e->getMessage());
        }
        
        // 3. Add information to the view
        $data['events'] = json_encode($events);
        $data['availableSlots'] = $availableSlots;
        
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
    /**
     * Display provider profile page
     */
    public function profile() {
        // Check if user is logged in as a provider
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'provider') {
            base_url('auth/login');
        }
        
        // Get provider ID from session
        $provider_id = $_SESSION['user_id'];
        
        // Get provider data from database
        $provider = $this->providerModel->getProviderById($provider_id);
        
        if (!$provider) {
            // Handle case where provider data couldn't be found
            $_SESSION['error'] = "Could not retrieve your profile information. Please contact support.";
            base_url('provider/index');
        }
        
        // Pass data to view
        $data = [
            'provider' => $provider,
            'title' => 'Provider Profile' // For page title
        ];
        
        // Load view with provider data
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
    /**
     * Get appointments for the calendar
     */
    public function getAppointmentEvents() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        
        $calendarEvents = [];
        foreach ($appointments as $appointment) {
            // Safely get values with fallbacks for missing fields
            $patientName = $appointment['patient_name'] ?? 
                       $appointment['patient_first_name'] ?? 
                       $appointment['first_name'] ?? 
                       'Patient';
                       
            $serviceName = $appointment['service_name'] ?? 
                       $appointment['service'] ?? 
                       'Appointment';
                       
            $status = $appointment['status'] ?? 'scheduled';
            
            // Determine color based on status
            $statusColor = match($status) {
                'confirmed' => '#28a745',           // success/green
                'scheduled', 'pending' => '#ffc107', // warning/yellow
                'canceled' => '#dc3545',            // danger/red
                'completed' => '#0dcaf0',           // info/blue
                'no_show' => '#6c757d',             // secondary/gray
                default => '#6c757d'                // secondary/gray
            };
            
            $calendarEvents[] = [
                "id" => $appointment['id'] ?? $appointment['appointment_id'] ?? null,
                "title" => $patientName . " (" . $serviceName . ")",
                "start" => $appointment['appointment_date'] . "T" . $appointment['start_time'],
                "end" => $appointment['appointment_date'] . "T" . $appointment['end_time'],
                "color" => $statusColor,
                "description" => "Status: " . $status,
                "extendedProps" => [
                    "type" => "appointment",
                    "patient" => $patientName,
                    "service" => $serviceName,
                    "status" => $status
                ]
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($calendarEvents);
        exit;
    }

    /**
     * Get availability for the calendar
     */
    public function getAvailabilityEvents() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        
        // Use the providerModel to get availability
        $schedules = $this->providerModel->getAvailability($provider_id);
        
        $calendarEvents = [];
        foreach ($schedules as $schedule) {
            // Use the aliased field from our fixed query
            $dateField = $schedule['availability_date'] ?? $schedule['available_date'] ?? null;
            if (!$dateField) {
                continue; // Skip if no date field found
            }
            
            $calendarEvents[] = [
                "id" => $schedule['id'] ?? $schedule['availability_id'] ?? null,
                "title" => "Available",
                "start" => $dateField . "T" . $schedule['start_time'],
                "end" => $dateField . "T" . $schedule['end_time'],
                "color" => "#17a2b8", // Info color (lighter blue)
                "extendedProps" => [
                    "type" => "availability"
                ]
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($calendarEvents);
        exit;
    }
    /**
     * View an appointment's details
     * 
     * @param int $appointment_id The ID of the appointment to view
     */
    public function viewAppointment($appointment_id = null) {
        // Check if provider is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            redirect('auth/login');
            return;
        }
        
        $provider_id = $_SESSION['user_id'];
        
        // Check if appointment ID is provided
        if (!$appointment_id) {
            set_flash_message('error', 'No appointment specified');
            redirect('provider/appointments');
            return;
        }
        
        // Get appointment details
        $appointment = $this->appointmentModel->getById($appointment_id);
        
        // Verify this appointment belongs to the logged-in provider
        if (!$appointment || $appointment['provider_id'] != $provider_id) {
            set_flash_message('error', 'You do not have permission to view this appointment');
            redirect('provider/appointments');
            return;
        }
        
        // Get provider data
        $provider = $this->providerModel->getProviderData($provider_id);
        
        // Load the appointment view
        include VIEW_PATH . '/provider/view_appointment.php';
    }
    /**
     * Update appointment status
     * 
     * @param int $appointment_id The ID of the appointment
     * @param string $status The new status
     */
    public function updateAppointmentStatus($appointment_id, $status) {
        // Check if provider is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            // Instead of redirect('auth/login')
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        
        // Get appointment details to verify ownership
        $appointment = $this->appointmentModel->getById($appointment_id);
        
        // Validate permission (only provider who owns it can update)
        if (!$appointment || $appointment['provider_id'] != $provider_id) {
            // Set message in session instead of using flash helper
            $_SESSION['error_message'] = 'You do not have permission to update this appointment';
            header('Location: ' . base_url('index.php/provider/appointments'));
            exit;
        }
        
        // List of valid statuses
        $valid_statuses = ['scheduled', 'confirmed', 'canceled', 'completed', 'no_show', 'pending'];
        if (!in_array($status, $valid_statuses)) {
            $_SESSION['error_message'] = 'Invalid status';
            header('Location: ' . base_url('index.php/provider/viewAppointment/' . $appointment_id));
            exit;
        }
        
        // Update the appointment status
        $result = $this->appointmentModel->updateStatus($appointment_id, $status);
        
        if ($result) {
            $_SESSION['success_message'] = 'Appointment status updated successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to update appointment status';
        }
        
        // Redirect back to the appointment view
        header('Location: ' . base_url('index.php/provider/viewAppointment/' . $appointment_id));
        exit;
    }

    /**
     * Update appointment notes
     */
    public function updateAppointmentNotes() {
        // Check if provider is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        
        // Get form data
        $appointment_id = $_POST['appointment_id'] ?? null;
        $notes = $_POST['notes'] ?? '';
        
        if (!$appointment_id) {
            $_SESSION['error_message'] = 'No appointment specified';
            header('Location: ' . base_url('index.php/provider/appointments'));
            exit;
        }
        
        // Get appointment details to verify ownership
        $appointment = $this->appointmentModel->getById($appointment_id);
        
        // Validate permission (only provider who owns it can update notes)
        if (!$appointment || $appointment['provider_id'] != $provider_id) {
            $_SESSION['error_message'] = 'You do not have permission to update appointment notes';
            header('Location: ' . base_url('index.php/provider/appointments'));
            exit;
        }
        
        // Update the appointment notes
        $result = $this->appointmentModel->updateNotes($appointment_id, $notes);
        
        if ($result) {
            $_SESSION['success_message'] = 'Appointment notes updated successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to update appointment notes';
        }
        
        // Redirect back to the appointment view
        header('Location: ' . base_url('index.php/provider/viewAppointment/' . $appointment_id));
        exit;
    }

    /**
     * Process provider profile update
     */
    public function processUpdateProfile() {
        // Check if user is logged in as a provider
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'provider') {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
                exit;
            }
            $_SESSION['error'] = 'Unauthorized access';
            redirect('auth/login');
        }
        
        $provider_id = $_SESSION['user_id'];
        
        // Get form data for user table
        $userData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '')
        ];
        
        // Get form data for provider_profiles table
        $profileData = [
            'specialization' => trim($_POST['specialization'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'accepting_new_patients' => isset($_POST['accepting_new_patients']) ? 1 : 0,
            'max_patients_per_day' => (int)($_POST['max_patients_per_day'] ?? 0)
        ];
        
        // Validate required data
        if (empty($userData['first_name']) || empty($userData['last_name']) || empty($profileData['specialization'])) {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => 'Required fields cannot be empty']);
                exit;
            }
            $_SESSION['error'] = 'Required fields cannot be empty';
            redirect('provider/profile');
        }
        
        // Update user data first - using your existing method
        $userResult = $this->userModel->updateUser($provider_id, $userData);
        
        // Handle possible error array return
        if (is_array($userResult) && isset($userResult['error'])) {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => $userResult['error']]);
                exit;
            }
            $_SESSION['error'] = $userResult['error'];
            redirect('provider/profile');
        }
        
        // Update provider profile data
        $profileUpdateSuccess = $this->providerModel->updateProfile($provider_id, $profileData);
        
        // Determine overall success
        $success = $userResult && $profileUpdateSuccess;
        
        if ($success) {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => true]);
                exit;
            }
            $_SESSION['success'] = 'Profile updated successfully';
        } else {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
                exit;
            }
            $_SESSION['error'] = 'Failed to update profile';
        }
        
        header('Location: ' . base_url('index.php/provider/profile'));
        exit;
    }

    /**
     * Check if current request is AJAX
     */
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
?>