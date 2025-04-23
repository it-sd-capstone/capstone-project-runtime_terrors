<?php
require_once '../models/Patient.php';
require_once '../core/Database.php';

session_start();
$db = Database::getInstance()->getConnection();
$patientModel = new Patient($db);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $patient_id = $_SESSION['user_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $age = trim($_POST['age']);

    if (!empty($first_name) && !empty($last_name) && !empty($phone) && !empty($age)) {
        $updated = $patientModel->updateProfile($patient_id, $first_name, $last_name, $phone, $age);

        if ($updated) {
            header("Location: /patient/profile?success=Profile updated successfully");
            exit;
        }
    }
    header("Location: /patient/profile?error=Failed to update profile");
    exit;
}
?>