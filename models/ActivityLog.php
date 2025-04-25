<?php
/**
 * ActivityLog Model
 * Handles system activity logging for audit purposes
 */
class ActivityLog {
    private $db;
    
    /**
     * Constructor
     * 
     * @param object $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
        $this->ensureTableExists();
    }
    
    /**
     * Log an activity
     * 
     * @param int $userId ID of user performing the action (null for system)
     * @param string $description Description of the activity
     * @param string $category Optional activity category for filtering
     * @return bool Success status
     */
    public function logActivity($userId, $description, $category = 'general') {
        try {
            $ipAddress = $this->getClientIp();
            
            $query = "INSERT INTO activity_log (user_id, description, category, ip_address) 
                     VALUES (?, ?, ?, ?)";
                     
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("isss", $userId, $description, $category, $ipAddress);
                return $stmt->execute();
            } else {
                $stmt = $this->db->prepare($query);
                return $stmt->execute([$userId, $description, $category, $ipAddress]);
            }
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log user authentication activity
     * 
     * @param int $userId User ID
     * @param string $action Login, logout, failed_login, etc.
     * @param string $details Additional details
     * @return bool Success status
     */
    public function logAuth($userId, $action, $details = '') {
        $description = "Auth: $action" . ($details ? " - $details" : "");
        return $this->logActivity($userId, $description, 'authentication');
    }
    
    /**
     * Log user account activity
     * 
     * @param int $userId User ID
     * @param string $action Created, updated, password_changed, etc.
     * @param int $targetUserId User ID of the affected user
     * @return bool Success status
     */
    public function logUserActivity($userId, $action, $targetUserId = null) {
        $targetInfo = $targetUserId ? " (User ID: $targetUserId)" : "";
        $description = "User: $action$targetInfo";
        return $this->logActivity($userId, $description, 'user');
    }
    
    /**
     * Log appointment activity
     * 
     * @param int $userId User ID
     * @param string $action Created, updated, canceled, etc.
     * @param int $appointmentId Appointment ID
     * @param string $details Optional JSON-encoded details about the action
     * @return bool Success status
     */
    public function logAppointment($userId, $action, $appointmentId, $details = null) {
        $description = "Appointment: $action (ID: $appointmentId)";
        
        // Store additional details if provided
        if ($details) {
            // Insert with details column
            try {
                $ipAddress = $this->getClientIp();
                
                $query = "INSERT INTO activity_log (user_id, description, category, ip_address, details, related_id, related_type) 
                         VALUES (?, ?, ?, ?, ?, ?, 'appointment')";
                         
                if ($this->db instanceof mysqli) {
                    $stmt = $this->db->prepare($query);
                    $category = 'appointment';
                    $stmt->bind_param("issssi", $userId, $description, $category, $ipAddress, $details, $appointmentId);
                    return $stmt->execute();
                } else {
                    $stmt = $this->db->prepare($query);
                    return $stmt->execute([$userId, $description, 'appointment', $ipAddress, $details, $appointmentId]);
                }
            } catch (Exception $e) {
                error_log("Error logging appointment activity with details: " . $e->getMessage());
                // Fallback to standard logging without details
                return $this->logActivity($userId, $description, 'appointment');
            }
        } else {
            // Standard logging without details
            return $this->logActivity($userId, $description, 'appointment');
        }
    }
    
    /**
     * Log service activity
     * 
     * @param int $userId User ID
     * @param string $action Created, updated, deleted, etc.
     * @param string $serviceName Name of the service
     * @return bool Success status
     */
    public function logService($userId, $action, $serviceName) {
        $description = "Service: $action - $serviceName";
        return $this->logActivity($userId, $description, 'service');
    }
    
    /**
     * Log system configuration changes
     * 
     * @param int $userId User ID
     * @param string $setting Name of the setting
     * @param string $oldValue Previous value
     * @param string $newValue New value
     * @return bool Success status
     */
    public function logConfigChange($userId, $setting, $oldValue, $newValue) {
        $description = "Config: Updated $setting from '$oldValue' to '$newValue'";
        return $this->logActivity($userId, $description, 'configuration');
    }
    
    /**
     * Get recent activity log entries
     * 
     * @param int $limit Number of entries to return
     * @param string $category Filter by category (optional)
     * @return array Activity entries
     */
    public function getRecentActivity($limit = 20, $category = null) {
        try {
            $whereClause = $category ? "WHERE category = ?" : "";
            $params = $category ? [$category] : [];
            
            $query = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
                     FROM activity_log a
                     LEFT JOIN users u ON a.user_id = u.user_id
                     $whereClause
                     ORDER BY a.created_at DESC
                     LIMIT ?";
                     
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare($query);
                
                if ($category) {
                    $stmt->bind_param("si", $category, $limit);
                } else {
                    $stmt->bind_param("i", $limit);
                }
                
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                $stmt = $this->db->prepare($query);
                $params[] = $limit;
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Error getting activity log: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    private function getClientIp() {
        $ipAddress = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ipAddress;
    }
    
    /**
     * Ensure the activity_log table exists
     */
    private function ensureTableExists() {
        try {
            // Check if table exists
            $tableExists = false;
            
            if ($this->db instanceof mysqli) {
                $result = $this->db->query("SHOW TABLES LIKE 'activity_log'");
                $tableExists = $result && $result->num_rows > 0;
            } else {
                $stmt = $this->db->query("SHOW TABLES LIKE 'activity_log'");
                $tableExists = $stmt->rowCount() > 0;
            }
            
            if (!$tableExists) {
                // Create table
                $query = "CREATE TABLE activity_log (
                    log_id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NULL,
                    description TEXT NOT NULL,
                    category VARCHAR(50) DEFAULT 'general',
                    ip_address VARCHAR(45) NULL,
                    details TEXT NULL,
                    related_id INT NULL,
                    related_type VARCHAR(50) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (user_id),
                    INDEX (category),
                    INDEX (related_id, related_type),
                    INDEX (created_at)
                )";
                
                if ($this->db instanceof mysqli) {
                    $this->db->query($query);
                } else {
                    $this->db->exec($query);
                }
                
                // Add a default system entry
                $this->logActivity(null, "Activity logging system initialized", "system");
            } else {
                // Check if needed columns exist, add if they don't
                $this->ensureColumnsExist();
            }
        } catch (Exception $e) {
            error_log("Error ensuring activity_log table exists: " . $e->getMessage());
        }
    }
    
    /**
     * Ensure all needed columns exist in the table
     */
    private function ensureColumnsExist() {
        try {
            if ($this->db instanceof mysqli) {
                // Check and add 'category' column
                $result = $this->db->query("SHOW COLUMNS FROM activity_log LIKE 'category'");
                if (!$result || $result->num_rows == 0) {
                    $this->db->query("ALTER TABLE activity_log ADD COLUMN category VARCHAR(50) DEFAULT 'general' AFTER description");
                    $this->db->query("ALTER TABLE activity_log ADD INDEX (category)");
                }
                
                // Check and add 'details' column
                $result = $this->db->query("SHOW COLUMNS FROM activity_log LIKE 'details'");
                if (!$result || $result->num_rows == 0) {
                    $this->db->query("ALTER TABLE activity_log ADD COLUMN details TEXT NULL AFTER ip_address");
                }
                
                // Check and add 'related_id' and 'related_type' columns
                $result = $this->db->query("SHOW COLUMNS FROM activity_log LIKE 'related_id'");
                if (!$result || $result->num_rows == 0) {
                    $this->db->query("ALTER TABLE activity_log ADD COLUMN related_id INT NULL AFTER details");
                    $this->db->query("ALTER TABLE activity_log ADD COLUMN related_type VARCHAR(50) NULL AFTER related_id");
                    $this->db->query("ALTER TABLE activity_log ADD INDEX (related_id, related_type)");
                }
            } else {
                // PDO implementation
                $stmt = $this->db->query("SHOW COLUMNS FROM activity_log LIKE 'category'");
                if ($stmt->rowCount() == 0) {
                    $this->db->exec("ALTER TABLE activity_log ADD COLUMN category VARCHAR(50) DEFAULT 'general' AFTER description");
                    $this->db->exec("ALTER TABLE activity_log ADD INDEX (category)");
                }
                
                $stmt = $this->db->query("SHOW COLUMNS FROM activity_log LIKE 'details'");
                if ($stmt->rowCount() == 0) {
                    $this->db->exec("ALTER TABLE activity_log ADD COLUMN details TEXT NULL AFTER ip_address");
                }
                
                $stmt = $this->db->query("SHOW COLUMNS FROM activity_log LIKE 'related_id'");
                if ($stmt->rowCount() == 0) {
                    $this->db->exec("ALTER TABLE activity_log ADD COLUMN related_id INT NULL AFTER details");
                    $this->db->exec("ALTER TABLE activity_log ADD COLUMN related_type VARCHAR(50) NULL AFTER related_id");
                    $this->db->exec("ALTER TABLE activity_log ADD INDEX (related_id, related_type)");
                }
            }
        } catch (Exception $e) {
            error_log("Error ensuring columns exist: " . $e->getMessage());
        }
    }
    
    /**
     * Get activity logs for a specific appointment
     * 
     * @param int $appointmentId The appointment ID
     * @return array Logs for the appointment
     */
    public function getAppointmentLogs($appointmentId) {
        try {
            $query = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as user_name, 
                            u.first_name as user_first_name, u.last_name as user_last_name, 
                            u.role as user_role
                     FROM activity_log a
                     LEFT JOIN users u ON a.user_id = u.user_id
                     WHERE a.category = 'appointment' 
                       AND (a.related_id = ? OR a.description LIKE ?)
                     ORDER BY a.created_at DESC";
            
            $searchPattern = "%Appointment: % (ID: $appointmentId)%";
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("is", $appointmentId, $searchPattern);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                $stmt = $this->db->prepare($query);
                $stmt->execute([$appointmentId, $searchPattern]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Error getting appointment logs: " . $e->getMessage());
            return [];
        }
    }
} 