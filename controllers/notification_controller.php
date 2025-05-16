<?php
require_once 'C:/xampp/htdocs/appointment-system/capstone-project-runtime_terrors/helpers/system_notifications.php';
require_once MODEL_PATH . '/Notification.php';

/**
 * NotificationController
 * Handles notification-related requests and operations
 */
class NotificationController {
    private $db;
    private $notificationModel;
    
    /**
     * Initialize controller with database connection and models
     */
    public function __construct($db = null) {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection if not provided
        $this->db = $db ?: get_db();
        
        // Initialize Notification model
        $this->notificationModel = new Notification($this->db);
    }
    
    /**
     * Display notifications for the current user
     * Required by the notification system
     */
    public function index() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            redirect('auth');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        
        // Get notifications for this user
        $notifications = $this->notificationModel->getNotificationsForUser($userId);
        
        // Load the appropriate view based on user role
        include VIEW_PATH . "/{$role}/notifications.php";
    }
    /**
     * Debug routing
     */
    public function debug() {
        echo "<h1>NotificationController Debug</h1>";
        echo "<p>This confirms the NotificationController is accessible.</p>";
        echo "<p>Try accessing <a href='index.php/notification/createTestSystemNotifications'>createTestSystemNotifications</a></p>";
    }

    /**
     * Retrieve and display notifications for a user
     *
     * @param int $user_id The user ID
     * @return void Loads the notifications view
     */
    public function notifications($user_id) {
        // Authorize access
        if (!$this->canAccessUserNotifications($user_id)) {
            set_flash_message('error', "You don't have permission to view these notifications", 'global');
            header('Location: ' . base_url('index.php'));
            exit;
        }
        
        $notifications = $this->notificationModel->getNotificationsForUser($user_id);
        include VIEW_PATH . '/patient/notifications.php';
    }
    
    /**
     * Get count of unread notifications for the current user
     * Used for AJAX requests
     */
    public function getUnreadCount() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $count = $this->notificationModel->getUnreadCountByUserId($userId);
        
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }
    
    /**
     * Send pending notifications (automated task)
     * Can be called from a cron job or task scheduler
     *
     * @return void
     */
    public function sendPendingNotifications() {
        // For automated tasks, add security check
        $apiKey = $_GET['api_key'] ?? $_POST['api_key'] ?? null;
        if (!$this->isValidApiRequest($apiKey)) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Unauthorized access']);
            exit;
        }
        
        $stats = ['processed' => 0, 'sent' => 0, 'failed' => 0];
        $pendingNotifications = $this->notificationModel->getPendingNotifications();
        $stats['processed'] = count($pendingNotifications);
        
        foreach ($pendingNotifications as $notification) {
            // Here you would call your email/SMS service
            // For now, we're just marking them as sent
            if ($this->notificationModel->markNotificationSent($notification['notification_id'])) {
                $stats['sent']++;
            } else {
                $stats['failed']++;
            }
        }
        
        // Log the activity
        $this->logActivity(
            'send_notifications',
            "Processed {$stats['processed']} notifications. Sent: {$stats['sent']}, Failed: {$stats['failed']}"
        );
        
        // Return result
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get latest system notifications for admin dashboard
     *
     * @return void Outputs JSON response
     */
    public function getAdminNotifications() {
        error_log("NotificationController::getAdminNotifications called");
        
        // Ensure user is admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            error_log("Access denied: User role is " . ($_SESSION['role'] ?? 'not set'));
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Unauthorized access',
                'success' => false
            ]);
            exit;
        }
        
        try {
            error_log("Attempting to get latest system notifications");
            
            // Get latest system notifications
            $notifications = $this->notificationModel->getLatestSystemNotifications(10);
            error_log("Retrieved " . count($notifications) . " notifications");
            
            // Format notifications for display
            $formattedNotifications = [];
            foreach ($notifications as $notification) {
                // Debug notification data
                error_log("Processing notification: " . json_encode($notification));
                
                // Determine alert type based on notification type
                $alertType = 'info'; // Default
                
                switch ($notification['type']) {
                    case 'appointment_created':
                    case 'user_registered':
                        $alertType = 'info';
                        break;
                    case 'appointment_confirmed':
                    case 'appointment_completed':
                        $alertType = 'success';
                        break;
                    case 'appointment_canceled':
                    case 'system_warning':
                        $alertType = 'warning';
                        break;
                    case 'system_error':
                    case 'security_alert':
                        $alertType = 'danger';
                        break;
                }
                
                // Calculate time difference
                $created = new DateTime($notification['created_at']);
                $now = new DateTime();
                $diff = $now->diff($created);
                
                if ($diff->d > 0) {
                    $timeAgo = $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
                } elseif ($diff->h > 0) {
                    $timeAgo = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                } elseif ($diff->i > 0) {
                    $timeAgo = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                } else {
                    $timeAgo = 'just now';
                }
                
                $formattedNotifications[] = [
                    'id' => $notification['notification_id'],
                    'type' => $alertType,
                    'message' => $notification['message'],
                    'time' => $timeAgo,
                    'is_read' => (bool)$notification['is_read']
                ];
            }
            
            // Debug the results
            error_log("Formatted " . count($formattedNotifications) . " notifications for response");
            
            $unreadCount = $this->notificationModel->getUnreadCountByUserId($_SESSION['user_id']);
            error_log("Unread count: " . $unreadCount);
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'notifications' => $formattedNotifications,
                'success' => true,
                'total_unread' => $unreadCount
            ]);
            exit;
        } catch (Exception $e) {
    // Log system event
logSystemEvent('system_error', 'A system error occurred: ' . $e->getMessage() . '', 'System Error Detected');

            error_log("Error in getAdminNotifications: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Internal server error: ' . $e->getMessage(),
                'success' => false
            ]);
            exit;
        }
    }
 
    /**
     * Mark notifications as read
     *
     * @return void Outputs JSON response
     */
    public function markAsRead() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            redirect('auth');
            return;
        }
        
        // Check if notification ID is provided
        if (!isset($_POST['notification_id']) || empty($_POST['notification_id'])) {
            set_flash_message('error', 'Invalid notification');
            redirect($_SERVER['HTTP_REFERER'] ?? $_SESSION['role'] . "/notifications");
            return;
        }
        
        $notificationId = $_POST['notification_id'];
        $userId = $_SESSION['user_id'];
        
        // Ensure the notification belongs to the current user
        $notification = $this->notificationModel->getNotificationById($notificationId);
        
        if (!$notification || $notification['user_id'] != $userId) {
            set_flash_message('error', 'Access denied');
            redirect($_SERVER['HTTP_REFERER'] ?? $_SESSION['role'] . "/notifications");
            return;
        }
        
        // Mark as read
        $updated = $this->notificationModel->markAsRead($notificationId);
        
        if ($updated) {
            set_flash_message('success', 'Notification marked as read');
        } else {
            set_flash_message('error', 'Failed to update notification');
        }
        
        redirect($_SERVER['HTTP_REFERER'] ?? $_SESSION['role'] . "/notifications");
    }
    
    /**
     * Mark all notifications as read for the current user
     */
    public function markAllAsRead() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            redirect('auth');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Mark all as read
        $updated = $this->notificationModel->markAllAsReadForUser($userId);
        
        if ($updated) {
            set_flash_message('success', 'All notifications marked as read');
        } else {
            set_flash_message('error', 'Failed to update notifications');
        }
        
        redirect($_SERVER['HTTP_REFERER'] ?? $_SESSION['role'] . "/notifications");
    }
    
    /**
     * Add a new system notification
     *
     * @param string $type Notification type
     * @param string $message Notification message
     * @param int $userId User ID (if applicable)
     * @return bool Success status
     */
    public function addSystemNotification($type, $message, $userId = null) {
        // Construct a proper subject line based on the type
        $subject = $this->getSubjectFromType($type);
        
        return $this->notificationModel->addNotification([
            'type' => $type,
            'subject' => $subject,
            'message' => $message,
            'user_id' => $userId,
            'is_system' => true
        ]);
    }
 
    /**
     * Log system activities for audit trail
     *
     * @param string $action Action performed
     * @param string $details Additional details
     * @param int $userId User who performed the action
     * @return bool Success status
     */
    public function logActivity($action, $details, $userId = null) {
        if (!$userId && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        
        // Get IP address for better tracking
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        
        return $this->notificationModel->logActivity($action, $details, $userId, $ipAddress);
    }
 
    /**
     * Check if the current user can access notifications for a specific user
     *
     * @param int $user_id The user ID to check
     * @return bool True if access is allowed
     */
    private function canAccessUserNotifications($user_id) {
        // Admin can access all notifications
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return true;
        }
        
        // Users can only access their own notifications
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id;
    }
 
    /**
     * Validate API requests for automated tasks
     *
     * @param string $apiKey API key from request
     * @return bool True if request is valid
     */
    private function isValidApiRequest($apiKey) {
        // In production, use a secure API key validation mechanism
        // For this example, we're using a simple check
        $validApiKey = getenv('API_KEY') ?: 'your-secure-api-key-here';
        
        return $apiKey === $validApiKey;
    }
 
    /**
     * Generate a subject line from notification type
     *
     * @param string $type Notification type
     * @return string Subject line
     */
    private function getSubjectFromType($type) {
        $subjects = [
            'appointment_created' => 'New Appointment Created',
            'appointment_confirmed' => 'Appointment Confirmed',
            'appointment_completed' => 'Appointment Completed',
            'appointment_canceled' => 'Appointment Canceled',
            'user_registered' => 'New User Registration',
            'system_warning' => 'System Warning',
            'system_error' => 'System Error',
            'security_alert' => 'Security Alert'
        ];
        
        return $subjects[$type] ?? 'System Notification';
    }

        /**
     * Display and manage notification settings for the current user
     */
    public function settings() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            set_flash_message('error', "You must be logged in to manage notification settings", 'auth_login');
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Handle form submission to update settings
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
            $smsNotifications = isset($_POST['sms_notifications']) ? 1 : 0;
            $appointmentReminders = isset($_POST['appointment_reminders']) ? 1 : 0;
            $systemUpdates = isset($_POST['system_updates']) ? 1 : 0;
            $reminderTime = isset($_POST['reminder_time']) ? intval($_POST['reminder_time']) : 24;
            
            // Update notification preferences in the database
            $success = $this->notificationModel->updateNotificationPreferences(
                $user_id,
                $emailNotifications,
                $smsNotifications,
                $appointmentReminders,
                $systemUpdates,
                $reminderTime
            );
            
            if ($success) {
                set_flash_message('success', "Notification settings updated successfully", 'global');
            } else {
                set_flash_message('error', "Failed to update notification settings", 'global');
            }
        }
        
        // Get current notification settings
        $settings = $this->notificationModel->getNotificationPreferences($user_id);
        
        // Include the view
        include VIEW_PATH . '/patient/notification_settings.php';
    }
    
    /**
     * Create a notification for a user about an appointment
     *
     * @param int $userId User ID
     * @param int $appointmentId Appointment ID
     * @param string $type Notification type
     * @param string $message Notification message
     * @return bool Success status
     */
    public function createAppointmentNotification($userId, $appointmentId, $type, $message) {
        // Format the notification data
        $notificationData = [
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'appointment_id' => $appointmentId,
            'is_read' => 0,
            'is_system' => 0
        ];
        
        // Add notification
        return $this->notificationModel->addNotification($notificationData);
    }
    
    /**
     * Delete notifications for a specific appointment
     *
     * @param int $appointmentId Appointment ID
     * @return bool Success status
     */
    public function deleteAppointmentNotifications($appointmentId) {
        return $this->notificationModel->deleteNotificationsByAppointmentId($appointmentId);
    }
    /**
     * Create test system notifications (for development)
     */
    public function createTestSystemNotifications() {
        // Create a response array
        $response = ['success' => true, 'notifications' => [], 'debug' => []];
        
        // First, find a valid admin user_id
        $admin_query = "SELECT user_id FROM users WHERE role = 'admin' LIMIT 1";
        $admin_result = $this->db->query($admin_query);
        
        if (!$admin_result || $admin_result->num_rows == 0) {
            // If no admin found, try to get any user
            $user_query = "SELECT user_id FROM users LIMIT 1";
            $user_result = $this->db->query($user_query);
            
            if (!$user_result || $user_result->num_rows == 0) {
                $response['error'] = "No valid users found in the database";
                header('Content-Type: application/json');
                echo json_encode($response, JSON_PRETTY_PRINT);
                return;
            }
            
            $user_row = $user_result->fetch_assoc();
            $user_id = $user_row['user_id'];
        } else {
            $admin_row = $admin_result->fetch_assoc();
            $user_id = $admin_row['user_id'];
        }
        
        $response['debug']['user_id_for_test'] = $user_id;
        
        // Define test notifications
        $testNotifications = [
            [
                'subject' => 'System Update',
                'message' => 'System updated to version 2.1.0',
                'type' => 'system_update',
                'status' => 'unread',
                'is_system' => 1,
                'audience' => 'admin',
                'user_id' => $user_id  // Add the valid user_id
            ],
            [
                'subject' => 'Database Backup',
                'message' => 'Weekly database backup completed successfully',
                'type' => 'system_maintenance',
                'status' => 'unread', 
                'is_system' => 1,
                'audience' => 'admin',
                'user_id' => $user_id  // Add the valid user_id
            ]
        ];
        
        // Create each notification
        $notification = new Notification($this->db);
        foreach ($testNotifications as $notificationData) {
            // Track debug info for this notification
            $debug_item = [
                'data' => $notificationData,
                'result' => null,
                'error' => null
            ];
            
            try {
                $notificationId = $notification->create($notificationData);
                $debug_item['result'] = $notificationId;
                
                if ($notificationId) {
                    $response['notifications'][] = array_merge(['id' => $notificationId], $notificationData);
                } else {
                    // Get the last error if available
                    if (method_exists($this->db, 'error')) {
                        $debug_item['error'] = $this->db->error;
                    }
                }
            } catch (Exception $e) {
                $debug_item['error'] = $e->getMessage();
            }
            
            $response['debug']['items'][] = $debug_item;
        }
        
        // Add table structure info if possible
        try {
            $tables_query = $this->db->query("SHOW TABLES LIKE 'notifications'");
            $response['debug']['table_exists'] = $tables_query && $tables_query->num_rows > 0;
            
            if ($response['debug']['table_exists']) {
                $columns_query = $this->db->query("SHOW COLUMNS FROM notifications");
                $columns = [];
                if ($columns_query) {
                    while ($column = $columns_query->fetch_assoc()) {
                        $columns[] = $column['Field'];
                    }
                }
                $response['debug']['columns'] = $columns;
            }
        } catch (Exception $e) {
            $response['debug']['schema_error'] = $e->getMessage();
        }
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }


}
?>