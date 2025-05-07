<?php
require_once MODEL_PATH . '/ProviderServices.php';
require_once MODEL_PATH . '/Service.php';

class ProviderServicesController {
    protected $db;
    protected $providerServicesModel;
    protected $serviceModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = get_db();
        $this->providerServicesModel = new ProviderServices($this->db);
        $this->serviceModel = new Service($this->db);
    }

    /**
     * List all services offered by the current provider
     */
    public function services() {
        $provider_id = $_SESSION['user_id'];
        $services = $this->providerServicesModel->getServicesByProvider($provider_id);

        // For the add form, get all global services not already linked
        $all_services = $this->serviceModel->getAllServices();
        $offered_service_ids = array_column($services, 'service_id');
        $available_services = array_filter($all_services, function($s) use ($offered_service_ids) {
            return !in_array($s['service_id'], $offered_service_ids);
        });

        include VIEW_PATH . '/provider/services.php';
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

            $success = $this->providerServicesModel->addProviderService($provider_id, $service_id, $custom_duration, $custom_notes);
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

            $success = $this->providerServicesModel->editProviderService($provider_service_id, $custom_duration, $custom_notes);
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
            $success = $this->providerServicesModel->deleteProviderService($provider_service_id);
            $_SESSION[$success ? 'success' : 'error'] = $success ? "Service removed from your offerings." : "Failed to remove service.";
            header('Location: ' . base_url('index.php/provider/services'));
            exit;
        }
    }
}
?>