<?php
/**
 * System Notification Helpers
 * 
 * Functions for logging system events as notifications
 */

/**
 * Log a system event as a notification for administrators
 * 
 * @param string $event_type Type of system event
 * @param string $message Detailed message
 * @param string $subject Short subject line
 * @return int|bool ID of created notification or false on failure
 */
function logSystemEvent($event_type, $message, $subject = null) {
    global $db; // Assumes $db is available in the global scope
    
    // If no database connection is available, try to connect
    if (!isset($db) || !$db) {
        // Try to include database configuration
        if (file_exists(dirname(__DIR__) . '/config/database.php')) {
            require_once dirname(__DIR__) . '/config/database.php';
            // Attempt to create database connection if constants are defined
            if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                if ($db->connect_error) {
                    error_log("Failed to connect to database for system notification: " . $db->connect_error);
                    return false;
                }
            }
        }
        
        if (!isset($db) || !$db) {
            error_log("No database connection available for system notification");
            return false;
        }
    }
    
    // Get an admin user ID for the foreign key requirement
    $admin_query = "SELECT user_id FROM users WHERE role = 'admin' LIMIT 1";
    $admin_result = $db->query($admin_query);
    
    if (!$admin_result || $admin_result->num_rows == 0) {
        // Use the first user as fallback if no admin exists
        $user_query = "SELECT user_id FROM users LIMIT 1";
        $user_result = $db->query($user_query);
        
        if (!$user_result || $user_result->num_rows == 0) {
            error_log("No valid user found for system notification");
            return false; // No users in system
        }
        
        $user_row = $user_result->fetch_assoc();
        $user_id = $user_row['user_id'];
    } else {
        $admin_row = $admin_result->fetch_assoc();
        $user_id = $admin_row['user_id'];
    }
    
    // If no subject provided, generate one from the event type
    if (!$subject) {
        $subject = ucwords(str_replace('_', ' ', $event_type));
    }
    
    // Prepare notification data
    $current_time = date('Y-m-d H:i:s');
    
    // Use prepared statement for insertion
    $query = "INSERT INTO notifications (user_id, subject, message, type, status, created_at, is_system, is_read, audience) 
              VALUES (?, ?, ?, ?, 'unread', ?, 1, 0, 'admin')";
              
    $stmt = $db->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare statement for system notification: " . $db->error);
        return false;
    }
    
    $stmt->bind_param('issss', $user_id, $subject, $message, $event_type, $current_time);
    
    $success = $stmt->execute();
    if (!$success) {
        error_log("Failed to create system notification: " . $stmt->error);
        return false;
    }
    
    $notification_id = $db->insert_id;
    $stmt->close();
    
    return $notification_id;
}