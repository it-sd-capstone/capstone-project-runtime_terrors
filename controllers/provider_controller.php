<?php
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/Appointment.php';
require_once MODEL_PATH . '/Services.php';


class ProviderController {
    private $db;
    private $providerModel;
    private $appointmentModel;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'provider' && $_SESSION['role'] !== 'admin')) {
            error_log("Unauthorized access attempt.");
            header('Location: ' . base_url('index.php/auth?error=Unauthorized access'));
            exit;
        }

        $this->db = get_db();
        $this->providerModel = new Provider($this->db);
        $this->appointmentModel = new Appointment($this->db);
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
        $provider_id = $_SESSION['user_id'];
        $schedules = $this->providerModel->getAvailability($provider_id);
    
        $events = [];
        foreach ($schedules as $schedule) {
            $events[] = [
                'id' => $schedule['schedule_id'],
                'title' => 'Available',
                'start' => $schedule['availability_date'] . 'T' . $schedule['start_time'],
                'end' => $schedule['availability_date'] . 'T' . $schedule['end_time']
            ];
        }
    
        header("Content-Type: application/json");
        echo json_encode($events);
        exit;
    }
    public function getAvailableSlots($provider_id) {
        $available_slots = $this->providerModel->getAvailability($provider_id);
        return $available_slots;
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

    // Add this method to your ProviderController class
    public function appointments() {
        $provider_id = $_SESSION['user_id'];
        
        // Get all appointments for this provider
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        
        // Load the appointments view
        include VIEW_PATH . '/provider/appointments.php';
    }
    
    // Add this method to your ProviderController class
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