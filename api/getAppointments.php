<?php
header("Content-Type: application/json");

require_once "../models/Appointment.php";
require_once "../core/Database.php";

$db = Database::getInstance()->getConnection();
$appointmentModel = new Appointment($db);

// Fetch all appointments
$appointments = $appointmentModel->getAllAppointments();

// Convert to JSON format for FullCalendar.js
$calendarEvents = [];
foreach ($appointments as $appointment) {
    $calendarEvents[] = [
        "title" => $appointment['status'],
        "start" => $appointment['appointment_date'] . "T" . $appointment['start_time'],
        "end" => $appointment['appointment_date'] . "T" . $appointment['end_time'],
        "color" => $appointment['status'] === "scheduled" ? "blue" : ($appointment['status'] === "completed" ? "green" : "red")
    ];
}

echo json_encode($calendarEvents);
?>