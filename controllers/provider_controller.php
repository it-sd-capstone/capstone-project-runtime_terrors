<?php
require_once __DIR__ . '/../helpers/system_notifications.php';
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/Appointment.php';
require_once MODEL_PATH . '/Services.php';
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/Notification.php';
require_once __DIR__ . '/../helpers/validation_helpers.php';

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
            
        // Added by notification fixer
        $recentAppointments = $this->appointmentModel->getRecentAppointmentsByProvider($_SESSION['user_id']);
        // Notify about new appointments if any were created recently
        if (!empty($recentAppointments)) {
            set_flash_message('info', "You have new appointment bookings", 'provider_dashboard');
        }
            redirect('auth/login');
            return;
        }
        
        $provider_id = $_SESSION['user_id'];
        $providerId = $_SESSION['user_id']; // or however you get the provider's id

        // Example: get all reviews for this provider
        $reviews = $this->providerModel->getProviderReviews($providerId);
        
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
     * Delete all availability slots within a date range
     */
    public function deleteAvailabilityRange() {
        // Check for AJAX request to avoid direct access
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo "Direct access not allowed.";
            exit;
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        // Get the JSON data sent in the request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['start_date']) || !isset($data['end_date'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing required data']);
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        
        // Log information to debug the issue
        error_log("Deleting availability range for provider $provider_id from $start_date to $end_date");
        
        // Implement a temporary SQL query solution if the model method might not be available yet
        try {
            $stmt = $this->db->prepare("
                DELETE FROM provider_availability 
                WHERE provider_id = ? 
                AND availability_date BETWEEN ? AND ?
            ");
            
            $stmt->bind_param("iss", $provider_id, $start_date, $end_date);
            $stmt->execute();
            
            $count = $stmt->affected_rows;
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => "Successfully deleted availability slots in the selected range",
                'count' => $count
            ]);
        } catch (Exception $e) {
            error_log("Error deleting availability range: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Delete a schedule event (handles both regular and recurring)
     */
    public function deleteScheduleEvent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        
        // Get JSON data from request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing event ID']);
            exit;
        }
        
        $eventId = $data['id'];
        $success = false;
        $message = '';
        
        // Check if this is a recurring schedule or regular availability
        if (strpos($eventId, 'recurring_') === 0) {
            // Extract the actual schedule ID from the event ID
            $parts = explode('_', $eventId);
            $scheduleId = $parts[1] ?? null;
            
            if ($scheduleId) {
                // Delete recurring schedule using the new method
                $success = $this->providerModel->deleteRecurringSchedule($scheduleId, $provider_id);
                $message = $success ? 'Recurring schedule deleted' : 'Failed to delete recurring schedule';
            } else {
                $message = 'Invalid recurring schedule ID format';
            }
        } else {
            // For regular availability, extract numeric ID if it's not already
            $availabilityId = is_numeric($eventId) ? $eventId : 
                            (preg_match('/(\d+)/', $eventId, $matches) ? $matches[1] : null);
            
            if ($availabilityId) {
                // Use your existing method
                $success = $this->providerModel->deleteAvailability($availabilityId, $provider_id);
                $message = $success ? 'Availability slot deleted' : 'Failed to delete availability slot';
            } else {
                $message = 'Invalid availability ID format';
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
    /**
     * Generate service-specific availability slots based on recurring schedule
     */
    public function generateServiceSlots() {
        // Check if it's an AJAX request
        if (!$this->isAjaxRequest()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $provider_id = $_SESSION['user_id'];
        $services = json_decode($_POST['services'], true);
        $distribution = $_POST['distribution'];
        $period = (int)$_POST['period'];
        
        if (empty($services)) {
            echo json_encode(['success' => false, 'message' => 'No services selected']);
            return;
        }
        
        // Get recurring schedule patterns
        $recurringSchedules = $this->providerModel->getRecurringSchedules($provider_id);
        
        if (empty($recurringSchedules)) {
            echo json_encode([
                'success' => false, 
                'message' => 'No recurring schedule found. Please set up your weekly schedule first.'
            ]);
            return;
        }
        
        // Calculate end date based on period (in weeks)
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$period} week"));
        
        $count = 0;
        $current_date = new DateTime($start_date);
        $end = new DateTime($end_date);
        
        // Loop through each day in the specified period
        while ($current_date <= $end) {
            $day_of_week = $current_date->format('N'); // 1 (Mon) to 7 (Sun)
            if ($day_of_week == 7) $day_of_week = 0; // Convert to 0 (Sun) - 6 (Sat) format if needed
            
            // Find if there's a recurring schedule for this day
            $daySchedules = array_filter($recurringSchedules, function($schedule) use ($day_of_week) {
                return $schedule['day_of_week'] == $day_of_week;
            });
            
            if (!empty($daySchedules)) {
                foreach ($daySchedules as $schedule) {
                    $date = $current_date->format('Y-m-d');
                    
                    // For this day's schedule, create alternating slots for each service
                    $this->generateSlotsForDaySchedule(
                        $provider_id,
                        $date,
                        $schedule['start_time'],
                        $schedule['end_time'],
                        $services,
                        $distribution,
                        $count
                    );
                }
            }
            
            // Move to next day
            $current_date->add(new DateInterval('P1D'));
        }
        
        echo json_encode([
            'success' => true,
            'count' => $count,
            'message' => "Generated {$count} service-specific availability slots"
        ]);
        exit;
    }

    /**
     * Generate slots for a specific day schedule with multiple services
     */
    private function generateSlotsForDaySchedule($provider_id, $date, $start_time, $end_time, $services, $distribution, &$count) {
        // Start with the first service
        $serviceIndex = 0;
        $totalServices = count($services);
        
        if ($totalServices === 0) {
            return;
        }
        
        // Calculate time slots for this day
        $current_time = new DateTime($date . ' ' . $start_time);
        $day_end_time = new DateTime($date . ' ' . $end_time);
        
        // Different distribution strategies
        switch ($distribution) {
            case 'alternate':
                // Alternate between services for each slot
                while ($current_time < $day_end_time) {
                    $service = $services[$serviceIndex % $totalServices];
                    // Get duration from the correct field
                    $duration = isset($service['duration']) ? (int)$service['duration'] : 30;
                    
                    $slot_start = clone $current_time;
                    $slot_end = clone $current_time;
                    $slot_end->add(new DateInterval("PT{$duration}M"));
                    
                    // Only create the slot if it fits within the day's schedule
                    if ($slot_end <= $day_end_time) {
                        try {
                            $success = $this->providerModel->addAvailability([
                                'provider_id' => $provider_id,
                                'availability_date' => $date,
                                'start_time' => $slot_start->format('H:i:s'),
                                'end_time' => $slot_end->format('H:i:s'),
                                'service_id' => $service['id'],
                                'max_appointments' => 1,
                                'is_available' => 1
                            ]);
                            
                            if ($success) {
                                $count++;
                            }
                        } catch (Exception $e) {
                            // Silent exception handling
                        }
                    }
                    
                    // Move to next slot and service
                    $current_time = $slot_end;
                    $serviceIndex++;
                }
                break;
                
            case 'blocks':
                // Create blocks of the same service
                $blocksPerService = 3; // Number of consecutive slots for each service
                $blockCount = 0;
                
                while ($current_time < $day_end_time) {
                    $service = $services[$serviceIndex % $totalServices];
                    $duration = isset($service['duration']) ? (int)$service['duration'] : 30;
                    
                    $slot_start = clone $current_time;
                    $slot_end = clone $current_time;
                    $slot_end->add(new DateInterval("PT{$duration}M"));
                    
                    // Only create the slot if it fits within the day's schedule
                    if ($slot_end <= $day_end_time) {
                        try {
                            $success = $this->providerModel->addAvailability([
                                'provider_id' => $provider_id,
                                'availability_date' => $date,
                                'start_time' => $slot_start->format('H:i:s'),
                                'end_time' => $slot_end->format('H:i:s'),
                                'service_id' => $service['id'],
                                'max_appointments' => 1,
                                'is_available' => 1
                            ]);
                            
                            if ($success) {
                                $count++;
                            }
                        } catch (Exception $e) {
                            // Silent exception handling
                        }
                    }
                    
                    // Move to next slot
                    $current_time = $slot_end;
                    $blockCount++;
                    
                    // Move to next service after creating the specified number of blocks
                    if ($blockCount >= $blocksPerService) {
                        $blockCount = 0;
                        $serviceIndex++;
                    }
                }
                break;
                
            case 'priority':
                // Prioritize services in the order they were selected
                foreach ($services as $index => $service) {
                    $duration = isset($service['duration']) ? (int)$service['duration'] : 30;
                    $service_time = clone $current_time;
                    
                    while ($service_time < $day_end_time) {
                        $slot_start = clone $service_time;
                        $slot_end = clone $service_time;
                        $slot_end->add(new DateInterval("PT{$duration}M"));
                        
                        // Only create the slot if it fits within the day's schedule
                        if ($slot_end <= $day_end_time) {
                            try {
                                $success = $this->providerModel->addAvailability([
                                    'provider_id' => $provider_id,
                                    'availability_date' => $date,
                                    'start_time' => $slot_start->format('H:i:s'),
                                    'end_time' => $slot_end->format('H:i:s'),
                                    'service_id' => $service['id'],
                                    'max_appointments' => 1,
                                    'is_available' => 1
                                ]);
                                
                                if ($success) {
                                    $count++;
                                }
                            } catch (Exception $e) {
                                // Silent exception handling
                            }
                        } else {
                            break;
                        }
                        
                        // Move to next slot
                        $service_time = $slot_end;
                    }
                    
                    // Update the overall current time for the next service
                    if ($index === count($services) - 1) {
                        $current_time = $day_end_time; // End the loop after last service
                    }
                }
                break;
        }
    }


    /**
     * Handle editing a provider service (custom duration and notes)
     */
    public function editProviderService() {
        // Check if user is logged in and is a provider
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
set_flash_message('error', "Unauthorized access", 'auth_login');
            redirect('auth');
            return;
        }
        
        // Verify CSRF token if implemented
        if (function_exists('verify_csrf_token') && !verify_csrf_token()) {
set_flash_message('error', "Invalid form submission", 'provider_services');
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
            if (!isset($this->providerModel)) {
                $this->providerModel = $this->providerModel;
            }
            
            // Update the provider service
            $success = $this->providerModel->updateService(
                $provider_service_id, 
                $provider_id, 
                $custom_duration, 
                $custom_notes
            );
            
            if ($success) {
set_flash_message('success', "Service updated successfully", 'provider_services');
            } else {
set_flash_message('error', "Failed to update service", 'provider_services');
            }
        } else {
set_flash_message('error', "Invalid request", 'provider_services');
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
set_flash_message('error', "Unauthorized access", 'auth_login');
            redirect('auth');
            return;
        }
        
        // Verify CSRF token if implemented
        if (function_exists('verify_csrf_token') && !verify_csrf_token()) {
set_flash_message('error', "Invalid form submission", 'provider_services');
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
            if (!isset($this->providerModel)) {
                $this->providerModel = $this->providerModel;
            }
            
            // Delete the provider service association
            $success = $this->providerModel->deleteProviderService($provider_service_id, $provider_id);
            
            if ($success) {
set_flash_message('success', "Service removed from your offerings", 'provider_services');
            } else {
set_flash_message('error', "Failed to remove service", 'provider_services');
            }
        } else {
set_flash_message('error', "Invalid request", 'provider_services');
        }
        
        // Redirect back to the services page
        redirect('provider/services');
    }

   /**
     * Process service creation or update
     */
    public function processService() {
        // Comprehensive debugging
        error_log("ServiceController::processService called");
        error_log("POST data: " . json_encode($_POST));
        error_log("Session data: user_id=" . ($_SESSION['user_id'] ?? 'not set') . 
                ", role=" . ($_SESSION['role'] ?? 'not set'));
        
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            error_log("Not a POST request, redirecting");
            header('Location: ' . base_url('index.php/provider/services'));
            exit;
        }
        
        // Fix field name extraction - handle both possible field names
        $service_name = isset($_POST['service_name']) ? trim($_POST['service_name']) : 
                    (isset($_POST['name']) ? trim($_POST['name']) : '');
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 30;
        
        // Validate required fields
        if (empty($service_name)) {
            error_log("Service name is empty, aborting");
set_flash_message('error', "Service name is required", 'provider_services');
            header('Location: ' . base_url('index.php/provider/services'));
            exit;
        }
        
        // Prepare data for the model
        $serviceData = [
            'name' => $service_name,
            'description' => $description,
            'price' => $price,
            'duration' => $duration
        ];
        error_log("Service data prepared: " . json_encode($serviceData));
        
        // Load Services model for creating/updating the base service
        require_once MODEL_PATH . '/Services.php';
        $serviceModel = new Services($this->db);
        
        // Service ID handling for updates
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;
        
        if ($service_id) {
            // Update existing service
            error_log("Updating existing service ID: $service_id");
            $success = $serviceModel->updateService($service_id, $serviceData);
        } else {
            // Create new service
            error_log("Creating new service");
            $service_id = $serviceModel->createService($serviceData);
            $success = ($service_id !== false);
        }
        
        error_log("Service operation result: " . ($success ? "success (ID: $service_id)" : "failure"));
        
        // Associate with provider if applicable
        if ($success && isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'provider') {
            $provider_id = $_SESSION['user_id'];
            error_log("Attempting to associate service $service_id with provider $provider_id");
            
            // Load Provider model
            require_once MODEL_PATH . '/Provider.php';
            $providerModel = new Provider($this->db);
            
            // Check if this method exists and is correctly implemented
            if (method_exists($providerModel, 'addServiceToProvider')) {
                $provider_result = $providerModel->addServiceToProvider($provider_id, $service_id);
                error_log("Provider association result: " . ($provider_result ? "success" : "failure"));
            } else {
                error_log("ERROR: Method 'addServiceToProvider' does not exist in Provider model!");
            }
        } else {
            error_log("Not associating with provider. Success=$success, User ID=" . 
                    ($_SESSION['user_id'] ?? 'not set') . ", Role=" . ($_SESSION['role'] ?? 'not set'));
        }
        
        // Set response message
        if ($success) {
set_flash_message('success', $service_id ? "Service created successfully!" : "Service updated successfully!", 'provider_services');
        } else {
set_flash_message('error', "Failed to " . ($service_id ? "update" : "create") . " service. Please try again.", 'provider_services');
        }
        
        // Redirect back to the services page
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
        
        // Check which view we're rendering for
        $view_type = $_GET['view'] ?? 'dayGridMonth';
        $isMonthView = ($view_type === 'dayGridMonth');
        
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
                    'status' => 'active',
                    'excluded_dates' => [] // Add empty excluded dates array
                ];
            }
        }
        
        // Format events for FullCalendar
        $events = [];
        
        // CONSOLIDATION FOR MONTH VIEW ONLY
        if ($isMonthView) {
            // Track dates with availability to avoid duplicates
            $availableDates = [];
            $recurringDates = [];
            
            // Process regular availability for month view (grouped by date)
            foreach ($schedules as $schedule) {
                $date = $schedule['availability_date'] ?? $schedule['date'] ??
                    $schedule['schedule_date'] ?? date('Y-m-d');
                    
                // If we haven't added this date yet, add a consolidated event
                if (!isset($availableDates[$date])) {
                    $availableDates[$date] = true;
                    
                    $events[] = [
                        'id' => 'consolidated_avail_' . md5($date),
                        'title' => 'Available',
                        'start' => $date,
                        'allDay' => true,
                        'color' => '#28a745',
                        'className' => 'consolidated-event',
                        'extendedProps' => [
                            'type' => 'consolidated',
                            'date' => $date
                        ]
                    ];
                }
            }
            
            // Process recurring schedules for month view (one entry per day)
            $dayOfWeekMap = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            
            foreach ($recurringSchedules as $recurring) {
                $dayIndex = $recurring['day_of_week'];
                if ($dayIndex < 0 || $dayIndex > 6) continue;
                    
                $dayName = $dayOfWeekMap[$dayIndex];
                $startTime = $recurring['start_time'];
                $endTime = $recurring['end_time'];
                    
                // Format times for display (12-hour with am/pm)
                $formattedStart = date('g:ia', strtotime($startTime));
                $formattedEnd = date('g:ia', strtotime($endTime));
                    
                // Generate events for the next 4 weeks
                for ($i = 0; $i < 4; $i++) {
                    $date = date('Y-m-d', strtotime("+$i week $dayName"));
                    
                    // Skip if this date is in the excluded dates array
                    if (isset($recurring['excluded_dates']) && in_array($date, $recurring['excluded_dates'])) {
                        continue;
                    }
                    
                    // Skip if we already have a recurring event for this date
                    if (isset($recurringDates[$date])) continue;
                    $recurringDates[$date] = true;
                    
                    $events[] = [
                        'id' => 'consolidated_recurring_' . md5($date),
                        'title' => 'Working Hours ' . $formattedStart . '-' . $formattedEnd,
                        'start' => $date,
                        'allDay' => true,
                        'color' => '#17a2b8', // Blue for recurring
                        'className' => 'consolidated-recurring',
                        'extendedProps' => [
                            'type' => 'consolidated_recurring',
                            'date' => $date,
                            'start_time' => $startTime,
                            'end_time' => $endTime
                        ]
                    ];
                }
            }
        } else {
            // DETAILED VIEW FOR WEEK/DAY VIEWS
            
            // Format regular availability slots with time in title
            foreach ($schedules as $schedule) {
                $date = $schedule['availability_date'] ?? $schedule['date'] ??
                    $schedule['schedule_date'] ?? date('Y-m-d');
                $startTime = $schedule['start_time'] ?? '09:00:00';
                $endTime = $schedule['end_time'] ?? '17:00:00';
                    
                // Format times for display (12-hour with am/pm)
                $formattedStart = date('g:ia', strtotime($startTime));
                $formattedEnd = date('g:ia', strtotime($endTime));
                    
                $event = [
                    'id' => $schedule['availability_id'] ?? $schedule['id'] ?? $schedule['schedule_id'] ?? ('reg_' . uniqid()),
                    'title' => 'Available ' . $formattedStart . '-' . $formattedEnd,
                    'start' => $date . 'T' . $startTime,
                    'end' => $date . 'T' . $endTime,
                    'color' => '#28a745',
                    'extendedProps' => [
                        'type' => 'availability',
                        'original_id' => $schedule['availability_id'] ?? $schedule['id'] ?? null
                    ]
                ];
                    
                $events[] = $event;
            }
            
            // Format recurring availability with time in title
            $dayOfWeekMap = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            
            foreach ($recurringSchedules as $recurring) {
                $dayIndex = $recurring['day_of_week'];
                if ($dayIndex < 0 || $dayIndex > 6) continue;
                    
                $dayName = $dayOfWeekMap[$dayIndex];
                $startTime = $recurring['start_time'];
                $endTime = $recurring['end_time'];
                    
                // Format times for display (12-hour with am/pm)
                $formattedStart = date('g:ia', strtotime($startTime));
                $formattedEnd = date('g:ia', strtotime($endTime));
                    
                // Generate events for the next 4 weeks
                for ($i = 0; $i < 4; $i++) {
                    $date = date('Y-m-d', strtotime("+$i week $dayName"));
                    
                    // Skip if this date is in the excluded dates array
                    if (isset($recurring['excluded_dates']) && in_array($date, $recurring['excluded_dates'])) {
                        continue;
                    }
                    
                    $events[] = [
                        'id' => 'recurring_' . $recurring['schedule_id'] . '_' . $i,
                        'title' => 'Working Hours ' . $formattedStart . '-' . $formattedEnd,
                        'start' => $date . 'T' . $startTime,
                        'end' => $date . 'T' . $endTime,
                        'color' => '#17a2b8', // Blue for recurring
                        'extendedProps' => [
                            'type' => 'recurring',
                            'original_id' => $recurring['schedule_id']
                        ]
                    ];
                }
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }

       
    /**
     * Generate availability slots from recurring schedule
     */
    public function generateAvailabilityFromSchedule() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        
        // Get JSON data from request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // Get parameters
        $slotDuration = isset($data['slotDuration']) ? intval($data['slotDuration']) : 30; // Default to 30 minutes
        $weeksAhead = isset($data['weeksAhead']) ? intval($data['weeksAhead']) : 2; // Default to 2 weeks
        
        // Get recurring schedules
        $recurringSchedules = $this->providerModel->getRecurringSchedules($provider_id);
        $slotsCreated = 0;
        
        // Process each recurring schedule
        foreach ($recurringSchedules as $schedule) {
            $dayOfWeek = $schedule['day_of_week'];
            $dayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$dayOfWeek];
            
            // Generate slots for the specified number of weeks ahead
            for ($week = 0; $week < $weeksAhead; $week++) {
                // Calculate the next occurrence of this day of the week
                $nextDate = date('Y-m-d', strtotime("+$week week $dayName"));
                
                // If the date is in the past, skip it
                if (strtotime($nextDate) < strtotime(date('Y-m-d'))) {
                    continue;
                }
                
                // Generate slots based on the specified duration
                $startTime = strtotime($schedule['start_time']);
                $endTime = strtotime($schedule['end_time']);
                
                for ($time = $startTime; $time < $endTime; $time += ($slotDuration * 60)) {
                    $slotStart = date('H:i:s', $time);
                    $slotEnd = date('H:i:s', $time + ($slotDuration * 60));
                    
                    // Add availability slot
                    $success = $this->providerModel->addAvailability([
                        'provider_id' => $provider_id,
                        'availability_date' => $nextDate,
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                        'max_appointments' => 1, // Default to 1 appointment per slot
                        'is_available' => 1 // Set as available
                    ]);
                    
                    if ($success) {
                        $slotsCreated++;
                    }
                }
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'slotsCreated' => $slotsCreated,
            'message' => "Successfully created $slotsCreated bookable appointment slots"
        ]);
        exit;
    }
    
public function getNextAvailableSlot($provider_id, $service_duration = 30, $after_date = null) {
    $after_date = $after_date ?: date('Y-m-d');
    $stmt = $this->db->prepare("
        SELECT * FROM provider_availability
        WHERE provider_id = ? AND availability_date >= ? AND is_available = 1
        ORDER BY availability_date ASC, start_time ASC
        LIMIT 1
    ");
    $stmt->bind_param("is", $provider_id, $after_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $slots = [];
    while ($row = $result->fetch_assoc()) {
        $slots[] = $row;
    }
    return $slots;
}
    /**
     * Clear all availability for a specific day
     */
    public function clearDayAvailability() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        // Get the JSON data sent in the request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['date'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing required data']);
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        $date = $data['date'];
        
        // Call the provider model to clear the day's availability
        $count = $this->providerModel->clearDayAvailability($provider_id, $date);
        
        header('Content-Type: application/json');
        if ($count !== false) {
            echo json_encode([
                'success' => true, 
                'message' => "Cleared all availability for $date",
                'count' => $count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to clear day availability']);
        }
        exit;
    }

    public function createRecurringSchedule($provider_id, $start_date, $end_date, $days_of_week, $start_time, $end_time) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO recurring_schedules (provider_id, start_date, end_date, day_of_week, start_time, end_time) 
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
            $day_of_week = $_POST['day_of_week'] ?? null; // single value: 0-6
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            $is_active = $_POST['is_active'] ?? 1;
            $repeat_weekly = $_POST['repeat_weekly'] ?? '0';
            $repeat_until = $_POST['repeat_until'] ?? null;

            // Use today as the start date
            $start_date = date('Y-m-d');
            // Use repeat_until as the end date, or default to 1 month from now if not set
            $end_date = $repeat_until ?: date('Y-m-d', strtotime('+1 month'));

            if (!$provider_id || $day_of_week === null || !$start_time || !$end_time) {
set_flash_message('error', "All required fields must be filled.", 'provider_schedule');
                header("Location: " . base_url("index.php/provider/schedule"));
                exit;
            }

            // Use providerModel instead of scheduleModel
            if ($repeat_weekly === '1') {
                $success = $this->providerModel->addRecurringSchedule(
                    $provider_id,
                    $day_of_week,
                    $start_time,
                    $end_time,
                    $is_active
                );
            } else {
                // For single slot, use the appropriate method from providerModel
                $next_date = date('Y-m-d', strtotime("next " . jddayofweek($day_of_week, 1)));
                $success = $this->providerModel->addAvailability([
                    'provider_id' => $provider_id,
                    'availability_date' => $next_date,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'is_available' => $is_active
                ]);
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Schedule created successfully!' : 'Failed to create schedule.'
            ]);
            exit;
        }
    
        // If not a POST request, redirect back to the schedule page
        header('Location: ' . base_url('index.php/provider/schedule'));
        exit;
    }

    /**
     * Delete work schedule (recurring schedules) in a specified date range
     */
    public function deleteWorkSchedule() {
        // Verify that the user is logged in and is a provider
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized access'
            ]);
            exit;
        }
        
        // Check if the request is AJAX and POST
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                
        if (!$isAjax || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
            exit;
        }
        
        // Get the JSON data from the request body
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        
        // Validate the data
        if (!isset($data['start_date']) || !isset($data['end_date'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Missing required parameters'
            ]);
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        
        // Validate dates
        if (!$this->isValidDate($start_date) || !$this->isValidDate($end_date)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid date format'
            ]);
            exit;
        }
        
        // If start date is after end date, swap them
        if (strtotime($start_date) > strtotime($end_date)) {
            $temp = $start_date;
            $start_date = $end_date;
            $end_date = $temp;
        }
        
        $result = false;
        $deleted_count = 0;
        
        // Determine which action to take based on date range
        if ($start_date === $end_date) {
            // Single day: Create a deletion marker for this specific date
            $day_of_week = date('w', strtotime($start_date));
            $result = $this->providerModel->deleteRecurringSchedulesByDay($provider_id, $day_of_week, $start_date);
            $deleted_count = $result;
        }
        elseif (date('Y-m', strtotime($start_date)) === date('Y-m', strtotime($end_date)) && 
                $start_date === date('Y-m-01', strtotime($start_date)) && 
                $end_date === date('Y-m-t', strtotime($end_date))) {
            // Entire month: Delete all recurring schedules for this provider
            // This is a special case where we delete all schedules since they're recurring
            // and will affect all future months as well
            $result = $this->providerModel->deleteAllRecurringSchedules($provider_id);
            $deleted_count = $result;
        }
        else {
            // Custom date range: Delete recurring schedules for days in this range
            $result = $this->providerModel->deleteRecurringSchedulesInRange($provider_id, $start_date, $end_date);
            $deleted_count = $result;
        }
        
        // Send the response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'count' => $deleted_count,
            'message' => $result 
                ? "Successfully deleted $deleted_count recurring work schedule(s)" 
                : 'No work schedules were found to delete'
        ]);
        exit;
    }

    /**
     * Helper method to validate date format (YYYY-MM-DD)
     */
    private function isValidDate($date) {
        $format = 'Y-m-d';
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }



    public function viewProfile() {
    // Check if user is logged in as a provider
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'provider') {
        header('Location: ' . base_url('index.php/auth/login'));
        exit;
    }

    // Get provider ID from session
    $provider_id = $_SESSION['user_id'];

    // Get provider data from database
    $provider = $this->providerModel->getProviderById($provider_id);

    if (!$provider) {
        set_flash_message('error', "Could not retrieve your profile information. Please contact support.", 'provider_profile');
        header('Location: ' . base_url('index.php/provider/index'));
        exit;
    }

    // Pass data to view
    $data = [
        'provider' => $provider,
        'title' => 'Provider Profile' // For page title
    ];

    // Load view with provider data (read-only)
    include VIEW_PATH . '/provider/view_profile.php';
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
set_flash_message('error', "You must be logged in to access this page", 'auth_login');
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }

        // Determine if this is an admin managing a provider's services
        $isAdminManaging = isset($_SESSION['admin_managing_provider_id']);
        $providerId = $isAdminManaging ? $_SESSION['admin_managing_provider_id'] : $_SESSION['user_id'];

        // If admin is managing, verify they are actually an admin
        if ($isAdminManaging && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
            unset($_SESSION['admin_managing_provider_id']);
set_flash_message('error', "You don't have permission to manage provider services", 'provider_services');
            header('Location: ' . base_url('index.php/admin'));
            exit;
        }

        // Get provider details
        $provider = $this->userModel->getUserById($providerId);

        if (!$provider || $provider['role'] !== 'provider') {
set_flash_message('error', "Provider not found", 'admin_providers');
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
    public function updateNotificationSettings()
{
    // Ensure user is logged in and is a provider
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
set_flash_message('error', "Unauthorized access", 'auth_login');
        header('Location: ' . base_url('index.php/auth'));
        exit;
    }

    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
set_flash_message('error', "Invalid request method", 'global');
        header('Location: ' . base_url('index.php/provider/notifications'));
        exit;
    }

    $provider_id = $_SESSION['user_id'];

    // Get settings from POST (checkboxes: checked = 'on', unchecked = not set)
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $appointment_reminders = isset($_POST['appointment_reminders']) ? 1 : 0;
    $system_updates = isset($_POST['system_updates']) ? 1 : 0;

    // Save settings - you can use a dedicated table or a JSON/settings field in provider profile
    // Here, we'll assume a notification_preferences table with provider_id as PK
    try {
        $stmt = $this->db->prepare("
            INSERT INTO notification_preferences (user_id, email_notifications, appointment_reminders, system_updates)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                email_notifications = VALUES(email_notifications),
                appointment_reminders = VALUES(appointment_reminders),
                system_updates = VALUES(system_updates)
        ");
        $stmt->bind_param("iiii", $provider_id, $email_notifications, $appointment_reminders, $system_updates);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
set_flash_message('success', "Notification settings updated successfully.", 'global');
        } else {
set_flash_message('error', "Failed to update notification settings.", 'global');
        }
    } catch (Exception $e) {
        error_log("Error updating notification settings: " . $e->getMessage());
set_flash_message('error', "Database error: " . $e->getMessage(), 'global');
    }

    header('Location: ' . base_url('index.php/provider/notifications'));
    exit;
}
    /**
     * Display provider notifications
     */
    public function notifications() {
        // Check if user is logged in and is a provider
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
set_flash_message('error', "You must be logged in as a provider to view notifications", 'auth_login');
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        
        // Get notifications for this provider
        $notifications = $this->notificationModel->getUserNotifications($provider_id);
        
        // Get unread count
        $unreadCount = $this->notificationModel->getUnreadCount($provider_id);
        $stmt = $this->db->prepare("SELECT email_notifications, appointment_reminders, system_updates FROM notification_preferences WHERE user_id = ?");
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notificationSettings = $result->fetch_assoc() ?: [
            'email_notifications' => 1,
            'appointment_reminders' => 1,
            'system_updates' => 1
        ];
        
        // Include the notifications view
        include VIEW_PATH . '/provider/notifications.php';
    }

    public function manage_services() {
        require_once MODEL_PATH . '/Services.php';
        $serviceModel = new Services($this->db);
    
        $services = $serviceModel->getAllServices(); // Fetch services from the database
        
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
    /**
     * Delete a schedule slot or recurring schedule
     */
    public function deleteSchedule() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        // Get JSON data from request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing event ID']);
            exit;
        }
        
        $eventId = $data['id'];
        $success = false;
        
        // Check if this is a recurring schedule or regular availability
        if (strpos($eventId, 'recurring_') === 0) {
            // Extract the actual schedule ID from the event ID
            $parts = explode('_', $eventId);
            $scheduleId = $parts[1] ?? null;
            
            if ($scheduleId) {
                // Delete recurring schedule
                $success = $this->providerModel->deleteRecurringSchedule($scheduleId);
            }
        } else {
            // Delete regular availability
            $success = $this->providerModel->deleteAvailabilitySlot($eventId);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
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
            // Check if we have proper date/time fields
            $date = $slot['date'] ?? $slot['appointment_date'] ?? $slot['availability_date'] ?? date('Y-m-d');
            $startTime = $slot['start_time'] ?? $slot['start'] ?? '09:00:00';
            $endTime = $slot['end_time'] ?? $slot['end'] ?? '17:00:00';
            
            // Format the title with proper error handling
            $title = '';
            if (isset($slot['start_time']) && !empty($slot['start_time'])) {
                $title = date('g:ia', strtotime($slot['start_time'])) . ' Available';
            } else {
                $title = 'Available Slot';
            }
            
            $events[] = [
                'id' => 'avail_' . uniqid(),
                'title' => $title,
                'start' => $date . ' ' . $startTime,
                'end' => $date . ' ' . $endTime,
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
        
        // Load the Services model if not already loaded
        if (!isset($this->serviceModel)) {
            require_once MODEL_PATH . '/Services.php';
            $this->serviceModel = new Services($this->db);
        }
        
        // Fetch provider services for the dropdown
        $provider_services = $this->serviceModel->getProviderServices($provider_id);
        
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
        
        // Log the incoming data for debugging
        error_log("Schedule update received data: " . json_encode($data));
        
        if (!$data) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }
        
        // Check if we have the required ID field
        if (!isset($data['id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing event ID']);
            exit;
        }
        
        $eventId = $data['id'];
        $eventType = $data['type'] ?? 'regular';
        
        // Handle different data formats
        $date = null;
        $startTime = null;
        $endTime = null;
        
        // Check for ISO datetime format (2023-05-08T09:00:00)
        if (isset($data['start']) && isset($data['end'])) {
            // Parse start datetime
            if (strpos($data['start'], 'T') !== false) {
                list($date, $startTime) = explode('T', $data['start']);
            } else {
                // Handle fallback if format is different
                $startDate = new DateTime($data['start']);
                $date = $startDate->format('Y-m-d');
                $startTime = $startDate->format('H:i:s');
            }
            
            // Parse end datetime
            if (strpos($data['end'], 'T') !== false) {
                list(, $endTime) = explode('T', $data['end']);
            } else {
                // Handle fallback if format is different
                $endDate = new DateTime($data['end']);
                $endTime = $endDate->format('H:i:s');
            }
        } 
        // Check for separate date/time fields
        else if (isset($data['date'])) {
            $date = $data['date'];
            $startTime = $data['start_time'] ?? null;
            $endTime = $data['end_time'] ?? null;
        }
        
        // Validate we have all required data
        if (!$date || !$startTime || !$endTime) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Missing required date/time data',
                'debug' => [
                    'date' => $date,
                    'startTime' => $startTime,
                    'endTime' => $endTime
                ]
            ]);
            exit;
        }
        
        // Process based on event type
        $success = false;
        if (strpos($eventId, 'recurring_') === 0) {
            // Handle recurring schedule updates
            $parts = explode('_', $eventId);
            $scheduleId = $parts[1] ?? null;
            
            if ($scheduleId) {
                $success = $this->providerModel->updateRecurringSchedule($scheduleId, $startTime, $endTime);
            }
        } else {
            // Handle regular availability updates
            $success = $this->providerModel->updateAvailabilitySlot($eventId, $date, $startTime, $endTime);
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => [
                'id' => $eventId,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]
        ]);
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
set_flash_message('error', "Could not retrieve your profile information. Please contact support.", 'provider_profile');
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
            // Instead of 
        // Added by notification fixer
        $success = true;
        if ($success) {
            set_flash_message('success', "Appointment status updated successfully", 'provider_appointments');
        } else {
            set_flash_message('error', "Failed to update appointment status", 'provider_appointments');
        }
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
            set_flash_message('error', 'Unauthorized access', 'auth_login');
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        $provider_id = $_SESSION['user_id'];
        
        // Initialize errors array
        $errors = [];
        
        // Get form data from POST
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $accepting_new_patients = isset($_POST['accepting_new_patients']) ? 1 : 0;
        $max_patients_per_day = (int)($_POST['max_patients_per_day'] ?? 0);
        
        // Validate first name
        if (empty($first_name)) {
            $errors[] = "First name is required.";
        } else {
            $firstNameValidation = validateName($first_name);
            if (!$firstNameValidation['valid']) {
                $errors[] = $firstNameValidation['error'];
            } else {
                $first_name = $firstNameValidation['sanitized'];
            }
        }
        
        // Validate last name
        if (empty($last_name)) {
            $errors[] = "Last name is required.";
        } else {
            $lastNameValidation = validateName($last_name);
            if (!$lastNameValidation['valid']) {
                $errors[] = $lastNameValidation['error'];
            } else {
                $last_name = $lastNameValidation['sanitized'];
            }
        }
        
        // Continue with update only if no errors
        if (!empty($errors)) {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
                exit;
            }
            set_flash_message('error', implode('<br>', $errors), 'provider_profile');
            header('Location: ' . base_url('index.php/provider/profile'));
            exit;
        }
        
        // Validate required data
        if (empty($first_name) || empty($last_name) || empty($specialization)) {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => 'Required fields cannot be empty']);
                exit;
            }
            set_flash_message('error', 'Required fields cannot be empty', 'provider_profile');
            header('Location: ' . base_url('index.php/provider/profile'));
            exit;
        }
        
        // Prepare data for update
        $userData = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone
        ];
        
        // Add the title field which is expected by the Provider model
        $profileData = [
            'specialization' => $specialization,
            'title' => '',  // Add this empty title since the model expects it
            'bio' => $bio,
            'accepting_new_patients' => $accepting_new_patients,
            'max_patients_per_day' => $max_patients_per_day
        ];
        
        // Update user data
        $userResult = $this->userModel->updateUser($provider_id, $userData);
        
        // Handle possible error array return
        if (is_array($userResult) && isset($userResult['error'])) {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => $userResult['error']]);
                exit;
            }
            set_flash_message('error', $userResult['error'], 'provider_profile');
            header('Location: ' . base_url('index.php/provider/profile'));
            exit;
        }
        
        // Update provider profile data
        $profileUpdateSuccess = $this->providerModel->updateProfile($provider_id, $profileData);
        
        // Debug output to check what's happening
        error_log("userResult: " . var_export($userResult, true));
        error_log("profileUpdateSuccess: " . var_export($profileUpdateSuccess, true));
        
        // Simpler success determination - just check if providerModel update succeeded
        // since we already handled any userModel errors above
        $success = $profileUpdateSuccess;
        
        if ($success) {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => true]);
                exit;
            }
            set_flash_message('success', 'Profile updated successfully', 'provider_profile');
        } else {
            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
                exit;
            }
            set_flash_message('error', 'Failed to update profile', 'provider_profile');
        }
        
        header('Location: ' . base_url('index.php/provider/profile'));
        exit;
    }


    /**
     * Deactivate the provider account (set is_active = 0)
     */
    public function deactivateAccount() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    set_flash_message('error', "Unauthorized access", 'auth_login');
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }

        $provider_id = $_SESSION['user_id'];
        $success = $this->providerModel->setActiveStatus($provider_id, 0);

        if ($success) {
    set_flash_message('success', "Your account has been deactivated.", 'auth_login');
            // Optionally, log the user out after deactivation
            session_destroy();
            header('Location: ' . base_url('index.php/auth/login?success=Account deactivated'));
        } else {
    set_flash_message('error', "Failed to deactivate account. Please try again.", 'provider_profile');
            header('Location: ' . base_url('index.php/provider/profile'));
        }
        exit;
    }

/**
 * Set accepting new patients status (1 = accepting, 0 = not accepting)
 * Usage: Call with $accepting = 0 to stop accepting new patients
 */
public function setAcceptingNewPatients($accepting = 0) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
set_flash_message('error', "Unauthorized access", 'auth_login');
        header('Location: ' . base_url('index.php/auth'));
        exit;
    }

    $provider_id = $_SESSION['user_id'];
    $accepting = $accepting ? 1 : 0; // Ensure it's 0 or 1

    $success = $this->providerModel->setAcceptingNewPatients($provider_id, $accepting);

    if ($success) {
        $_SESSION['success'] = $accepting
            ? "You are now accepting new patients."
            : "You have stopped accepting new patients.";
    } else {
set_flash_message('error', "Failed to update accepting new patients status.", 'provider_profile');
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

    /**
     * Cancel Appointment
     */
    public function cancelAppointment($appointment_id) {
    // Log system event
    if ($success) {
        logSystemEvent('appointment_cancelled', 'An appointment was cancelled in the system', 'Appointment Cancelled');
    }

        // Get appointment ID from request
        $appointment_id = $_POST['appointment_id'] ?? $appointment_id;
        $success = $this->appointmentModel->cancelAppointment($appointment_id, $_SESSION['user_id']);
    
        if ($success) {
            set_flash_message('success', "Appointment cancelled successfully", 'provider_appointments');
        } else {
            set_flash_message('error', "Failed to cancel appointment", 'provider_appointments');
        }
}

    /**
     * Reschedule Appointment
     */
    public function rescheduleAppointment($appointment_id, $new_datetime = null) {
        // Get appointment ID and new time from request
        $appointment_id = $_POST['appointment_id'] ?? $appointment_id;
        $new_datetime = $_POST['new_datetime'] ?? $new_datetime;
        $success = $this->appointmentModel->rescheduleAppointment($appointment_id, $new_datetime);
    
        if ($success) {
            set_flash_message('success', "Appointment rescheduled successfully", 'provider_appointments');
        } else {
            set_flash_message('error', "Failed to reschedule appointment", 'provider_appointments');
        }
}

    /**
     * Update Availability
     */
    public function updateAvailability() {
        // Update provider availability
        $provider_id = $_SESSION['user_id'];
        $availability_data = $_POST['availability'] ?? [];
        $result = $this->providerModel->updateAvailability($provider_id, $availability_data);
    
        if ($result) {
            set_flash_message('success', "Your availability has been updated", 'provider_schedule');
        } else {
            set_flash_message('error', "Failed to update your availability", 'provider_schedule');
        }
}

    /**
     * Book Time Slot
     */
    public function bookTimeSlot($provider_id, $date, $start_time, $end_time) {
        // Book a new time slot
        $provider_id = $_SESSION['user_id'];
        $date = $_POST['date'] ?? $date;
        $start_time = $_POST['start_time'] ?? $start_time;
        $end_time = $_POST['end_time'] ?? $end_time;
        $success = $this->providerModel->addTimeSlot($provider_id, $date, $start_time, $end_time);
    
        set_flash_message('success', "Time slot booked successfully", 'provider_schedule');
}
}
?>