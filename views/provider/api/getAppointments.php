<?php
header("Content-Type: application/json");

require_once "../models/Appointment.php";
require_once "../core/Database.php";

session_start();
$db = Database::getInstance()->getConnection();
$appointmentModel = new Appointment($db);

$provider_id = $_SESSION['user_id'];

$appointments = $appointmentModel->getByProvider($provider_id);

$calendarEvents = [];
foreach ($appointments as $appointment) {
    $calendarEvents[] = [
        "title" => $appointment['patient_name'] . " (" . $appointment['service_name'] . ")",
        "start" => $appointment['appointment_date'] . "T" . $appointment['start_time'],
        "end" => $appointment['appointment_date'] . "T" . $appointment['end_time'],
        "color" => ($appointment['status'] == 'confirmed') ? "blue" : "gray",
        "description" => "Status: " . $appointment['status']
    ];
}

echo json_encode($calendarEvents);
?>