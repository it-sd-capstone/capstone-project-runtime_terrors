<?php
class ApiController {
    private $db;
    private $providerModel;
    private $appointmentModel;
    
    public function __construct() {
        require_once MODEL_PATH . '/Provider.php';
        require_once MODEL_PATH . '/Appointment.php';
        
        $this->db = get_db();
        $this->providerModel = new Provider($this->db);
        $this->appointmentModel = new Appointment($this->db);
    }
    
    public function getAvailableSlots() {
        header("Content-Type: application/json");
        
        $date = $_GET['date'] ?? null;
        $provider_id = $_GET['provider_id'] ?? null;
        $service_id = $_GET['service_id'] ?? null;
        $appointment_id = $_GET['appointment_id'] ?? null;
        
        // Debug output to check what's being received
        error_log("API getAvailableSlots called with date: $date, provider: $provider_id, service: $service_id");
        
        if (!$provider_id) {
            error_log("No provider_id specified in request");
            echo json_encode([]);
            exit;
        }
        
        if (!$date) {
            error_log("No date specified in request");
            echo json_encode([]);
            exit;
        }
        
        
        $provider = $this->providerModel->getProviderById($provider_id);
        
        if (!$provider) {
            error_log("ERROR: Provider with ID $provider_id not found in database");
            echo json_encode([]);
            exit;
        }
        
        error_log("Found provider: " . $provider['first_name'] . " " . $provider['last_name']);
        
        // Get service duration if service_id is provided
        $service_duration = 30; // Default duration in minutes
        if ($service_id) {
            require_once MODEL_PATH . '/services.php';
            $serviceModel = new Service($db);
            $service = $serviceModel->getServiceById($service_id);
            if ($service && isset($service['duration'])) {
                $service_duration = (int)$service['duration'];
                error_log("Using service duration: $service_duration minutes for service ID: $service_id");
            }
        }
        
        // Get provider availability
        $schedules = $this->providerModel->getAvailability($provider_id);
        
        
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        error_log("Found " . count($appointments) . " appointments for provider $provider_id");
        
        $calendarEvents = [];
        $processedCount = 0;
        $skippedCount = 0;
        $filteredDate = date('Y-m-d', strtotime($date)); // Normalize date format
        
        foreach ($schedules as $index => $schedule) {
            // Filter by the requested date
            if (isset($schedule['availability_date']) && $schedule['availability_date'] != $filteredDate) {
                // Skip slots that don't match our requested date
                continue;
            }
            
            // Validate each required field is present
            $requiredFields = ['availability_date', 'start_time', 'end_time'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (!isset($schedule[$field]) || empty($schedule[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                error_log("Schedule[$index] missing required fields: " . implode(', ', $missingFields));
                error_log("Schedule[$index] data: " . json_encode($schedule));
                $skippedCount++;
                continue;
            }
            
            // Check if date and time are valid
            if (!strtotime($schedule['availability_date'] . ' ' . $schedule['start_time'])) {
                error_log("Schedule[$index] has invalid date/time format: " .
                         $schedule['availability_date'] . ' ' . $schedule['start_time']);
                $skippedCount++;
                continue;
            }
            
            // Check if this slot overlaps with any existing appointments
            $isBooked = false;
            $start = strtotime($schedule['availability_date'] . ' ' . $schedule['start_time']);
            $end = strtotime($schedule['availability_date'] . ' ' . $schedule['end_time']);
            
            if (!$start || !$end) {
                error_log("Failed to convert date/time to timestamp for schedule[$index]: " .
                         $schedule['availability_date'] . ' ' . $schedule['start_time'] . '-' . $schedule['end_time']);
                $skippedCount++;
                continue;
            }
            
            // Skip the appointment we're trying to reschedule
            $apptToIgnore = $appointment_id ? (int)$appointment_id : null;
            
            foreach ($appointments as $apptIndex => $appt) {
                if ($appt['status'] === 'canceled') continue;
                
                // Skip the appointment we're trying to reschedule
                if ($apptToIgnore && isset($appt['appointment_id']) && (int)$appt['appointment_id'] === $apptToIgnore) {
                    error_log("Ignoring appointment ID $apptToIgnore for rescheduling");
                    continue;
                }
                
                if (!isset($appt['appointment_date']) || !isset($appt['start_time']) || !isset($appt['end_time'])) {
                    error_log("Appointment[$apptIndex] missing required date/time fields");
                    continue;
                }
                
                $apptStart = strtotime($appt['appointment_date'] . ' ' . $appt['start_time']);
                $apptEnd = strtotime($appt['appointment_date'] . ' ' . $appt['end_time']);
                
                if (!$apptStart || !$apptEnd) {
                    error_log("Failed to convert date/time for appointment[$apptIndex]");
                    continue;
                }
                
                // If appointment overlaps this availability slot
                if (($apptStart >= $start && $apptStart < $end) ||
                    ($apptEnd > $start && $apptEnd <= $end) ||
                    ($apptStart <= $start && $apptEnd >= $end)) {
                    $isBooked = true;
                    error_log("Schedule[$index] overlaps with appointment ID: " . ($appt['appointment_id'] ?? 'unknown'));
                    break;
                }
            }
            
            // Only add available slots (not booked)
            if (!$isBooked) {
                // Format start and end times to include seconds for proper ISO format
                $startTime = date('H:i:s', strtotime($schedule['start_time']));
                $endTime = date('H:i:s', strtotime($schedule['end_time']));
                
                $calendarEvents[] = [
                    "id" => $schedule['id'] ?? $schedule['availability_id'] ?? $index,
                    "title" => "Available" . (isset($schedule['recurring_id']) ? " (Recurring)" : ""),
                    "start" => $schedule['availability_date'] . "T" . $startTime,
                    "end" => $schedule['availability_date'] . "T" . $endTime,
                    "color" => "#28a745", // Success/green for available slots
                    "extendedProps" => [
                        "type" => "availability",
                        "isRecurring" => isset($schedule['recurring_id']),
                        "recurring_id" => $schedule['recurring_id'] ?? null
                    ]
                ];
                $processedCount++;
            } else {
                $skippedCount++;
            }
        }
        
        error_log("Processed $processedCount available slots, skipped $skippedCount slots (booked or invalid)");
        error_log("Final calendar events count: " . count($calendarEvents));
        
        echo json_encode($calendarEvents);
    }
    
    
    public function checkSlotAvailability() {
        header("Content-Type: application/json");
        
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
        
        // Get provider availability
        $schedules = $this->providerModel->getAvailability($provider_id);
        // Get existing appointments
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        
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
    }
}
?>