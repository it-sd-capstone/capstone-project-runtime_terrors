<?php
header("Content-Type: application/json");
require_once "../models/Provider.php";
require_once "../models/Appointment.php";
require_once "../core/Database.php";

$provider_id = $_GET['provider_id'] ?? null;

if (!$provider_id) {
    echo json_encode([]);
    exit;
}

$db = Database::getInstance()->getConnection();
$providerModel = new Provider($db);
$appointmentModel = new Appointment($db);

// Get provider availability - ensure this includes BOTH regular and recurring availability
$schedules = $providerModel->getAvailability($provider_id, true); // Add a parameter to include recurring slots
$appointments = $appointmentModel->getByProvider($provider_id);

$calendarEvents = [];

// Debug
error_log("Found " . count($schedules) . " availability slots for provider $provider_id");

foreach ($schedules as $schedule) {
    // Check if this slot overlaps with any existing appointments
    $isBooked = false;
    $start = strtotime($schedule['availability_date'] . ' ' . $schedule['start_time']);
    $end = strtotime($schedule['availability_date'] . ' ' . $schedule['end_time']);
    
    foreach ($appointments as $appt) {
        if ($appt['status'] === 'canceled') continue;
        
        $apptStart = strtotime($appt['appointment_date'] . ' ' . $appt['start_time']);
        $apptEnd = strtotime($appt['appointment_date'] . ' ' . $appt['end_time']);
        
        // If appointment overlaps this availability slot
        if (($apptStart >= $start && $apptStart < $end) || 
            ($apptEnd > $start && $apptEnd <= $end) ||
            ($apptStart <= $start && $apptEnd >= $end)) {
            $isBooked = true;
            break;
        }
    }
    
    // Only add available slots (not booked)
    if (!$isBooked) {
        $calendarEvents[] = [
            "id" => $schedule['id'] ?? $schedule['availability_id'] ?? null,
            "title" => "Available" . (isset($schedule['recurring_id']) ? " (Recurring)" : ""),
            "start" => $schedule['availability_date'] . "T" . $schedule['start_time'],
            "end" => $schedule['availability_date'] . "T" . $schedule['end_time'],
            "color" => "#28a745", // Success/green for available slots
            "extendedProps" => [
                "type" => "availability",
                "isRecurring" => isset($schedule['recurring_id']),
                "recurring_id" => $schedule['recurring_id'] ?? null
            ]
        ];
    }
}

echo json_encode($calendarEvents);
?>