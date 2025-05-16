<?php
/**
 * Notification System Fix
 * 
 * This script repairs common issues with the notification system:
 * 1. Creates missing database indexes for performance
 * 2. Adds notification display to navigation if missing
 * 3. Creates missing controller methods
 */

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'kholley_appointment_system';

$db = new mysqli($host, $user, $password, $database);
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

echo "Starting notification system fixes...\n\n";
$fixedItems = [];

// Fix 1: Add missing database indexes for better performance
function addMissingIndexes($db, &$fixedItems) {
    $indexesToAdd = [
        'user_id' => "CREATE INDEX idx_notifications_user_id ON notifications(user_id)",
        'is_read' => "CREATE INDEX idx_notifications_is_read ON notifications(is_read)",
        'created_at' => "CREATE INDEX idx_notifications_created_at ON notifications(created_at)",
        'appointment_id' => "CREATE INDEX idx_notifications_appointment_id ON notifications(appointment_id)"
    ];
    
    foreach ($indexesToAdd as $name => $sql) {
        $result = $db->query($sql);
        if ($result) {
            $fixedItems[] = "Added index on notifications.$name column";
        } else {
            echo "Warning: Could not add index on $name: " . $db->error . "\n";
        }
    }
}

// Fix 2: Add notification badge to navigation
function addNotificationBadge(&$fixedItems) {
    $navigationFile = __DIR__ . '/views/partials/navigation.php';
    
    if (!file_exists($navigationFile)) {
        echo "Warning: Navigation file not found at $navigationFile\n";
        return;
    }
    
    $navContent = file_get_contents($navigationFile);
    
    // Check if notification badge already exists
    if (strpos($navContent, 'unreadNotificationsCount') !== false) {
        echo "Notification badge already exists in navigation\n";
        return;
    }
    
    // Look for the user dropdown section
    $userDropdownPos = strpos($navContent, '<li class="nav-item dropdown">');
    
    if ($userDropdownPos === false) {
        echo "Warning: Could not find user dropdown in navigation\n";
        return;
    }
    
    // Create notification badge HTML
    $notificationBadgeCode = <<<HTML
<?php if (\$isLoggedIn): ?>
    <li class="nav-item dropdown me-2">
        <a class="nav-link position-relative" href="<?= base_url('index.php/' . \$userRole . '/notifications') ?>">
            <i class="bi bi-bell fs-5"></i>
            <?php
            // Get unread notification count
            \$unreadCount = 0;
            \$query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
            \$stmt = \$db->prepare(\$query);
            \$stmt->bind_param("i", \$_SESSION['user_id']);
            \$stmt->execute();
            \$result = \$stmt->get_result();
            if (\$row = \$result->fetch_assoc()) {
                \$unreadCount = \$row['count'];
            }
            ?>
            <?php if (\$unreadCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= \$unreadCount ?>
                    <span class="visually-hidden">unread notifications</span>
                </span>
            <?php endif; ?>
        </a>
    </li>
<?php endif; ?>

HTML;
    
    // Insert notification badge before user dropdown
    $newNavContent = substr_replace($navContent, $notificationBadgeCode, $userDropdownPos, 0);
    
    // Write updated navigation file
    if (file_put_contents($navigationFile, $newNavContent)) {
        $fixedItems[] = "Added notification badge to navigation";
    } else {
        echo "Warning: Could not update navigation file\n";
    }
}

// Fix 3: Create or update notification controller
function fixNotificationController(&$fixedItems) {
    $controllerPath = __DIR__ . '/controllers/notification_controller.php';
    $controllerExists = file_exists($controllerPath);
    
    $controllerCode = <<<PHP
<?php
require_once MODEL_PATH . '/Notification.php';

class NotificationController {
    private \$db;
    private \$notificationModel;
    
    public function __construct(\$db) {
        \$this->db = \$db;
        \$this->notificationModel = new Notification(\$db);
    }
    
    /**
     * Display notifications for the current user
     */
    public function index() {
        // Check if user is logged in
        if (!isset(\$_SESSION['user_id'])) {
            redirect('auth');
            return;
        }
        
        \$userId = \$_SESSION['user_id'];
        \$role = \$_SESSION['role'];
        
        // Get notifications for this user
        \$notifications = \$this->notificationModel->getNotificationsForUser(\$userId);
        
        // Load the appropriate view based on user role
        include VIEW_PATH . "/{\$role}/notifications.php";
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead() {
        // Check if user is logged in
        if (!isset(\$_SESSION['user_id'])) {
            redirect('auth');
            return;
        }
        
        // Check if notification ID is provided
        if (!isset(\$_POST['notification_id']) || empty(\$_POST['notification_id'])) {
            set_flash_message('error', 'Invalid notification');
            redirect(\$_SERVER['HTTP_REFERER'] ?? "{\$_SESSION['role']}/notifications");
            return;
        }
        
        \$notificationId = \$_POST['notification_id'];
        \$userId = \$_SESSION['user_id'];
        
        // Ensure the notification belongs to the current user
        \$notification = \$this->notificationModel->getNotificationById(\$notificationId);
        
        if (!\$notification || \$notification['user_id'] != \$userId) {
            set_flash_message('error', 'Access denied');
            redirect(\$_SERVER['HTTP_REFERER'] ?? "{\$_SESSION['role']}/notifications");
            return;
        }
        
        // Mark as read
        \$updated = \$this->notificationModel->markAsRead(\$notificationId);
        
        if (\$updated) {
            set_flash_message('success', 'Notification marked as read');
        } else {
            set_flash_message('error', 'Failed to update notification');
        }
        
        redirect(\$_SERVER['HTTP_REFERER'] ?? "{\$_SESSION['role']}/notifications");
    }
    
    /**
     * Mark all notifications as read for the current user
     */
    public function markAllAsRead() {
        // Check if user is logged in
        if (!isset(\$_SESSION['user_id'])) {
            redirect('auth');
            return;
        }
        
        \$userId = \$_SESSION['user_id'];
        
        // Mark all as read
        \$updated = \$this->notificationModel->markAllAsReadForUser(\$userId);
        
        if (\$updated) {
            set_flash_message('success', 'All notifications marked as read');
        } else {
            set_flash_message('error', 'Failed to update notifications');
        }
        
        redirect(\$_SERVER['HTTP_REFERER'] ?? "{\$_SESSION['role']}/notifications");
    }
    
    /**
     * Get count of unread notifications for the current user
     * Used for AJAX requests
     */
    public function getUnreadCount() {
        // Check if user is logged in
        if (!isset(\$_SESSION['user_id'])) {
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }
        
        \$userId = \$_SESSION['user_id'];
        \$count = \$this->notificationModel->getUnreadCountByUserId(\$userId);
        
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['count' => \$count]);
    }
}
PHP;

    // Only create/update if needed
    if (!$controllerExists) {
        if (file_put_contents($controllerPath, $controllerCode)) {
            $fixedItems[] = "Created missing notification_controller.php";
        } else {
            echo "Warning: Could not create notification controller\n";
        }
    } else {
        // Check if the file has the necessary methods
        $content = file_get_contents($controllerPath);
        
        $missingMethods = [];
        if (strpos($content, 'function index') === false) $missingMethods[] = 'index';
        if (strpos($content, 'function markAsRead') === false) $missingMethods[] = 'markAsRead';
        if (strpos($content, 'function getUnreadCount') === false) $missingMethods[] = 'getUnreadCount';
        if (strpos($content, 'function markAllAsRead') === false) $missingMethods[] = 'markAllAsRead';
        
        if (!empty($missingMethods)) {
            echo "Warning: NotificationController exists but is missing methods: " . implode(', ', $missingMethods) . "\n";
            echo "Consider manually adding these methods or replacing the file.\n";
        } else {
            echo "NotificationController has all required methods.\n";
        }
    }
}

// Fix 4: Add missing model methods to Notification class
function fixNotificationModel(&$fixedItems) {
    $modelPath = __DIR__ . '/models/Notification.php';
    
    if (!file_exists($modelPath)) {
        echo "Warning: Notification model not found at expected location\n";
        return;
    }
    
    $content = file_get_contents($modelPath);
    
    $missingMethods = [];
    $methodsToAdd = [];
    
    // Check for missing methods in the Notification model
    if (strpos($content, 'function getNotificationsForUser') === false) {
        $missingMethods[] = 'getNotificationsForUser';
        $methodsToAdd[] = <<<'PHP'
    
    /**
     * Get notifications for a specific user
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of notifications to return
     * @return array Array of notifications
     */
    public function getNotificationsForUser($userId, $limit = 20) {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        return $notifications;
    }
PHP;
    }
    
    if (strpos($content, 'function getNotificationById') === false) {
        $missingMethods[] = 'getNotificationById';
        $methodsToAdd[] = <<<'PHP'
    
    /**
     * Get a specific notification by ID
     *
     * @param int $notificationId Notification ID
     * @return array|null Notification data or null if not found
     */
    public function getNotificationById($notificationId) {
        $sql = "SELECT * FROM notifications WHERE notification_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
PHP;
    }
    
    if (strpos($content, 'function markAsRead') === false) {
        $missingMethods[] = 'markAsRead';
        $methodsToAdd[] = <<<'PHP'
    
    /**
     * Mark a notification as read
     *
     * @param int $notificationId Notification ID
     * @return bool True if successful, false otherwise
     */
    public function markAsRead($notificationId) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $notificationId);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
PHP;
    }
    
    if (strpos($content, 'function markAllAsReadForUser') === false) {
        $missingMethods[] = 'markAllAsReadForUser';
        $methodsToAdd[] = <<<'PHP'
    
    /**
     * Mark all notifications as read for a user
     *
     * @param int $userId User ID
     * @return bool True if successful, false otherwise
     */
    public function markAllAsReadForUser($userId) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
PHP;
    }
    
    if (strpos($content, 'function getUnreadCountByUserId') === false) {
        $missingMethods[] = 'getUnreadCountByUserId';
        $methodsToAdd[] = <<<'PHP'
    
    /**
     * Get count of unread notifications for a user
     *
     * @param int $userId User ID
     * @return int Count of unread notifications
     */
    public function getUnreadCountByUserId($userId) {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return (int)$row['count'];
        }
        
        return 0;
    }
PHP;
    }
    
    if (!empty($missingMethods)) {
                echo "Notification model is missing methods: " . implode(', ', $missingMethods) . "\n";
        
        // Find the closing brace of the class to append methods
        $lastBracePos = strrpos($content, '}');
        if ($lastBracePos !== false) {
            $newContent = substr($content, 0, $lastBracePos) . implode('', $methodsToAdd) . "\n}\n";
            
            if (file_put_contents($modelPath, $newContent)) {
                $fixedItems[] = "Added missing methods to Notification model: " . implode(', ', $missingMethods);
            } else {
                echo "Warning: Could not update Notification model\n";
            }
        } else {
            echo "Warning: Could not locate the end of the Notification class\n";
        }
    } else {
        echo "Notification model has all required methods.\n";
    }
}

// Run the fixes
echo "Applying database fixes...\n";
addMissingIndexes($db, $fixedItems);

echo "\nChecking notification badge in navigation...\n";
addNotificationBadge($fixedItems);

echo "\nChecking notification controller...\n";
fixNotificationController($fixedItems);

echo "\nChecking notification model...\n";
fixNotificationModel($fixedItems);

// Report results
echo "\n=== FIX SUMMARY ===\n";
if (empty($fixedItems)) {
    echo "No fixes were applied - your notification system appears to be complete!\n";
} else {
    echo count($fixedItems) . " fixes were applied:\n";
    foreach ($fixedItems as $item) {
        echo "  ✅ $item\n";
    }
}

echo "\nNotification system fix script completed.\n";
?>
