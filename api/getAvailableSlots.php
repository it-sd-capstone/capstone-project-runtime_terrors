    <?php
    // --- BACKEND: api/getAvailableSlots.php ---
    header("Content-Type: application/json");
    require_once MODEL_PATH . '/Provider.php';
    require_once MODEL_PATH . '/Appointment.php';
    require_once __DIR__ . '/../config/database.php';

    // Enable error reporting for debugging
    error_log("API getAvailableSlots called");

    // Initialize response
    $calendarEvents = [];

    try {
        // Get parameters from request
        $provider_id = isset($_GET['provider_id']) ? intval($_GET['provider_id']) : null;
        $service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : null;
    
        // First establish database connection and create models
        $db = Database::getInstance()->getConnection();
        $providerModel = new Provider($db);
        $appointmentModel = new Appointment($db);
    
        error_log("Provider ID: " . $provider_id . ", Service ID: " . $service_id);
        error_log("Provider exists: " . ($providerModel->getProviderById($provider_id) ? "YES" : "NO"));
        error_log("Provider has services: " . (count($providerModel->getProviderServices($provider_id)) > 0 ? "YES" : "NO"));
    
        if (!$provider_id) {
            error_log("No provider_id specified, returning empty result");
            echo json_encode($calendarEvents);
            exit;
        }
    
        // Fetch availability slots - MODIFY THIS LINE TO PASS THE SERVICE_ID
        $schedules = $providerModel->getAvailability($provider_id, $service_id);
        error_log("Found " . count($schedules) . " availability slots for provider $provider_id" . 
                  ($service_id ? " and service $service_id" : ""));
    
        // If no schedules found, return empty result
        if (empty($schedules)) {
            error_log("No availability found for provider $provider_id" . ($service_id ? " and service $service_id" : ""));
            echo json_encode($calendarEvents);
            exit;
        }
    
        // Fetch appointments for conflict checking
        $appointments = $appointmentModel->getByProvider($provider_id);
        error_log("Found " . count($appointments) . " appointments for provider $provider_id");
    
        // Build a list of booked time ranges
        $bookedRanges = [];
        foreach ($appointments as $appt) {
            if ($appt['status'] === 'canceled' || $appt['status'] === 'no_show') continue;
            $bookedRanges[] = [
                'start' => strtotime($appt['appointment_date'] . ' ' . $appt['start_time']),
                'end'   => strtotime($appt['appointment_date'] . ' ' . $appt['end_time'])
            ];
        }
    
        // Process each availability slot
        foreach ($schedules as $schedule) {
            $slotStart = strtotime($schedule['availability_date'] . ' ' . $schedule['start_time']);
            $slotEnd = strtotime($schedule['availability_date'] . ' ' . $schedule['end_time']);
        
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
                "start" => $schedule['availability_date'] . "T" . $schedule['start_time'],
                "end" => $schedule['availability_date'] . "T" . $schedule['end_time'],
                "color" => $isBooked ? "#6c757d" : "#17a2b8",
                "extendedProps" => [
                    "type" => "availability",
                    "isBooked" => $isBooked
                ]
            ];
        }
    
        // To ensure proper date sorting of the final events, add this before returning:
        usort($calendarEvents, function($a, $b) {
            return strcmp($a['start'], $b['start']);
        });
    
        error_log("Returning " . count($calendarEvents) . " calendar events");
    
    } catch (Exception $e) {
        error_log("Error in getAvailableSlots: " . $e->getMessage());
    }

    // Return the result
    echo json_encode($calendarEvents);
    ?>
