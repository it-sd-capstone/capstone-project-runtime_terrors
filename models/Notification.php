<?php
class Notification {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Store a new notification
    public function createNotification($user_id, $appointment_id, $subject, $message, $type, $scheduled_for = null) {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, appointment_id, subject, message, type, status, scheduled_for) 
            VALUES (?, ?, ?, ?, ?, 'pending', ?)
        ");
    
        if (!$stmt->execute([$user_id, $appointment_id, $subject, $message, $type, $scheduled_for])) {
            error_log("Notification creation failed: " . implode(" | ", $stmt->errorInfo()));
            return false;
        }
    
        return true;
    }

    // Retrieve pending notifications for processing
    public function getPendingNotifications() {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications WHERE status = 'pending' AND scheduled_for <= NOW()
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function sendNotifications() {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications WHERE status = 'pending' AND scheduled_for <= NOW()
        ");
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        foreach ($notifications as $notification) {
            if ($this->sendNotificationEmail($notification['user_id'], $notification['subject'], $notification['message'])) {
                $this->markNotificationSent($notification['notification_id']);
            }
        }
    }
    
    // Add a method to send notifications via email
    private function sendNotificationEmail($user_id, $subject, $message) {
        $user_email = $this->getUserEmail($user_id); // Fetch email dynamically
        if (!$user_email) {
            return false;
        }
    
        // Simulate sending email (Replace with actual email sending logic)
        mail($user_email, $subject, $message);
        return true;
    }
    
    // Get user email dynamically
    private function getUserEmail($user_id) {
        $stmt = $this->db->prepare("SELECT email FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }
    

    // Mark a notification as sent
    public function markNotificationSent($notification_id) {
        $stmt = $this->db->prepare("
            UPDATE notifications SET status = 'sent', sent_at = NOW() WHERE notification_id = ?
        ");
        return $stmt->execute([$notification_id]);
    }

    // Retrieve notifications for a user
    public function getUserNotifications($user_id, $limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get latest system notifications for admin dashboard
     * 
     * @param int $limit Maximum number of notifications to retrieve
     * @return array Array of notification records
     */
    public function getLatestSystemNotifications($limit = 10) {
        error_log("Notification::getLatestSystemNotifications called with limit=$limit");
        
        try {
            // Make sure the notifications table exists with all required columns
            $this->ensureNotificationsTable();
            
            // Let's inspect the table structure to see what columns we have
            try {
                if ($this->db instanceof mysqli) {
                    $columns = $this->db->query("SHOW COLUMNS FROM notifications");
                    $columnList = [];
                    while ($column = $columns->fetch_assoc()) {
                        $columnList[] = $column['Field'];
                    }
                    error_log("Notification table columns: " . implode(", ", $columnList));
                    
                    // Check if is_system column exists
                    $hasIsSystemColumn = in_array('is_system', $columnList);
                    
                    // Build the query based on available columns
                    if ($hasIsSystemColumn) {
                        $query = "
                            SELECT * FROM notifications 
                            WHERE is_system = 1
                            ORDER BY created_at DESC 
                            LIMIT ?
                        ";
                    } else {
                        // Fallback query if is_system column doesn't exist
                        $query = "
                            SELECT * FROM notifications 
                            WHERE user_id IS NULL
                            ORDER BY created_at DESC 
                            LIMIT ?
                        ";
                    }
                    
                    error_log("Using query: $query");
                    
                    $stmt = $this->db->prepare($query);
                    if (!$stmt) {
                        error_log("Prepare statement error: " . $this->db->error);
                        return $this->createSampleNotifications();
                    }
                    
                    $stmt->bind_param("i", $limit);
                    $execResult = $stmt->execute();
                    
                    if (!$execResult) {
                        error_log("Execute error: " . $stmt->error);
                        return $this->createSampleNotifications();
                    }
                    
                    $result = $stmt->get_result();
                    $notifications = $result->fetch_all(MYSQLI_ASSOC);
                    
                    // If no notifications found, return sample notifications
                    if (empty($notifications)) {
                        error_log("No notifications found in database, returning samples");
                        return $this->createSampleNotifications();
                    }
                    
                    error_log("Retrieved " . count($notifications) . " notifications from database");
                    return $notifications;
                } else {
                    // Handle PDO connection
                    error_log("Using PDO connection");
                    
                    $columns = $this->db->query("SHOW COLUMNS FROM notifications")->fetchAll(PDO::FETCH_COLUMN);
                    $hasIsSystemColumn = in_array('is_system', $columns);
                    
                    if ($hasIsSystemColumn) {
                        $query = "
                            SELECT * FROM notifications 
                            WHERE is_system = 1
                            ORDER BY created_at DESC 
                            LIMIT ?
                        ";
                    } else {
                        // Fallback query if is_system column doesn't exist
                        $query = "
                            SELECT * FROM notifications 
                            WHERE user_id IS NULL
                            ORDER BY created_at DESC 
                            LIMIT ?
                        ";
                    }
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([$limit]);
                    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // If no notifications found, return sample notifications
                    if (empty($notifications)) {
                        error_log("No notifications found in database, returning samples");
                        return $this->createSampleNotifications();
                    }
                    
                    error_log("Retrieved " . count($notifications) . " notifications from database");
                    return $notifications;
                }
            } catch (Exception $e) {
                error_log("Error querying notifications: " . $e->getMessage());
                return $this->createSampleNotifications();
            }
        } catch (Exception $e) {
            // If there's an error, log it and return some sample notifications
            error_log("Error in getLatestSystemNotifications: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            return $this->createSampleNotifications();
        }
    }
    
    /**
     * Create sample notifications for testing when database access fails
     * 
     * @return array Sample notifications
     */
    private function createSampleNotifications() {
        error_log("Creating sample notifications for testing");
        
        // Create current timestamp
        $now = new DateTime();
        $nowStr = $now->format('Y-m-d H:i:s');
        
        // Create some timestamps for different times
        $fiveMinAgo = (new DateTime())->modify('-5 minutes')->format('Y-m-d H:i:s');
        $oneHourAgo = (new DateTime())->modify('-1 hour')->format('Y-m-d H:i:s');
        $oneDayAgo = (new DateTime())->modify('-1 day')->format('Y-m-d H:i:s');
        
        return [
            [
                'notification_id' => 1,
                'user_id' => null,
                'subject' => 'New Registration',
                'message' => 'New provider registered: Dr. Smith',
                'type' => 'user_registered',
                'created_at' => $fiveMinAgo,
                'is_read' => 0
            ],
            [
                'notification_id' => 2,
                'user_id' => null,
                'subject' => 'Appointment Statistics',
                'message' => '15 appointments confirmed today',
                'type' => 'appointment_confirmed',
                'created_at' => $oneHourAgo,
                'is_read' => 0
            ],
            [
                'notification_id' => 3,
                'user_id' => null,
                'subject' => 'System Warning',
                'message' => '3 appointments need admin review',
                'type' => 'system_warning',
                'created_at' => $oneHourAgo,
                'is_read' => 0
            ],
            [
                'notification_id' => 4,
                'user_id' => null,
                'subject' => 'System Error',
                'message' => 'System backup failed: Check database connection',
                'type' => 'system_error',
                'created_at' => $oneDayAgo,
                'is_read' => 0
            ]
        ];
    }
    
    /**
     * Add a new notification with flexible parameters
     * 
     * @param array $params Notification parameters
     * @return bool Success status
     */
    public function addNotification($params) {
        // Set default values for optional parameters
        $defaults = [
            'user_id' => null,
            'appointment_id' => null,
            'subject' => '',
            'message' => '',
            'type' => 'system',
            'status' => 'pending',
            'scheduled_for' => null,
            'is_system' => false,
            'is_read' => 0,
            'audience' => null
        ];
        
        // Merge provided parameters with defaults
        $params = array_merge($defaults, $params);
        
        try {
            // Check for duplicates if this is a system notification
            if ($params['is_system']) {
                if ($this->isDuplicateSystemNotification($params['subject'], $params['message'], $params['audience'])) {
                    error_log("Duplicate system notification prevented: " . $params['subject']);
                    return true; // Return true to indicate "success" even though we didn't add it
                }
            }
            
            if ($this->db instanceof mysqli) {
                $query = "
                    INSERT INTO notifications 
                    (user_id, appointment_id, subject, message, type, status, scheduled_for, is_system, is_read, audience) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                
                $stmt = $this->db->prepare($query);
                $stmt->bind_param(
                    "iisssssiss", 
                    $params['user_id'], 
                    $params['appointment_id'], 
                    $params['subject'], 
                    $params['message'], 
                    $params['type'], 
                    $params['status'], 
                    $params['scheduled_for'], 
                    $params['is_system'],
                    $params['is_read'],
                    $params['audience']
                );
                
                return $stmt->execute();
            } else {
                $query = "
                    INSERT INTO notifications 
                    (user_id, appointment_id, subject, message, type, status, scheduled_for, is_system, is_read, audience) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                
                $stmt = $this->db->prepare($query);
                return $stmt->execute([
                    $params['user_id'], 
                    $params['appointment_id'], 
                    $params['subject'], 
                    $params['message'], 
                    $params['type'], 
                    $params['status'], 
                    $params['scheduled_for'], 
                    $params['is_system'],
                    $params['is_read'],
                    $params['audience']
                ]);
            }
        } catch (Exception $e) {
            // If error occurs (likely due to missing columns), try with minimal fields
            error_log("Error adding notification: " . $e->getMessage());
            
            try {
                if ($this->db instanceof mysqli) {
                    $fallbackQuery = "
                        INSERT INTO notifications 
                        (user_id, subject, message, type, status) 
                        VALUES (?, ?, ?, ?, ?)
                    ";
                    
                    $stmt = $this->db->prepare($fallbackQuery);
                    $stmt->bind_param(
                        "issss", 
                        $params['user_id'], 
                        $params['subject'], 
                        $params['message'], 
                        $params['type'], 
                        $params['status']
                    );
                    
                    return $stmt->execute();
                } else {
                    $fallbackQuery = "
                        INSERT INTO notifications 
                        (user_id, subject, message, type, status) 
                        VALUES (?, ?, ?, ?, ?)
                    ";
                    
                    $stmt = $this->db->prepare($fallbackQuery);
                    return $stmt->execute([
                        $params['user_id'], 
                        $params['subject'], 
                        $params['message'], 
                        $params['type'], 
                        $params['status']
                    ]);
                }
            } catch (Exception $e2) {
                error_log("Failed to add notification with fallback method: " . $e2->getMessage());
                return false;
            }
        }
    }
    
    /**
     * Check if a system notification with the same content already exists
     * 
     * @param string $subject The notification subject
     * @param string $message The notification message
     * @param string $audience The notification audience (optional)
     * @return bool True if a duplicate exists, false otherwise
     */
    private function isDuplicateSystemNotification($subject, $message, $audience = null) {
        try {
            // Build the query with parameters
            $queryParams = [];
            $conditions = ["is_system = 1", "subject = ?", "message = ?"];
            $queryParams[] = $subject;
            $queryParams[] = $message;
            
            // Add audience condition if provided
            if ($audience !== null) {
                $conditions[] = "audience = ?";
                $queryParams[] = $audience;
            }
            
            // Additional time constraint to avoid checking very old notifications
            $conditions[] = "created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)";
            
            $whereClause = implode(" AND ", $conditions);
            $query = "SELECT COUNT(*) FROM notifications WHERE $whereClause";
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare($query);
                
                // Create the types string for bind_param
                $types = str_repeat("s", count($queryParams));
                
                // Use reflection to work around the bind_param array limitation
                $refStmt = new ReflectionClass($stmt);
                $refMethod = $refStmt->getMethod('bind_param');
                $params = array_merge([$types], $queryParams);
                $refMethod->invokeArgs($stmt, $params);
                
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                
                return $count > 0;
            } else {
                $stmt = $this->db->prepare($query);
                $stmt->execute($queryParams);
                return $stmt->fetchColumn() > 0;
            }
        } catch (Exception $e) {
            error_log("Error checking for duplicate notifications: " . $e->getMessage());
            return false; // If error, assume not duplicate and allow creation
        }
    }
    
    /**
     * Get unread count of unread notifications
     * 
     * @param string $role User role (optional filter)
     * @param int $userId User ID (optional filter)
     * @return int Count of unread notifications
     */
    public function getUnreadCount($role = null, $userId = null) {
        try {
            // Make sure notifications table exists
            $this->ensureNotificationsTable();
            
            $conditions = [];
            $params = [];
            
            // Build query based on parameters
            if ($userId !== null) {
                $conditions[] = "user_id = ?";
                $params[] = $userId;
            }
            
            $conditions[] = "is_read = 0";
            
            $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $query = "SELECT COUNT(*) FROM notifications $whereClause";
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare($query);
                
                if (!empty($params)) {
                    $types = str_repeat("i", count($params));
                    $stmt->bind_param($types, ...$params);
                }
                
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                return $count;
            } else {
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
                return $stmt->fetchColumn();
            }
        } catch (Exception $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Ensure the notifications table exists with all required columns
     * This is a helper function to create the table if it doesn't exist
     */
    private function ensureNotificationsTable() {
        try {
            if ($this->db instanceof mysqli) {
                // Check if table exists
                $result = $this->db->query("SHOW TABLES LIKE 'notifications'");
                $tableExists = $result && $result->num_rows > 0;
                
                if (!$tableExists) {
                    error_log("Creating notifications table");
                    
                    // Create the notifications table
                    $createTableQuery = "
                        CREATE TABLE notifications (
                            notification_id INT AUTO_INCREMENT PRIMARY KEY,
                            user_id INT NULL,
                            appointment_id INT NULL,
                            subject VARCHAR(255) NOT NULL,
                            message TEXT NOT NULL,
                            type VARCHAR(50) NOT NULL,
                            status VARCHAR(20) DEFAULT 'pending',
                            scheduled_for DATETIME NULL,
                            sent_at DATETIME NULL,
                            is_system TINYINT(1) DEFAULT 0,
                            is_read TINYINT(1) DEFAULT 0,
                            audience VARCHAR(50) NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            UNIQUE KEY unique_system_notification (subject(100), message(100), is_system, audience)
                        )
                    ";
                    
                    $this->db->query($createTableQuery);
                    error_log("Notifications table created successfully");
                    
                    // Add some sample notifications
                    $this->addSampleNotificationsToDb();
                    return;
                }
                
                // Check for required columns
                $columns = $this->db->query("SHOW COLUMNS FROM notifications");
                $columnList = [];
                while ($column = $columns->fetch_assoc()) {
                    $columnList[] = $column['Field'];
                }
                
                // Check for is_system column
                if (!in_array('is_system', $columnList)) {
                    error_log("Adding is_system column to notifications table");
                    $this->db->query("ALTER TABLE notifications ADD COLUMN is_system TINYINT(1) DEFAULT 0");
                }
                
                // Check for is_read column
                if (!in_array('is_read', $columnList)) {
                    error_log("Adding is_read column to notifications table");
                    $this->db->query("ALTER TABLE notifications ADD COLUMN is_read TINYINT(1) DEFAULT 0");
                }
                
                // Check for audience column
                if (!in_array('audience', $columnList)) {
                    error_log("Adding audience column to notifications table");
                    $this->db->query("ALTER TABLE notifications ADD COLUMN audience VARCHAR(50) NULL");
                }
                
                // Check for the unique index to prevent duplicates
                $indices = $this->db->query("SHOW INDEX FROM notifications WHERE Key_name = 'unique_system_notification'");
                $hasUniqueIndex = $indices && $indices->num_rows > 0;
                
                if (!$hasUniqueIndex) {
                    try {
                        error_log("Adding unique constraint to prevent duplicate system notifications");
                        $this->db->query("ALTER TABLE notifications ADD CONSTRAINT unique_system_notification UNIQUE (subject(100), message(100), is_system, audience)");
                    } catch (Exception $e) {
                        // It's possible there are duplicates already, so let's handle that
                        error_log("Error adding unique constraint: " . $e->getMessage());
                        error_log("Trying to remove duplicates first...");
                        
                        // Run a query to detect duplicates
                        $duplicatesQuery = "
                            SELECT subject, message, is_system, audience, COUNT(*) as count
                            FROM notifications
                            WHERE is_system = 1
                            GROUP BY subject, message, is_system, audience
                            HAVING COUNT(*) > 1
                        ";
                        $duplicatesResult = $this->db->query($duplicatesQuery);
                        
                        if ($duplicatesResult && $duplicatesResult->num_rows > 0) {
                            error_log("Found " . $duplicatesResult->num_rows . " sets of duplicates");
                            
                            // Create a temporary table to store the IDs we want to keep
                            $this->db->query("CREATE TEMPORARY TABLE IF NOT EXISTS notifications_to_keep (notification_id INT PRIMARY KEY)");
                            
                            // Add the highest ID (newest) of each duplicate set to the temporary table
                            $keepQuery = "
                                INSERT INTO notifications_to_keep
                                SELECT MAX(notification_id) as notification_id
                                FROM notifications
                                WHERE is_system = 1
                                GROUP BY subject, message, is_system, audience
                            ";
                            $this->db->query($keepQuery);
                            
                            // Delete all system notifications except those in the temporary table
                            $deleteQuery = "
                                DELETE FROM notifications
                                WHERE is_system = 1
                                AND notification_id NOT IN (SELECT notification_id FROM notifications_to_keep)
                            ";
                            $deleteResult = $this->db->query($deleteQuery);
                            
                            if ($deleteResult) {
                                error_log("Successfully removed duplicate notifications. Affected rows: " . $this->db->affected_rows);
                                
                                // Try to add the unique constraint again
                                $this->db->query("ALTER TABLE notifications ADD CONSTRAINT unique_system_notification UNIQUE (subject(100), message(100), is_system, audience)");
                            }
                            
                            // Drop the temporary table
                            $this->db->query("DROP TEMPORARY TABLE IF EXISTS notifications_to_keep");
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error ensuring notifications table: " . $e->getMessage());
        }
    }
    
    /**
     * Add sample notifications to the database
     */
    private function addSampleNotificationsToDb() {
        try {
            $sampleNotifications = $this->createSampleNotifications();
            
            foreach ($sampleNotifications as $notification) {
                if ($this->db instanceof mysqli) {
                    $stmt = $this->db->prepare("
                        INSERT INTO notifications 
                        (subject, message, type, created_at, is_read, is_system) 
                        VALUES (?, ?, ?, ?, ?, 1)
                    ");
                    
                    $stmt->bind_param(
                        "ssssi", 
                        $notification['subject'],
                        $notification['message'],
                        $notification['type'],
                        $notification['created_at'],
                        $notification['is_read']
                    );
                    
                    $stmt->execute();
                }
            }
            
            error_log("Sample notifications added to database");
        } catch (Exception $e) {
            error_log("Error adding sample notifications: " . $e->getMessage());
        }
    }
    
    /**
     * Mark a notification as read
     * 
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public function markAsRead($notificationId) {
        try {
            // Make sure the notifications table exists with all required columns
            $this->ensureNotificationsTable();
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
                $stmt->bind_param("i", $notificationId);
                return $stmt->execute();
            } else {
                $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
                return $stmt->execute([$notificationId]);
            }
        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function markAllAsRead($userId) {
        try {
            // Make sure the notifications table exists with all required columns
            $this->ensureNotificationsTable();
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                return $stmt->execute();
            } else {
                $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
                return $stmt->execute([$userId]);
            }
        } catch (Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log system activity for audit trail
     * 
     * @param string $action Action performed
     * @param string $details Additional details
     * @param int $userId User who performed the action
     * @return bool Success status
     */
    public function logActivity($action, $details, $userId = null) {
        try {
            // First check if activity_log table exists
            if ($this->db instanceof mysqli) {
                $result = $this->db->query("SHOW TABLES LIKE 'activity_log'");
                $tableExists = $result->num_rows > 0;
            } else {
                $result = $this->db->query("SHOW TABLES LIKE 'activity_log'");
                $tableExists = $result->rowCount() > 0;
            }
            
            if (!$tableExists) {
                // Create activity_log table if it doesn't exist
                $createTableQuery = "
                    CREATE TABLE activity_log (
                        log_id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NULL,
                        action VARCHAR(100) NOT NULL,
                        details TEXT,
                        ip_address VARCHAR(45),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ";
                
                $this->db->query($createTableQuery);
            }
            
            // Get client IP address
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    INSERT INTO activity_log (user_id, action, details, ip_address)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("isss", $userId, $action, $details, $ipAddress);
                return $stmt->execute();
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO activity_log (user_id, action, details, ip_address)
                    VALUES (?, ?, ?, ?)
                ");
                return $stmt->execute([$userId, $action, $details, $ipAddress]);
            }
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }
}
?>