<?php
require_once __DIR__ . "/../config/database.php"; // Ensure correct DB connection
require_once __DIR__ . "/../models/Appointment.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve user input
    $patient_id = $_POST['patient_id'];
    $provider_id = $_POST['provider_id'];
    $service_id = $_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $availability_id = $_POST['availability_id']; // Reference provider slot

    // Instantiate appointment model
    $appointment = new Appointment($db);

    // Check if the slot is already booked
    if ($appointment->isSlotBooked($availability_id)) {
        echo "Error: This time slot is already booked!";
        exit;
    }

    // Attempt to create the appointment
    if ($appointment->create($patient_id, $provider_id, $service_id, $availability_id, $appointment_date, $start_time, $end_time)) {
        header("Location: /appointments?success=1");
        exit;
    } else {
        echo "Error: Failed to book the appointment.";
    }
}
?>