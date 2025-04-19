<?php
require_once MODEL_PATH . '/Provider.php';

class ProviderController {
    private $db;
    private $providerModel;

    public function __construct() {
        $this->db = new PDO("mysql:host=localhost;dbname=appointment_system", "root", "");
        $this->providerModel = new Provider($this->db);
    }

    public function upload_availability() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->providerModel->addAvailability($_POST['available_date'], $_POST['start_time'], $_POST['end_time']);
            header("Location: /index.php?page=provider");
            exit;
        }
    }
}
?>