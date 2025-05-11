<?php
require_once MODEL_PATH . '/Services.php';

class ServiceController {
    protected $db;
    protected $serviceModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = get_db();
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
     * Process service creation
     */
    public function processService() {
        error_log("Session user role: " . ($_SESSION['user_role'] ?? 'not set'));
        error_log("Session user ID: " . ($_SESSION['user_id'] ?? 'not set'));
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            // Validate common input fields
            // Verify CSRF token
            if (!verify_csrf_token()) {
                return;
            }
            
            $name = $_POST['service_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $duration = $_POST['duration'] ?? 30;
            $serviceId = $_POST['service_id'] ?? null;
            $errors = [];

            // Perform action based on request
            if (empty($errors)) {
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
            }
        }
        
        // Redirect back to services page
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
            $provider_service_id = intval($_POST['provider_service_id']);
            $success = $this->serviceModel->deleteService($provider_service_id);
            $_SESSION[$success ? 'success' : 'error'] = $success ? "Service removed from your offerings." : "Failed to remove service.";
            header('Location: ' . base_url('index.php/provider/services'));
            exit;
        }
    }
}
?>
