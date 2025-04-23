<?php
require_once "../models/Provider.php";
require_once "../core/Database.php";

session_start();
$db = Database::getInstance()->getConnection();
$providerModel = new Provider($db);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $service_id = $_POST['service_id'];
    $provider_id = $_SESSION['user_id'];

    if ($providerModel->deleteService($service_id, $provider_id)) {
        header("Location: /provider/services?success=Service removed");
    } else {
        header("Location: /provider/services?error=Failed to remove service");
    }
    exit;
}
?>