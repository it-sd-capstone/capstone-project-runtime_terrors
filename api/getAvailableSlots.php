
<?php
// --- BACKEND: /api/getAvailableSlots.php ---
header("Content-Type: application/json");
require_once "../models/Provider.php";
require_once "../models/Appointment.php";
require_once __DIR__ . '/../config/database.php';

session_start();
$provider_id = $_GET['provider_id'] ?? ($_SESSION['user_id'] ?? null);

if (!$provider_id) {
    echo json_encode([]);
    exit;
}

$db = Database::getInstance()->getConnection();
$providerModel = new Provider($db);
$appointmentModel = new Appointment($db);

// --- Fetch all availability slots (future, including recurring if supported) ---
$schedules = $providerModel->getAvailability($provider_id, true); // true = include recurring

// --- Fetch all appointments for this provider ---
$appointments = $appointmentModel->getByProvider($provider_id);

// --- Build a list of booked time ranges for overlap checking ---
$bookedRanges = [];
foreach ($appointments as $appt) {
    if ($appt['status'] === 'canceled' || $appt['status'] === 'no_show') continue;
    $bookedRanges[] = [
        'start' => strtotime($appt['appointment_date'] . ' ' . $appt['start_time']),
        'end'   => strtotime($appt['appointment_date'] . ' ' . $appt['end_time'])
    ];
}

// --- Fetch unavailability slots if you want to show them on the calendar ---
$unavailability = [];
try {
    $stmt = $db->prepare("SELECT * FROM provider_unavailability WHERE provider_id = ?");
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $unavailability[] = $row;
    }
} catch (Exception $e) {
    // If table doesn't exist or error, just skip
    $unavailability = [];
}

// --- Build calendar events ---
$calendarEvents = [];

// --- Add availability events ---
foreach ($schedules as $schedule) {
    $slotStart = strtotime($schedule['availability_date'] . ' ' . $schedule['start_time']);
    $slotEnd   = strtotime($schedule['availability_date'] . ' ' . $schedule['end_time']);
    $is_available = isset($schedule['is_available']) ? intval($schedule['is_available']) : 1;

    // Check if slot is booked (overlaps with any appointment)
    $isBooked = false;
    foreach ($bookedRanges as $range) {
        if ($range['start'] < $slotEnd && $range['end'] > $slotStart) {
            $isBooked = true;
            break;
        }
    }

    $calendarEvents[] = [
        "id" => $schedule['id'] ?? $schedule['availability_id'] ?? null,
        "title" => $isBooked ? "Booked" : ($is_available ? "Available" : "Unavailable"),
        "start" => $schedule['availability_date'] . "T" . $schedule['start_time'],
        "end" => $schedule['availability_date'] . "T" . $schedule['end_time'],
        "color" => $isBooked ? "#6c757d" : ($is_available ? "#17a2b8" : "#dc3545"),
        "extendedProps" => [
            "type" => "availability",
            "is_available" => $is_available,
            "isBooked" => $isBooked
        ]
    ];
}

// --- Add unavailability events ---
foreach ($unavailability as $ua) {
    $calendarEvents[] = [
        "id" => $ua['unavailability_id'],
        "title" => $ua['reason'] ? "Unavailable: " . $ua['reason'] : "Unavailable",
        "start" => $ua['unavailable_date'] . "T" . $ua['start_time'],
        "end" => $ua['unavailable_date'] . "T" . $ua['end_time'],
        "color" => "#dc3545",
        "extendedProps" => [
            "type" => "unavailability",
            "is_available" => 0,
            "isBooked" => false
        ]
    ];
}

echo json_encode($calendarEvents);
?>
