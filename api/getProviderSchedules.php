<?php
header("Content-Type: application/json");
require_once "../models/Provider.php";
require_once __DIR__ . '/../config/database.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$db = Database::getInstance()->getConnection();
$providerModel = new Provider($db);
$provider_id = $_SESSION['user_id'];

try {
    $schedules = $providerModel->getAvailability($provider_id);
    $calendarEvents = [];
    
    foreach ($schedules as $schedule) {
        // Handle different possible data formats
        if (isset($schedule['start_time']) && isset($schedule['availability_date'])) {
            // Format with separate date and time fields
            $start = $schedule['availability_date'] . "T" . $schedule['start_time'];
            $end = $schedule['availability_date'] . "T" . $schedule['end_time'];
        } else if (isset($schedule['start_time']) && !isset($schedule['availability_date'])) {
            // Format with datetime strings that need parsing
            $startDateTime = new DateTime($schedule['start_time']);
            $endDateTime = new DateTime($schedule['end_time']);
            
            $start = $startDateTime->format('Y-m-d\TH:i:s');
            $end = $endDateTime->format('Y-m-d\TH:i:s');
        }
        
        // Only add valid events
        if (isset($start) && isset($end)) {
            $calendarEvents[] = [
                "id" => $schedule['id'] ?? $schedule['availability_id'] ?? null,
                "title" => "Available",
                "start" => $start,
                "end" => $end,
                "color" => "#17a2b8", // Info color (lighter blue)
                "extendedProps" => [
                    "type" => "availability",
                    "isRecurring" => isset($schedule['recurring_id']),
                    "recurring_id" => $schedule['recurring_id'] ?? null
                ]
            ];
        }
    }
    
    echo json_encode($calendarEvents);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}
?>
