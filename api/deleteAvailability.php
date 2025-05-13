<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$type = $data['type'] ?? null;

// Validate input
if (!$id || !$type) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing ID or type']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();
    
    // Get the current user's ID and role
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    if ($type === 'availability') {
        // First check if this availability belongs to the provider
        if ($role === 'provider') {
            $checkStmt = $db->prepare("SELECT provider_id FROM provider_availability WHERE availability_id = ?");
            $checkStmt->execute([$id]);
            $owner = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$owner || $owner['provider_id'] != $user_id) {
                $db->rollBack();
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'You do not have permission to delete this availability']);
                exit;
            }
        }
        
        // Check if there are any appointments for this availability
        $apptStmt = $db->prepare("
            SELECT COUNT(*) FROM appointments 
            WHERE availability_id = ? AND status NOT IN ('canceled', 'no_show')
        ");
        $apptStmt->execute([$id]);
        $hasAppointments = $apptStmt->fetchColumn() > 0;
        
        if ($hasAppointments) {
            $db->rollBack();
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cannot delete availability with booked appointments']);
            exit;
        }
        
        // Delete the availability
        $stmt = $db->prepare("DELETE FROM provider_availability WHERE availability_id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $db->commit();
            echo json_encode(['success' => true]);
            exit;
        } else {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => 'Availability slot not found']);
            exit;
        }
    } elseif ($type === 'unavailability') {
        // First check if this unavailability belongs to the provider
        if ($role === 'provider') {
            $checkStmt = $db->prepare("SELECT provider_id FROM provider_unavailability WHERE unavailability_id = ?");
            $checkStmt->execute([$id]);
            $owner = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$owner || $owner['provider_id'] != $user_id) {
                $db->rollBack();
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'You do not have permission to delete this unavailability']);
                exit;
            }
        }
        
        // Delete the unavailability
        $stmt = $db->prepare("DELETE FROM provider_unavailability WHERE unavailability_id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $db->commit();
            echo json_encode(['success' => true]);
            exit;
        } else {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => 'Unavailability slot not found']);
            exit;
        }
    } else {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid type']);
        exit;
    }
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
