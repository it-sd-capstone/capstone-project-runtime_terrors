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
        
        $provider_id = $_GET['provider_id'] ?? null;
        error_log("===== DEBUG: getAvailableSlots for provider_id: $provider_id =====");
        
        if (!$provider_id) {
            error_log("No provider_id specified in request");
            echo json_encode([]);
            exit;
        }
        
        // Verify provider exists
        $db = get_db();
        $stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE user_id = ? AND role = 'provider'");
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $provider = $result->fetch_assoc();
        
        if (!$provider) {
            error_log("ERROR: Provider with ID $provider_id not found in database");
            echo json_encode([]);
            exit;
        }
        
        error_log("Found provider: " . $provider['first_name'] . " " . $provider['last_name']);
        
        // Get provider availability
        $schedules = $this->providerModel->getAvailability($provider_id);
        
        // Debug availability data
        error_log("Found " . count($schedules) . " availability slots for provider $provider_id");
        if (count($schedules) === 0) {
            // Check if the method exists
            $reflection = new ReflectionMethod($this->providerModel, 'getAvailability');
            error_log("Method exists: " . $reflection->getName());
            
            // Check direct database query
            $sql = "SELECT * FROM provider_availability WHERE provider_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $direct_slots = $result->fetch_all(MYSQLI_ASSOC);
            
            error_log("Direct DB query found " . count($direct_slots) . " availability records");
            foreach ($direct_slots as $index => $slot) {
                error_log("Slot[$index]: ID: " . ($slot['availability_id'] ?? 'missing') . 
                         ", Date: " . ($slot['availability_date'] ?? 'N/A') . 
                         ", Time: " . ($slot['start_time'] ?? 'missing') . "-" . ($slot['end_time'] ?? 'missing') .
                         ", Recurring: " . ($slot['is_recurring'] ? 'Yes' : 'No'));
            }
        } else {
            // Debug the first few schedule entries
            for ($i = 0; $i < min(3, count($schedules)); $i++) {
                $schedule = $schedules[$i];
                error_log("Schedule[$i]: " . json_encode($schedule));
            }
        }
        
        $appointments = $this->appointmentModel->getByProvider($provider_id);
        error_log("Found " . count($appointments) . " appointments for provider $provider_id");
        
        $calendarEvents = [];
        $processedCount = 0;
        $skippedCount = 0;
        
        foreach ($schedules as $index => $schedule) {
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
            
            foreach ($appointments as $apptIndex => $appt) {
                if ($appt['status'] === 'canceled') continue;
                
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
                $calendarEvents[] = [
                    "id" => $schedule['id'] ?? $schedule['availability_id'] ?? $index,
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