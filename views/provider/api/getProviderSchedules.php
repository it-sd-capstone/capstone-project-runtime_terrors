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
        "title" => "Available",
        "start" => $schedule['availability_date'] . "T" . $schedule['start_time'],
        "end" => $schedule['availability_date'] . "T" . $schedule['end_time'],
        "color" => "green"
    ];
}

echo json_encode($calendarEvents);
?>