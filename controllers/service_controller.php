<?php
require_once MODEL_PATH . '/Services.php';

class ServiceController {
    protected $db;
    protected $serviceModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->db = get_db();
        $this->serviceModel = new Service($this->db);
    }

    /**
     * Process service actions (add, edit, delete)
     */
    public function processService() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            // Validate common input fields
            // Verify CSRF token
            if (!verify_csrf_token()) {
                return;
            }
            
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $duration = $_POST['duration'] ?? 30;
            $serviceId = $_POST['service_id'] ?? null;

            $errors = [];

            // Validate fields unless deleting
            if ($action !== 'delete') {
                if (empty($name)) $errors[] = "Service name is required";
                if (empty($description)) $errors[] = "Description is required";
                if (empty($price) || !is_numeric($price)) $errors[] = "Valid price is required";
            }

            // Perform action based on request
            if (empty($errors)) {
                switch ($action) {
                    case 'add':
                        $serviceData = compact('name', 'description', 'price', 'duration');
                        $result = $this->serviceModel->createService($serviceData);
                        break;

                    case 'edit':
                        if (!$serviceId) {
                            $_SESSION['errors'][] = "Service ID is missing";
                            break;
                        }
                        $serviceData = compact('name', 'description', 'price', 'duration', 'serviceId');
                        $result = $this->serviceModel->updateService($serviceData);
                        break;

                    case 'delete':
                        if (!$serviceId) {
                            $_SESSION['errors'][] = "Service ID is missing";
                            break;
                        }
                        $result = $this->serviceModel->deleteService($serviceId);
                        break;

                    default:
                        $_SESSION['errors'][] = "Invalid service action";
                        $result = false;
                }

                if ($result) {
                    $_SESSION['success'] = ucfirst($action) . " service successful";
                } else {
                    $_SESSION['errors'][] = "Error processing service operation";
                }
            } else {
                $_SESSION['errors'] = $errors;
            }
        }

        header('Location: ' . base_url('index.php/provider/services'));
        exit;
    }
}