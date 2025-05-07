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