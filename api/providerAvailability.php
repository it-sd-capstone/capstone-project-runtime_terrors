
<?php
session_start();
require_once '../config/database.php';

// Validate session
if (!isset($_SESSION['provider_id'])) {
    http_response_code(401);
    echo "Unauthorized: Provider not logged in.";
    exit;
}

$provider_id = $_SESSION['provider_id'];

// Validate and sanitize input
$start_time = $_POST['start_time'] ?? null;
$end_time = $_POST['end_time'] ?? null;
$repeat = isset($_POST['repeat']) ? filter_var($_POST['repeat'], FILTER_VALIDATE_BOOLEAN) : false;
$repeat_until = $_POST['repeat_until'] ?? null;

if (!$start_time || !$end_time) {
    http_response_code(400);
    echo "Missing start or end time.";
    exit;
}

if ($repeat && !$repeat_until) {
    http_response_code(400);
    echo "Missing repeat_until date for recurring availability.";
    exit;
}

try {
    $pdo = getDatabaseConnection();

    $start = new DateTime($start_time);
    $end = new DateTime($end_time);

    // If recurring, set the repeat end date
    $until = $repeat ? new DateTime($repeat_until) : null;

    do {
        // Insert availability
        $stmt = $pdo->prepare("INSERT INTO availability (provider_id, start_time, end_time) VALUES (?, ?, ?)");
        $stmt->execute([
            $provider_id,
            $start->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s')
        ]);

        // Prepare next week's time
        $start->modify('+1 week');
        $end->modify('+1 week');
    } while ($repeat && $start <= $until);

    echo "Availability saved" . ($repeat ? " with recurrence." : ".");
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>
