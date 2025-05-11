<?php
require_once __DIR__ . "/../config/database.php"; // Ensure correct DB connection
require_once __DIR__ . "/../models/Appointment.php";

// Prevent direct access to view files
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login
        header("Location: " . base_url('index.php/auth/login?redirect=appointments'));
        exit;
    }
    
    $patient_id = $_SESSION['user_id'];
    $availability_id = $_POST['availability_id'];
    
    $appointmentModel = new Appointment($db);
    
    if ($appointmentModel->isSlotBooked($availability_id)) {
        $_SESSION['error'] = "This slot is already booked!";
        header("Location: " . base_url('index.php/appointments'));
        exit;
    }
    
    if ($appointmentModel->create($patient_id, $availability_id)) {
        // Get the booked availability details
        $availability = $providerModel->getAvailabilityById($availability_id);
        
        // Mark conflicting slots as unavailable
        $conflictingAvailabilities = $providerModel->getConflictingAvailabilities(
            $availability['provider_id'],
            $availability['availability_date'],
            $availability['start_time'],
            $availability['end_time'],
            $availability['service_id']
        );
        
        foreach ($conflictingAvailabilities as $conflictingAvailability) {
            $providerModel->updateAvailabilityStatus(
                $conflictingAvailability['id'],
                0 // Set as unavailable
            );
        }
        
        $_SESSION['success'] = "Appointment booked successfully!";
        header("Location: " . base_url('index.php/appointments'));
        exit;
    } else {
        $_SESSION['error'] = "Failed to book the appointment.";
        header("Location: " . base_url('index.php/appointments'));
        exit;
    }
}
?>