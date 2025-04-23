<?php
require_once '../models/Provider.php';
require_once '../core/Database.php';

session_start();
$db = Database::getInstance()->getConnection();
$providerModel = new Provider($db);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $provider_id = $_SESSION['user_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $specialty = trim($_POST['specialty']);
    $bio = trim($_POST['bio']);

    // Profile Picture Upload Handling
    $profilePicture = null;
    if (!empty($_FILES["profile_picture"]["name"])) {
        $targetDir = "../uploads/";
        $profilePicture = $targetDir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profilePicture);
    }

    $updated = $providerModel->updateProfile($provider_id, $first_name, $last_name, $specialty, $bio, $profilePicture);

    if ($updated) {
        header("Location: /provider/profile?success=Profile updated successfully");
        exit;
    } else {
        header("Location: /provider/profile?error=Failed to update profile");
        exit;
    }
}
?>