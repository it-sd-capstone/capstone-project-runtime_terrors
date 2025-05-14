<?php
header("Content-Type: application/json");
require_once "../models/Appointment.php";
require_once __DIR__ . '/../config/database.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

try {
    // Get parameters
    $provider_id = $_SESSION['user_id'];
    $start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-30 days'));
    $end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+90 days'));
    
    $db = Database::getInstance()->getConnection();
    $appointmentModel = new Appointment($db);
    
    // Get appointments with date range
    $appointments = $appointmentModel->getByProvider($provider_id, $start_date, $end_date);
    $calendarEvents = [];
    
    foreach ($appointments as $appointment) {
        // Handle different possible data formats
        if (isset($appointment['start_time']) && isset($appointment['appointment_date'])) {
            // Format with separate date and time fields
            $start = $appointment['appointment_date'] . "T" . $appointment['start_time'];
            $end = $appointment['appointment_date'] . "T" . $appointment['end_time'];
        } else if (isset($appointment['appointment_datetime'])) {
            // Format with a single datetime field
            $startDateTime = new DateTime($appointment['appointment_datetime']);
            
            // Calculate end time based on duration if available
            if (isset($appointment['duration'])) {
                $endDateTime = clone $startDateTime;
                $endDateTime->modify("+{$appointment['duration']} minutes");
            } else {
                // Default to 1 hour if no duration specified
                $endDateTime = clone $startDateTime;
                $endDateTime->modify("+60 minutes");
            }
            
            $start = $startDateTime->format('Y-m-d\TH:i:s');
            $end = $endDateTime->format('Y-m-d\TH:i:s');
        }
        
        // Determine color based on status
        $statusColor = match($appointment['status']) {
            'confirmed' => '#28a745',           // success/green
            'scheduled', 'pending' => '#ffc107', // warning/yellow
            'canceled' => '#dc3545',            // danger/red
            'completed' => '#0dcaf0',           // info/blue
            'no_show' => '#6c757d',             // secondary/gray
            default => '#6c757d'                // secondary/gray
        };
        
        // Get patient name - handle different field names
        $patientName = $appointment['patient_name'] ?? 
                      ($appointment['first_name'] && $appointment['last_name'] ? 
                       $appointment['first_name'] . ' ' . $appointment['last_name'] : 
                       'Patient #' . ($appointment['patient_id'] ?? 'Unknown'));
        
        // Get service name - handle different field names
        $serviceName = $appointment['service_name'] ?? 
                      ($appointment['service'] ?? 
                       ($appointment['service_id'] ? 'Service #' . $appointment['service_id'] : 'Unknown Service'));
        
        $calendarEvents[] = [
            "id" => $appointment['id'] ?? $appointment['appointment_id'] ?? null,
            "title" => $patientName . " (" . $serviceName . ")",
            "start" => $start,
            "end" => $end,
            "color" => $statusColor,
            "description" => "Status: " . $appointment['status'],
            "extendedProps" => [
                "type" => "appointment",
                "patient" => $patientName,
                "service" => $serviceName,
                "status" => $appointment['status'],
                "notes" => $appointment['notes'] ?? '',
                "patient_id" => $appointment['patient_id'] ?? null,
                "service_id" => $appointment['service_id'] ?? null
            ]
        ];
    }
    
    echo json_encode($calendarEvents);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}
?>
