<?php
require_once __DIR__ . "/../config/database.php"; // Ensure correct DB connection
require_once __DIR__ . "/../models/Appointment.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = $_SESSION['user_id']; // Ensure user is logged in
    $availability_id = $_POST['availability_id'];

    $appointmentModel = new Appointment($db);

    if ($appointmentModel->isSlotBooked($availability_id)) {
        echo "Error: This slot is already booked!";
        exit;
    }

    if ($appointmentModel->create($patient_id, $availability_id)) {
        header("Location: /appointments?success=1");
        exit;
    } else {
        echo "Error: Failed to book the appointment.";
    }
}
?>