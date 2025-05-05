<?php
header("Content-Type: application/json");

require_once "../models/Provider.php";
require_once "../core/Database.php";

session_start();
$db = Database::getInstance()->getConnection();
$providerModel = new Provider($db);

$provider_id = $_SESSION['user_id'];
$schedules = $providerModel->getAvailability($provider_id);

$calendarEvents = [];
foreach ($schedules as $schedule) {
    $calendarEvents[] = [
        "id" => $schedule['id'] ?? $schedule['availability_id'] ?? null,
        "title" => "Available",
        "start" => $schedule['availability_date'] . "T" . $schedule['start_time'],
        "end" => $schedule['availability_date'] . "T" . $schedule['end_time'],
        "color" => "#17a2b8", // Info color (lighter blue) to distinguish from appointments
        "extendedProps" => [
            "type" => "availability",
            "isRecurring" => isset($schedule['recurring_id']),
            "recurring_id" => $schedule['recurring_id'] ?? null
        ]
    ];
}

echo json_encode($calendarEvents);
?>