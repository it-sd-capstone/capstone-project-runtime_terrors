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
        // Get parameters with validation
        $provider_id = isset($_GET['provider_id']) ? (int)$_GET['provider_id'] : null;
        $service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;
        $start_date = isset($_GET['start']) ? date('Y-m-d', strtotime($_GET['start'])) : date('Y-m-d');
        $end_date = isset($_GET['end']) ? date('Y-m-d', strtotime($_GET['end'])) : date('Y-m-d', strtotime('+14 days'));
        
        // Validate provider_id
        if (!$provider_id) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Provider ID is required']);
            return;
        }
        
        // Initialize response array
        $events = [];

        
        $provider = $this->providerModel->getProviderById($provider_id);
        
        if (!$provider) {
            error_log("ERROR: Provider with ID $provider_id not found in database");
            echo json_encode([]);
            exit;
        }
        
        try {
            // First, check if service-specific slots exist for this service
            $serviceSpecificExists = false;
            if ($service_id) {
                $checkQuery = "
                    SELECT COUNT(*) as count
                    FROM provider_availability 
                    WHERE provider_id = ? 
                    AND service_id = ?
                    AND is_available = 1
                ";
                $stmt = $this->db->prepare($checkQuery);
                $stmt->bind_param("ii", $provider_id, $service_id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $serviceSpecificExists = ($result['count'] > 0);
                
                error_log($serviceSpecificExists 
                    ? "Found service-specific availability for service_id: $service_id" 
                    : "No service-specific slots found for service_id: $service_id, using general availability");
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
            
            $stmt->execute();
            $oneTimeResult = $stmt->get_result();
            error_log("Found " . $oneTimeResult->num_rows . " one-time availability slots");
            
            // Process one-time availability slots
            while ($row = $oneTimeResult->fetch_assoc()) {
                // Get service duration (default 30 min)
                $duration = 30;
                if ($service_id) {
                    $serviceDuration = $this->getServiceDuration($service_id);
                    if ($serviceDuration) $duration = $serviceDuration;
                } else if ($row['service_id']) {
                    $serviceDuration = $this->getServiceDuration($row['service_id']);
                    if ($serviceDuration) $duration = $serviceDuration;
                }
                
                // Format datetime for calendar
                $startDateTime = $row['availability_date'] . 'T' . $row['start_time'];
                $endDateTime = $row['availability_date'] . 'T' . $row['end_time'];
                
                // Add the slot to events
                $events[] = [
                    'id' => 'slot_' . $row['availability_id'] . '_' . str_replace(':', '', $row['start_time']),
                    'title' => 'Available',
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'color' => '#28a745',
                    'extendedProps' => [
                        'availability_id' => $row['availability_id'],
                        'duration' => $duration,
                        'service_id' => $service_id ?: $row['service_id'],
                        'is_recurring' => $row['is_recurring']
                    ]
                ];
            }
            
            // 2. Get recurring availability and expand based on weekdays
            $recurringQuery = "
                SELECT
                    a.availability_id,
                    a.provider_id,
                    a.availability_date AS template_date,
                    a.start_time,
                    a.end_time,
                    a.is_available,
                    a.is_recurring,
                    a.weekdays,
                    a.service_id
                FROM
                    provider_availability a
                WHERE
                    a.provider_id = ?
                    AND a.is_available = 1
                    AND a.is_recurring = 1
            ";
            
            // Add service filter - if service-specific slots exist, only show those
            // Otherwise, show general availability (NULL) slots
            if ($service_id) {
                if ($serviceSpecificExists) {
                    $recurringQuery .= " AND a.service_id = ?";
                } else {
                    $recurringQuery .= " AND (a.service_id = ? OR a.service_id IS NULL)";
                }
            }
            
            $stmt = $this->db->prepare($recurringQuery);
            if ($service_id) {
                $stmt->bind_param("ii", $provider_id, $service_id);
            } else {
                $stmt->bind_param("i", $provider_id);
            }
            
            $stmt->execute();
            $recurringResult = $stmt->get_result();
            error_log("Found " . $recurringResult->num_rows . " recurring availability patterns");
            
            // Process recurring availability patterns
            while ($row = $recurringResult->fetch_assoc()) {
                // Get service duration (default 30 min)
                $duration = 30;
                if ($service_id) {
                    $serviceDuration = $this->getServiceDuration($service_id);
                    if ($serviceDuration) $duration = $serviceDuration;
                } else if ($row['service_id']) {
                    $serviceDuration = $this->getServiceDuration($row['service_id']);
                    if ($serviceDuration) $duration = $serviceDuration;
                }
                
                // Generate applicable dates within the range based on weekdays
                $weekdays = explode(',', $row['weekdays']);
                
                // Loop through each day in the date range
                $currentDate = new DateTime($start_date);
                $endDateObj = new DateTime($end_date);
                
                while ($currentDate <= $endDateObj) {
                    $dayOfWeek = $currentDate->format('w'); // 0 (Sunday) to 6 (Saturday)
                    
                    // Check if this day of week is in the recurring pattern
                    if (in_array($dayOfWeek, $weekdays)) {
                        $currentDateStr = $currentDate->format('Y-m-d');
                        
                        // Check if there's no appointment already booked for this slot
                        if (!$this->isSlotBooked($provider_id, $currentDateStr, $row['start_time'])) {
                            $startDateTime = $currentDateStr . 'T' . $row['start_time'];
                            $endDateTime = $currentDateStr . 'T' . $row['end_time'];
                            
                            // Create a unique ID for this recurring slot
                            $slotId = 'slot_' . $row['availability_id'] . '_' . $currentDateStr . '_' . str_replace(':', '', $row['start_time']);
                            
                            // Add the slot to events
                            $events[] = [
                                'id' => $slotId,
                                'title' => 'Available',
                                'start' => $startDateTime,
                                'end' => $endDateTime,
                                'color' => '#28a745',
                                'extendedProps' => [
                                    'availability_id' => $row['availability_id'],
                                    'duration' => $duration,
                                    'service_id' => $service_id ?: $row['service_id'],
                                    'is_recurring' => $row['is_recurring']
                                ]
                            ];
                        }
                    }
                    
                    // Move to next day
                    $currentDate->modify('+1 day');
                }
            }
            
            // Debug the result
            error_log("Returning " . count($events) . " total availability slots");
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode($events);
            
        } catch (Exception $e) {
            error_log("Error getting available slots: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to retrieve availability data']);
        }
    }

    /**
     * Helper method to add time slots to events array
     */
    private function addTimeSlots(&$events, $row, $startDateTime, $endDateTime, $duration) {
        // Convert to DateTime objects
        $start = new DateTime($startDateTime);
        $end = new DateTime($endDateTime);
        
        // Calculate time difference
        $interval = $start->diff($end);
        $totalMinutes = ($interval->h * 60) + $interval->i;
        
        // Skip if less than minimum duration
        if ($totalMinutes < $duration) {
            return;
        }
        
        // Create slots based on duration
        $currentStart = clone $start;
        while ($currentStart < $end) {
            $currentEnd = clone $currentStart;
            $currentEnd->modify("+{$duration} minutes");
            
            // Don't create partial slots at the end
            if ($currentEnd > $end) {
                break;
            }
            
            // Format dates for the slot
            $slotStart = $currentStart->format('Y-m-d\TH:i:s');
            $slotEnd = $currentEnd->format('Y-m-d\TH:i:s');
            $slotTime = $currentStart->format('His'); // Time component for ID
            
            // Create a unique ID for the slot (including date and time)
            $slotId = "slot_{$row['availability_id']}_{$slotTime}";
            if ($row['is_recurring']) {
                // For recurring slots, include the date in the ID to make it unique
                $slotId = "slot_{$row['availability_id']}_{$currentStart->format('Ymd')}_{$slotTime}";
            }
            
            // Add the event
            $events[] = [
                'id' => $slotId,
                'title' => 'Available',
                'start' => $slotStart,
                'end' => $slotEnd,
                'color' => '#28a745', // Green for available
                'extendedProps' => [
                    'availability_id' => $row['availability_id'],
                    'duration' => $duration,
                    'service_id' => $row['service_id'] ?: null,
                    'is_recurring' => $row['is_recurring']
                ]
            ];
            
            // Move to next slot
            $currentStart->modify("+{$duration} minutes");
        }
    }

    /**
     * Helper method to check if a slot is already booked
     */
    private function isSlotBooked($provider_id, $date, $start_time) {
        $query = "
            SELECT COUNT(*) as count
            FROM appointments
            WHERE provider_id = ?
            AND appointment_date = ?
            AND start_time = ?
            AND status != 'canceled'
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iss", $provider_id, $date, $start_time);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return ($result['count'] > 0);
    }

    /**
     * Helper method to get service duration
     */
    private function getServiceDuration($service_id) {
        $query = "SELECT duration FROM services WHERE service_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result ? (int)$result['duration'] : 30;
    }


    // Helper method to remove slots that conflict with existing appointments
    private function removeConflictingAppointments($events, $provider_id, $start_date, $end_date) {
        // Get all booked appointments in this date range
        $sql = "
            SELECT 
                appointment_id,
                appointment_date,
                start_time,
                end_time,
                status
            FROM 
                appointments
            WHERE 
                provider_id = ?
                AND appointment_date BETWEEN ? AND ?
                AND status != 'canceled'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iss", $provider_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $appointments = [];
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        
        error_log("Found " . count($appointments) . " booked appointments to check for conflicts");
        
        if (empty($appointments)) {
            return $events; // No appointments to check against
        }
        
        $filtered_events = [];
        
        // Check each event against each appointment for conflicts
        foreach ($events as $event) {
            $event_start = strtotime(substr($event['start'], 0, 10) . ' ' . substr($event['start'], 11));
            $event_end = strtotime(substr($event['end'], 0, 10) . ' ' . substr($event['end'], 11));
            $has_conflict = false;
            
            foreach ($appointments as $appointment) {
                $appt_start = strtotime($appointment['appointment_date'] . ' ' . $appointment['start_time']);
                $appt_end = strtotime($appointment['appointment_date'] . ' ' . $appointment['end_time']);
                
                // Check for overlap - exclude if slot overlaps with appointment
                if (!($event_end <= $appt_start || $event_start >= $appt_end)) {
                    $has_conflict = true;
                    break;
                }
            }
            
            if (!$has_conflict) {
                $filtered_events[] = $event;
            }
        }
        
        error_log("Removed " . (count($events) - count($filtered_events)) . " conflicting slots");
        
        return $filtered_events;
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
        $service_id = $data['service_id'] ?? null;
        $schedules = $this->providerModel->getAvailability($provider_id, $service_id);
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
    
    public function test() {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'API is working']);
        exit;
    }
}
?>