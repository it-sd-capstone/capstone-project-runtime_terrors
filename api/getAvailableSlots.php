<?php
// --- BACKEND: api/getAvailableSlots.php ---
header("Content-Type: application/json");
require_once MODEL_PATH . '/Provider.php';
require_once MODEL_PATH . '/Appointment.php';
require_once __DIR__ . '/../config/database.php';

// Initialize response
$calendarEvents = [];

try {
    // Get parameters from request
    $provider_id = isset($_GET['provider_id']) ? intval($_GET['provider_id']) : null;
    $service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : null;
    $start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
    $end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+30 days'));
    
    // Validate input
    if (!$provider_id) {
        error_log("No provider_id specified, returning empty result");
        echo json_encode($calendarEvents);
        exit;
    }
    
    // First establish database connection and create models
    $db = Database::getInstance()->getConnection();
    $providerModel = new Provider($db);
    $appointmentModel = new Appointment($db);
    
    // Verify provider exists
    if (!$providerModel->getProviderById($provider_id)) {
        error_log("Provider ID $provider_id not found");
        echo json_encode(["error" => "Provider not found"]);
        exit;
    }
    
    // Fetch availability slots
    $schedules = $providerModel->getAvailability($provider_id, $service_id, $start_date, $end_date);
    error_log("Found " . count($schedules) . " availability slots for provider $provider_id" .
              ($service_id ? " and service $service_id" : ""));
    
    // If no schedules found, return empty result
    if (empty($schedules)) {
        error_log("No availability found for provider $provider_id" . ($service_id ? " and service $service_id" : ""));
        echo json_encode($calendarEvents);
        exit;
    }
    
    // Fetch appointments for conflict checking
    $appointments = $appointmentModel->getByProvider($provider_id, $start_date, $end_date);
    error_log("Found " . count($appointments) . " appointments for provider $provider_id");
    
    // Build a list of booked time ranges
    $bookedRanges = [];
    foreach ($appointments as $appt) {
        if (in_array($appt['status'], ['canceled', 'no_show'])) continue;
        
        // Handle different date/time formats
        if (isset($appt['appointment_datetime'])) {
            // If stored as a single datetime field
            $startDateTime = new DateTime($appt['appointment_datetime']);
            $duration = isset($appt['duration']) ? intval($appt['duration']) : 60; // Default 60 min
            $endDateTime = clone $startDateTime;
            $endDateTime->modify("+{$duration} minutes");
            
            $start = $startDateTime->getTimestamp();
            $end = $endDateTime->getTimestamp();
        } else {
            // If stored as separate date and time fields
            $start = strtotime($appt['appointment_date'] . ' ' . $appt['start_time']);
            $end = strtotime($appt['appointment_date'] . ' ' . $appt['end_time']);
        }
        
        $bookedRanges[] = [
            'start' => $start,
            'end' => $end
        ];
    }
    
    // Process each availability slot
    foreach ($schedules as $schedule) {
        // Handle different date/time formats
        if (isset($schedule['start_time']) && isset($schedule['availability_date'])) {
            // Format with separate date and time fields
            $slotStart = strtotime($schedule['availability_date'] . ' ' . $schedule['start_time']);
            $slotEnd = strtotime($schedule['availability_date'] . ' ' . $schedule['end_time']);
            
            $startFormatted = $schedule['availability_date'] . "T" . $schedule['start_time'];
            $endFormatted = $schedule['availability_date'] . "T" . $schedule['end_time'];
        } else if (isset($schedule['start_time']) && !isset($schedule['availability_date'])) {
            // Format with datetime strings
            $startDateTime = new DateTime($schedule['start_time']);
            $endDateTime = new DateTime($schedule['end_time']);
            
            $slotStart = $startDateTime->getTimestamp();
            $slotEnd = $endDateTime->getTimestamp();
            
            $startFormatted = $startDateTime->format('Y-m-d\TH:i:s');
            $endFormatted = $endDateTime->format('Y-m-d\TH:i:s');
        }
        
        // Skip past slots
        if ($slotEnd < time()) {
            continue;
        }
        
        // Check if slot is booked
        $isBooked = false;
        foreach ($bookedRanges as $range) {
            if ($range['start'] < $slotEnd && $range['end'] > $slotStart) {
                $isBooked = true;
                break;
            }
        }
        
        // Add event to calendar
        $calendarEvents[] = [
            "id" => $schedule['availability_id'] ?? $schedule['id'] ?? null,
            "title" => $isBooked ? "Booked" : "Available",
            "start" => $startFormatted,
            "end" => $endFormatted,
            "color" => $isBooked ? "#6c757d" : "#17a2b8",
            "extendedProps" => [
                "type" => "availability",
                "isBooked" => $isBooked
            ]
        ];
    }
    
    // To ensure proper date sorting of the final events
    usort($calendarEvents, function($a, $b) {
        return strcmp($a['start'], $b['start']);
    });
    
    error_log("Returning " . count($calendarEvents) . " calendar events");
} catch (Exception $e) {
    error_log("Error in getAvailableSlots: " . $e->getMessage());
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
    exit;
}

// Return the result
echo json_encode($calendarEvents);
?>
