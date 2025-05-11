<?php
header("Content-Type: application/json");

require_once "../models/Appointment.php";
require_once __DIR__ . '/../config/database.php';

session_start();
$db = Database::getInstance()->getConnection();
$appointmentModel = new Appointment($db);

$provider_id = $_SESSION['user_id'];

$appointments = $appointmentModel->getByProvider($provider_id);

$calendarEvents = [];
foreach ($appointments as $appointment) {
    // Determine color based on status
    $statusColor = match($appointment['status']) {
        'confirmed' => '#28a745',           // success/green
        'scheduled', 'pending' => '#ffc107', // warning/yellow
        'canceled' => '#dc3545',            // danger/red
        'completed' => '#0dcaf0',           // info/blue
        'no_show' => '#6c757d',             // secondary/gray
        default => '#6c757d'                // secondary/gray
    };
    
    $calendarEvents[] = [
        "id" => $appointment['id'] ?? $appointment['appointment_id'] ?? null,
        "title" => $appointment['patient_name'] . " (" . $appointment['service_name'] . ")",
        "start" => $appointment['appointment_date'] . "T" . $appointment['start_time'],
        "end" => $appointment['appointment_date'] . "T" . $appointment['end_time'],
        "color" => $statusColor,
        "description" => "Status: " . $appointment['status'],
        "extendedProps" => [
            "type" => "appointment",
            "patient" => $appointment['patient_name'],
            "service" => $appointment['service_name'],
            "status" => $appointment['status']
        ]
    ];
}

echo json_encode($calendarEvents);
?>