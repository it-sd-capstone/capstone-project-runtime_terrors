<?php
/**
 * System Notification Injector
 * 
 * This script:
 * 1. Creates the logSystemEvent function in a helper file
 * 2. Scans your codebase for potential system event locations
 * 3. Injects logSystemEvent calls at appropriate locations
 */

// Configuration
$root_dir = dirname(__DIR__); // Parent directory of scripts folder
$dry_run = true;  // Set to true to preview changes without applying them
$create_helper = true; // Whether to create the helper function file

// Define the helper function file
$helper_file = $root_dir . '/helpers/system_notifications.php';

// Event patterns to look for and the notifications to add
$event_patterns = [
    // Database backups
    [
        'pattern' => '/function\s+(?:perform|run|execute|do)(?:Weekly|Daily|Monthly)?Backup\s*\([^)]*\)\s*{/',
        'event_type' => 'database_backup',
        'subject' => 'Database Backup',
        'message' => 'Database backup operation was completed',
        'insert_after' => '{',
        'condition' => null // No condition needed
    ],
    
    // System settings updated
    [
        'pattern' => '/(?:update|save|change)Settings\s*\([^)]*\)\s*{/',
        'event_type' => 'configuration_change',
        'subject' => 'System Settings Updated',
        'message' => 'System configuration settings were updated',
        'insert_after' => '{',
        'condition' => '$success'
    ],
    
    // New provider added
    [
        'pattern' => '/function\s+(?:add|create|register)Provider\s*\([^)]*\)\s*{/',
        'event_type' => 'provider_added',
        'subject' => 'New Provider Added',
        'message' => 'A new healthcare provider was added to the system',
        'insert_after' => 'if\s*\(.+\)\s*{', // Look for success condition
        'condition' => null
    ],
    
    // User role changes
    [
        'pattern' => '/function\s+(?:change|update|modify)UserRole\s*\([^)]*\)\s*{/',
        'event_type' => 'user_role_change',
        'subject' => 'User Role Changed',
        'message' => 'A user\'s role was modified in the system',
        'insert_after' => 'if\s*\(.+\)\s*{', // Look for success condition
        'condition' => null
    ],
    
    // Multiple failed logins
    [
        'pattern' => '/function\s+(?:check|track|log)FailedLogins\s*\([^)]*\)\s*{/',
        'event_type' => 'security_alert',
        'subject' => 'Multiple Failed Login Attempts',
        'message' => 'Multiple failed login attempts detected for a user account',
        'insert_after' => 'if\s*\(\s*\$(?:count|attempts|failures)\s*>\s*\d+\s*\)\s*{',
        'condition' => null
    ],
    
    // System errors
    [
        'pattern' => '/catch\s*\(\s*(?:Exception|Error|Throwable)\s+\$(?:e|ex|exception|error)\s*\)\s*{/',
        'event_type' => 'system_error',
        'subject' => 'System Error Detected',
        'message' => 'A system error occurred: \' . $e->getMessage() . \'',
        'insert_after' => '{',
        'condition' => null
    ]
];

// Create the helper function file if it doesn't exist
if ($create_helper && !file_exists($helper_file)) {
    createHelperFile($helper_file);
    echo "✅ Created helper file: $helper_file\n";
}

// Get all PHP files in the project
$files = findPhpFiles($root_dir);
echo "Found " . count($files) . " PHP files to scan.\n";

// Track changes
$changes_made = 0;
$files_modified = 0;

// Process each file
foreach ($files as $file) {
    $file_changes = processFile($file, $event_patterns, $dry_run);
    if ($file_changes > 0) {
        $files_modified++;
        $changes_made += $file_changes;
    }
}

// Output results
echo "\n==== System Notification Injector Summary ====\n";
echo "Mode: " . ($dry_run ? "Dry run (no files modified)" : "Live run (files were modified)") . "\n";
echo "Total files scanned: " . count($files) . "\n";
echo "Files modified: $files_modified\n";
echo "Total changes made: $changes_made\n";
echo "Helper file: " . ($create_helper ? "Created/Updated" : "Skipped") . "\n";

/**
 * Create the helper file with the logSystemEvent function
 */
function createHelperFile($file_path) {
    $helper_content = <<<'PHP'
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
PHP;

    // Create the directory if it doesn't exist
    $dir = dirname($file_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Write the file
    file_put_contents($file_path, $helper_content);
}

/**
 * Recursively find all PHP files in a directory
 * 
 * @param string $dir Directory to search
 * @return array List of PHP file paths
 */
function findPhpFiles($dir) {
    $files = [];
    $skip_dirs = ['vendor', 'node_modules', '.git'];
    
    if (is_dir($dir)) {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            $path = $dir . '/' . $item;
            
            // Skip certain directories
            if (is_dir($path) && !in_array($item, $skip_dirs)) {
                $files = array_merge($files, findPhpFiles($path));
            } else if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) == 'php') {
                $files[] = $path;
            }
        }
    }
    
    return $files;
}

/**
 * Process a single PHP file to inject system event logging
 * 
 * @param string $file_path Path to the PHP file
 * @param array $patterns Patterns to search for
 * @param bool $dry_run Whether to actually modify the file
 * @return int Number of changes made
 */
function processFile($file_path, $patterns, $dry_run) {
    $content = file_get_contents($file_path);
    if (!$content) {
        return 0;
    }
    
    $changes = 0;
    $file_modified = false;
    
    // Add the necessary include if not present
    if (strpos($content, 'system_notifications.php') === false) {
        $include_statement = "require_once '" . str_replace('\\', '/', dirname(dirname(__FILE__))) . "/helpers/system_notifications.php';\n";
        
        // Look for an appropriate place to add the include
        if (preg_match('/<\?php\s+/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $position = $matches[0][1] + strlen($matches[0][0]);
            $content = substr($content, 0, $position) . $include_statement . substr($content, $position);
            $file_modified = true;
            $changes++;
        }
    }
    
    foreach ($patterns as $pattern_info) {
        if (preg_match_all($pattern_info['pattern'], $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $match_pos = $match[1] + strlen($match[0]);
                
                // Look for the insertion point
                if ($pattern_info['insert_after'] != '{') {
                    if (preg_match('/' . $pattern_info['insert_after'] . '/', 
                            substr($content, $match_pos), $inner_match, PREG_OFFSET_CAPTURE)) {
                        $match_pos += $inner_match[0][1] + strlen($inner_match[0][0]);
                    } else {
                        // Can't find insertion point, skip this match
                        continue;
                    }
                }
                
                // Check if the code already has a logSystemEvent call for this event type
                $next_100_chars = substr($content, $match_pos, 100);
                if (strpos($next_100_chars, 'logSystemEvent') !== false && 
                    strpos($next_100_chars, $pattern_info['event_type']) !== false) {
                    // Already has a similar log event, skip
                    continue;
                }
                
                // Create the log statement
                $log_code = "\n    // Log system event\n";
                if ($pattern_info['condition']) {
                    $log_code .= "    if ({$pattern_info['condition']}) {\n        ";
                }
                
                $log_code .= "logSystemEvent('{$pattern_info['event_type']}', '{$pattern_info['message']}', '{$pattern_info['subject']}');";
                
                if ($pattern_info['condition']) {
                    $log_code .= "\n    }";
                }
                $log_code .= "\n";
                
                if (!$dry_run) {
                    $content = substr($content, 0, $match_pos) . $log_code . substr($content, $match_pos);
                }
                
                $file_modified = true;
                $changes++;
                
                // Since we've modified the content, we need to adjust match positions for any future matches
                // We'll just break here since it's simpler, and process the file again on the next run if needed
                break 2;
            }
        }
    }
    
    // Save the modified file
    if ($file_modified && !$dry_run) {
        file_put_contents($file_path, $content);
        echo "✅ Modified: $file_path ($changes changes)\n";
    } elseif ($file_modified && $dry_run) {
        echo "🔍 Would modify: $file_path ($changes changes)\n";
    }
    
    return $changes;
}
?>
