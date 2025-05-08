<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$type = $data['type'] ?? null;

if (!$id || !$type) {
    echo json_encode(['success' => false, 'error' => 'Missing ID or type']);
    exit;
}

$db = Database::getInstance()->getConnection();

if ($type === 'availability') {
    $stmt = $db->prepare("DELETE FROM provider_availability WHERE availability_id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Availability slot not found']);
    exit;
} elseif ($type === 'unavailability') {
    $stmt = $db->prepare("DELETE FROM provider_unavailability WHERE unavailability_id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Unavailability slot not found']);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid type']);
    exit;
}
?>