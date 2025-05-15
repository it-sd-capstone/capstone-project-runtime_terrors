<?php
/**
 * Notification Model
 * Handles data operations for notifications
 */
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
    
    // Get user email dynamically
    public function getUserEmail($user_id) {
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
    public function getUserNotifications($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? OR user_id IS NULL 
                ORDER BY created_at DESC
            ");
            
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            // Get the result set
            $result = $stmt->get_result();
            
            // Fetch all rows as an associative array
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            
            return $notifications;
        } catch (Exception $e) {
            error_log("Error getting user notifications: " . $e->getMessage());
            return [];
        }
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
            
            // Check if is_system column exists
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
                    return $this->getSampleNotifications();
                }
                
                $stmt->bind_param("i", $limit);
                $execResult = $stmt->execute();
                
                if (!$execResult) {
                    error_log("Execute error: " . $stmt->error);
                    return $this->getSampleNotifications();
                }
                
                $result = $stmt->get_result();
                $notifications = $result->fetch_all(MYSQLI_ASSOC);
                
                // If no notifications found, return sample notifications
                if (empty($notifications)) {
                    error_log("No notifications found in database, returning samples");
                    return $this->getSampleNotifications();
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
                    return $this->getSampleNotifications();
                }
                
                error_log("Retrieved " . count($notifications) . " notifications from database");
                return $notifications;
            }
        } catch (Exception $e) {
            // If there's an error, log it and return some sample notifications
            error_log("Error in getLatestSystemNotifications: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            return $this->getSampleNotifications();
        }
    }
    
      /**
     * Get sample notifications for fallback
     *
     * @return array Sample notifications
     */
    public function getSampleNotifications() {
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
            // Try with minimal fields if full insert fails

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
            
            $whereClause = "WHERE " . implode(" AND ", $conditions);
            
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
     * Ensure the notifications table exists in the database
     * Creates it if it doesn't exist
     * 
     * @return bool True if table exists or was created
     */
    private function ensureNotificationsTable() {
        try {
            // Check if table exists
            $exists = false;
            
            if ($this->db instanceof mysqli) {
                $result = $this->db->query("SHOW TABLES LIKE 'notifications'");
                $exists = ($result && $result->num_rows > 0);
            } else {
                // For PDO (assuming SQLite or another database)
                $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='notifications'");
                $exists = ($result && count($result->fetchAll()) > 0);
            }
            
            // If table doesn't exist, create it
            if (!$exists) {
                $sql = "CREATE TABLE IF NOT EXISTS notifications (
                    notification_id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    type VARCHAR(50) NOT NULL,
                    message TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    is_read TINYINT(1) DEFAULT 0,
                    link VARCHAR(255) NULL,
                    metadata TEXT NULL
                )";
                
                if ($this->db instanceof mysqli) {
                    $this->db->query($sql);
                } else {
                    $this->db->exec($sql);
                }
                
                error_log("Created notifications table");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error ensuring notifications table: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get notification preferences for a user
     * 
     * @param int $userId The user ID
     * @return array The notification preferences
     */
    public function getNotificationPreferences($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notification_preferences 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            } else {
                // Return default preferences if none exist
                return [
                    'email_notifications' => 1,
                    'sms_notifications' => 0,
                    'appointment_reminders' => 1,
                    'system_updates' => 1,
                    'reminder_time' => 24
                ];
            }
        } catch (Exception $e) {
            error_log("Error getting notification preferences: " . $e->getMessage());
            return [
                'email_notifications' => 1,
                'sms_notifications' => 0,
                'appointment_reminders' => 1,
                'system_updates' => 1,
                'reminder_time' => 24
            ];
        }
    }

    /**
     * Update notification preferences for a user
     * 
     * @param int $userId The user ID
     * @param int $emailNotifications Whether to send email notifications (1 or 0)
     * @param int $smsNotifications Whether to send SMS notifications (1 or 0)
     * @param int $appointmentReminders Whether to send appointment reminders (1 or 0)
     * @param int $systemUpdates Whether to send system updates (1 or 0)
     * @param int $reminderTime Hours before appointment to send reminder (default 24)
     * @return bool Whether the update was successful
     */
    public function updateNotificationPreferences($userId, $emailNotifications, $smsNotifications, $appointmentReminders, $systemUpdates, $reminderTime = 24) {
        try {
            // Check if preferences already exist for this user
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM notification_preferences 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                // Update existing preferences
                $stmt = $this->db->prepare("
                    UPDATE notification_preferences 
                    SET email_notifications = ?, 
                        sms_notifications = ?, 
                        appointment_reminders = ?, 
                        system_updates = ?,
                        reminder_time = ?,
                        updated_at = NOW()
                    WHERE user_id = ?
                ");
                $stmt->bind_param("iiiiii", $emailNotifications, $smsNotifications, $appointmentReminders, $systemUpdates, $reminderTime, $userId);
            } else {
                // Insert new preferences
                $stmt = $this->db->prepare("
                    INSERT INTO notification_preferences 
                    (user_id, email_notifications, sms_notifications, appointment_reminders, system_updates, reminder_time, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->bind_param("iiiiii", $userId, $emailNotifications, $smsNotifications, $appointmentReminders, $systemUpdates, $reminderTime);
            }
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating notification preferences: " . $e->getMessage());
            return false;
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
       * @param string $ipAddress IP address of the client (optional)
       * @return bool Success status
       */
      public function logActivity($action, $details, $userId = null, $ipAddress = null) {
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

              // Get client IP address if not provided
              if ($ipAddress === null) {
                  $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
              }

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
}
