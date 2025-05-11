<?php
require_once MODEL_PATH . '/Services.php';
require_once MODEL_PATH . '/Provider.php';

class ServiceController {
    protected $db;
    protected $serviceModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = get_db();

        $this->providerServicesModel = new Provider($this->db);
        $this->serviceModel = new Services($this->db);
    }

    /**
     * List all services offered by the current provider
     */
    public function services() {
        $provider_id = $_SESSION['user_id'];
        
        // Get services offered by this provider
        $services = $this->serviceModel->getServicesByProvider($provider_id);
        
        // Get all services for the add form
        $all_services = $this->serviceModel->getAllServices();
        
        // Find services not already linked to this provider
        $offered_service_ids = array_column($services, 'service_id');
        $available_services = array_filter($all_services, function($s) use ($offered_service_ids) {
            return !in_array($s['service_id'], $offered_service_ids);
        });
        
        // Make sure to pass available_services to the view with array keys preserved
        $available_services = array_values($available_services);
        
        include VIEW_PATH . '/provider/services.php';
    }

    /**
     * Process service creation or update
     */
    public function processService() {

        $errors = [];
        error_log("Session user role: " . ($_SESSION['user_role'] ?? 'not set'));
        error_log("Session user ID: " . ($_SESSION['user_id'] ?? 'not set'));
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

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
            $_SESSION['error'] = "Service name is required";
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
            

            $name = $_POST['service_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $duration = $_POST['duration'] ?? 30;
            $serviceId = $_POST['service_id'] ?? null;

            // Perform action based on request
            if (count($errors) === 0) {
                switch ($action) {
                    case 'add':
                        $serviceData = [
                            'name' => $name,  // Use 'name' to match the database column
                            'description' => $description,
                            'price' => $price,
                            'duration' => $duration
                        ];
                        error_log("Data being sent to model: " . json_encode($serviceData));
                        error_log("Calling createService with data");
                        $result = $this->serviceModel->createService($serviceData);
                        error_log("createService result: " . ($result ? "success" : "failure"));
                        break;
                }

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
            $_SESSION['success'] = $service_id ? "Service created successfully!" : "Service updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to " . ($service_id ? "update" : "create") . " service. Please try again.";
        }
        
        // Redirect back to the services page
        header('Location: ' . base_url('index.php/provider/services'));
        exit;
    }


    /**
     * Add a service to the provider's offerings
     */
    public function addProviderService() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $provider_id = $_SESSION['user_id'];
            $service_id = intval($_POST['service_id']);
            $custom_duration = !empty($_POST['custom_duration']) ? intval($_POST['custom_duration']) : null;
            $custom_notes = trim($_POST['custom_notes'] ?? '');
            $success = $this->serviceModel->addService($provider_id, $service_id, $custom_duration, $custom_notes);
            $_SESSION[$success ? 'success' : 'error'] = $success ? "Service added to your offerings." : "Failed to add service.";
            header('Location: ' . base_url('index.php/provider/services'));
            exit;
        }
    }

    /**
     * Edit a provider's service customization
     */
    public function editProviderService() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $provider_service_id = intval($_POST['provider_service_id']);
            $custom_duration = !empty($_POST['custom_duration']) ? intval($_POST['custom_duration']) : null;
            $custom_notes = trim($_POST['custom_notes'] ?? '');
            $success = $this->serviceModel->editService($provider_service_id, $custom_duration, $custom_notes);
            $_SESSION[$success ? 'success' : 'error'] = $success ? "Service updated." : "Failed to update service.";
            header('Location: ' . base_url('index.php/provider/services'));
            exit;
        }
    }

    /**
     * Remove a service from the provider's offerings
     */
    public function deleteProviderService() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get provider_service_id from the form
            $provider_service_id = isset($_POST['provider_service_id']) ? intval($_POST['provider_service_id']) : 0;
            
            // Get provider_id from the session instead of from POST data
            $provider_id = $_SESSION['user_id'];
            
            // Debug what we're working with
            error_log("Deleting service: provider_service_id=$provider_service_id, provider_id=$provider_id");
            
            if (!$provider_service_id || !$provider_id) {
                $_SESSION['error'] = "Missing required information";
                header('Location: ' . base_url('index.php/provider/services'));
                exit;
            }
            
            // Load the provider model
            require_once MODEL_PATH . '/Provider.php';
            $providerModel = new Provider($this->db);
            
            // Call your existing method
            $result = $providerModel->deleteService($provider_service_id, $provider_id);
            
            if ($result) {
                $_SESSION['success'] = "Service deleted";
            } else {
                $_SESSION['error'] = "Failed to delete service";
            }
            
            header('Location: ' . base_url('index.php/provider/services'));
            exit;
        }
        
        // If not a POST request, redirect back
        header('Location: ' . base_url('index.php/provider/services'));
        exit;
    }
}
?>
