<?php
session_start();
require_once '../config/database.php';

// Validate session
if (!isset($_SESSION['provider_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: Provider not logged in."]);
    exit;
}

$provider_id = $_SESSION['provider_id'];

// Validate and sanitize input
$start_time = $_POST['start_time'] ?? null;
$end_time = $_POST['end_time'] ?? null;
$repeat = isset($_POST['repeat']) ? filter_var($_POST['repeat'], FILTER_VALIDATE_BOOLEAN) : false;
$repeat_until = $_POST['repeat_until'] ?? null;

// Input validation
if (!$start_time || !$end_time) {
    http_response_code(400);
    echo json_encode(["error" => "Missing start or end time."]);
    exit;
}

if ($repeat && !$repeat_until) {
    http_response_code(400);
    echo json_encode(["error" => "Missing repeat_until date for recurring availability."]);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Parse dates
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    
    // Validate time range
    if ($start >= $end) {
        http_response_code(400);
        echo json_encode(["error" => "End time must be after start time."]);
        exit;
    }
    
    // Standardize times to 15 or 30-minute intervals if needed
    // Uncomment this if you want to enforce standard time slots
    /*
    $minutes = $start->format('i');
    if ($minutes % 15 != 0) {
        $start->setTime($start->format('H'), floor($minutes / 15) * 15);
    }
    
    $minutes = $end->format('i');
    if ($minutes % 15 != 0) {
        $end->setTime($end->format('H'), ceil($minutes / 15) * 15);
    }
    */
    
    // If recurring, set the repeat end date
    $until = $repeat ? new DateTime($repeat_until) : null;
    
    // Track successful insertions
    $insertedSlots = 0;
    $errors = [];
    
    do {
        // Check for overlapping availability
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM availability 
            WHERE provider_id = ? 
            AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))
        ");
        
        $checkStmt->execute([
            $provider_id,
            $start->format('Y-m-d H:i:s'),
            $start->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s')
        ]);
        
        if ($checkStmt->fetchColumn() > 0) {
            $errors[] = "Overlapping availability on " . $start->format('Y-m-d');
        } else {
            // Insert availability
            $stmt = $pdo->prepare("
                INSERT INTO availability (provider_id, start_time, end_time) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $provider_id,
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s')
            ]);
            
            $insertedSlots++;
        }
        
        // Prepare next week's time
        if ($repeat) {
            $start->modify('+1 week');
            $end->modify('+1 week');
        }
        
    } while ($repeat && $start <= $until);
    
    // Return response
    if ($insertedSlots > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Added $insertedSlots availability slot(s)" . ($repeat ? " with recurrence" : ""),
            "errors" => $errors
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Failed to add availability",
            "errors" => $errors
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}
?>
