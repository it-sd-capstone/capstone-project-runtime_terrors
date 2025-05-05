<?php
header("Content-Type: application/json");
require_once "../models/Provider.php";
require_once "../models/Appointment.php";
require_once "../core/Database.php";

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['provider_id']) || !isset($data['date']) || !isset($data['time'])) {
    echo json_encode(['available' => false, 'error' => 'Missing required parameters']);
    exit;
}

$provider_id = $data['provider_id'];
$date = $data['date'];
$time = $data['time'];

$db = Database::getInstance()->getConnection();
$providerModel = new Provider($db);
$appointmentModel = new Appointment($db);

// Get provider availability
$schedules = $providerModel->getAvailability($provider_id);
// Get existing appointments
$appointments = $appointmentModel->getByProvider($provider_id);

// Check if the time is within any availability slot
$isWithinSchedule = false;
foreach ($schedules as $schedule) {
    if ($schedule['availability_date'] == $date) {
        $slotStart = strtotime($schedule['start_time']);
        $slotEnd = strtotime($schedule['end_time']);
        $selectedTime = strtotime($time);
        
        if ($selectedTime >= $slotStart && $selectedTime < $slotEnd) {
            $isWithinSchedule = true;
            break;
        }
    }
}

// If not in provider's schedule, not available
if (!$isWithinSchedule) {
    echo json_encode(['available' => false, 'reason' => 'not_in_schedule']);
    exit;
}

// Check if there's an existing appointment that conflicts
$isBooked = false;
foreach ($appointments as $appt) {
    if ($appt['appointment_date'] == $date && $appt['status'] != 'canceled') {
        $apptStart = strtotime($appt['start_time']);
        $apptEnd = strtotime($appt['end_time']);
        $selectedTime = strtotime($time);
        $selectedEndTime = strtotime($time) + (30 * 60); // Assuming 30-minute appointments
        
        // Check for overlap
        if (($selectedTime >= $apptStart && $selectedTime < $apptEnd) || 
            ($selectedEndTime > $apptStart && $selectedEndTime <= $apptEnd) ||
            ($selectedTime <= $apptStart && $selectedEndTime >= $apptEnd)) {
            $isBooked = true;
            break;
        }
    }
}

echo json_encode(['available' => !$isBooked]);
?>