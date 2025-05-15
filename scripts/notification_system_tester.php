<?php
/**
 * Notification System Tester
 *
 * This script tests the actual notification system implementation.
 * Designed to run independently without relying on project bootstrapping.
 */

// Test mode - set to false to actually create test notifications
$testOnly = true;

class NotificationSystemTester {
    private $db;
    private $testOnly;
    private $results = [];
    private $expectedNotifications = [];
    
    public function __construct($testOnly = true) {
        $this->testOnly = $testOnly;
        $this->connectToDatabase();
        $this->defineExpectedNotifications();
    }
    
    /**
     * Connect to the database using mysqli
     */
    private function connectToDatabase() {
        // Database credentials - change to match your config
        $host = 'localhost';
        $user = 'root';
        $password = '';
        $database = 'kholley_appointment_system';
        
        // Create connection using mysqli
        $this->db = new mysqli($host, $user, $password, $database);
        
        // Check connection
        if ($this->db->connect_error) {
            die("Database connection failed: " . $this->db->connect_error);
        }
        
        // Set character set
        $this->db->set_charset("utf8mb4");
    }
    
    /**
     * Define all the notification types that should exist in the system
     */
    private function defineExpectedNotifications() {
        // Patient notifications
        $this->expectedNotifications['patient'] = [
            'appointment_booked' => 'Notification when a patient books a new appointment',
            'appointment_confirmed' => 'Notification when provider confirms an appointment',
            'appointment_cancelled' => 'Notification when an appointment is cancelled',
            'appointment_rescheduled' => 'Notification when an appointment is rescheduled',
            'reminder' => 'Reminder notification for upcoming appointments'
        ];
        
        // Provider notifications
        $this->expectedNotifications['provider'] = [
            'new_appointment' => 'Notification when a new appointment is booked',
            'cancelled_appointment' => 'Notification when a patient cancels an appointment',
            'rescheduled_appointment' => 'Notification when a patient requests appointment rescheduling'
        ];
        
        // Admin notifications
        $this->expectedNotifications['admin'] = [
            'new_provider_registration' => 'Notification when a new provider is registered',
            'new_patient_registration' => 'Notification when a new patient is registered'
        ];
    }
    
    /**
     * Check if notifications table exists and analyze it
     */
    public function analyzeNotificationTable() {
        $this->results['database'] = [];
        
        // Check if notifications table exists
        $query = "SHOW TABLES LIKE 'notifications'";
        $result = $this->db->query($query);
        
        $tableExists = $result && $result->num_rows > 0;
        $this->results['database']['table_exists'] = $tableExists;
        
        if (!$tableExists) {
            return;
        }
        
        // Get table structure
        $query = "DESCRIBE notifications";
        $result = $this->db->query($query);
        
        if (!$result) {
            $this->results['database']['error'] = $this->db->error;
            return;
        }
        
        $this->results['database']['columns'] = [];
        while ($column = $result->fetch_assoc()) {
            $this->results['database']['columns'][$column['Field']] = [
                'type' => $column['Type'],
                'nullable' => $column['Null'],
                'key' => $column['Key'],
                'default' => $column['Default']
            ];
        }
        
        // Check for required columns
        $requiredColumns = ['notification_id', 'user_id', 'message', 'type', 'is_read', 'created_at'];
        $missingColumns = [];
        
        foreach ($requiredColumns as $column) {
            if (!isset($this->results['database']['columns'][$column])) {
                $missingColumns[] = $column;
            }
        }
        
        $this->results['database']['missing_columns'] = $missingColumns;
        $this->results['database']['has_required_structure'] = empty($missingColumns);
        
        // Count notifications in table
        $query = "SELECT COUNT(*) as count FROM notifications";
        $result = $this->db->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            $this->results['database']['notification_count'] = $row['count'];
        } else {
            $this->results['database']['notification_count'] = 0;
        }
        
        // Get notification types
        $query = "SELECT DISTINCT type FROM notifications";
        $result = $this->db->query($query);
        
        $types = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $types[] = $row['type'];
            }
        }
        
        $this->results['database']['notification_types'] = $types;
    }
    
    /**
     * Analyze files for notification code
     */
    public function analyzeFilesForNotifications() {
        $this->results['files'] = [];
        $controllers = glob(__DIR__ . '/controllers/*_controller.php');
        
        foreach ($controllers as $controller) {
            $filename = basename($controller);
            $content = file_get_contents($controller);
            
            // Look for notification-related code
            $hasNotificationCode = preg_match('/notification|notify/i', $content) > 0;
            
            if ($hasNotificationCode) {
                $this->results['files'][$filename] = true;
            }
        }
    }
    
    /**
     * Check if notification views exist
     */
    public function checkNotificationViews() {
        $this->results['views'] = [];
        
        // Common locations for notification views
        $viewPaths = [
            __DIR__ . '/views/notifications.php',
            __DIR__ . '/views/partials/notifications.php',
            __DIR__ . '/views/components/notifications.php',
            __DIR__ . '/views/shared/notifications.php',
            __DIR__ . '/views/patient/notifications.php',
            __DIR__ . '/views/provider/notifications.php'
        ];
        
        foreach ($viewPaths as $path) {
            if (file_exists($path)) {
                $this->results['views'][basename(dirname($path)) . '/' . basename($path)] = true;
            }
        }
    }
    
    /**
     * Run all tests and return results
     */
    public function run() {
        try {
            $this->analyzeNotificationTable();
            $this->analyzeFilesForNotifications();
            $this->checkNotificationViews();
            
            return $this->generateReport();
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
    
    /**
     * Generate report of findings
     */
    private function generateReport() {
        return [
            'summary' => [
                'database_exists' => $this->results['database']['table_exists'] ?? false,
                'has_required_columns' => $this->results['database']['has_required_structure'] ?? false,
                'notification_count' => $this->results['database']['notification_count'] ?? 0,
                'notification_types' => $this->results['database']['notification_types'] ?? [],
                'files_with_notification_code' => array_keys($this->results['files'] ?? []),
                'notification_views' => array_keys($this->results['views'] ?? [])
            ],
            'details' => $this->results
        ];
    }
    
    /**
     * Create sample test notifications if table exists
     */
    public function createTestNotifications() {
        if ($this->testOnly || !($this->results['database']['table_exists'] ?? false)) {
            return false;
        }
        
        // Get some user IDs for testing
        $query = "SELECT user_id, role FROM users LIMIT 3";
        $result = $this->db->query($query);
        
        if (!$result || $result->num_rows == 0) {
            return false;
        }
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        // Create test notifications
        $created = [];
        
        foreach ($this->expectedNotifications as $role => $notifications) {
            foreach ($notifications as $type => $description) {
                // Find an appropriate user for this notification
                $userId = null;
                foreach ($users as $user) {
                    if ($user['role'] == $role || $role == 'admin') {
                        $userId = $user['user_id'];
                        break;
                    }
                }
                
                if (!$userId) {
                    continue;
                }
                
                $message = "Test {$type} notification: {$description}";
                $query = "INSERT INTO notifications (user_id, type, message, is_read, created_at) 
                          VALUES (?, ?, ?, 0, NOW())";
                
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("iss", $userId, $type, $message);
                
                if ($stmt->execute()) {
                    $created[$role][$type] = $message;
                }
            }
        }
        
        return $created;
    }
}

// Run the tester
$tester = new NotificationSystemTester($testOnly);
$results = $tester->run();

// Format and display results
function displayHeader($text) {
    $line = str_repeat('=', strlen($text) + 4);
    echo "\n$line\n  $text  \n$line\n";
}

function displaySubheader($text) {
    $line = str_repeat('-', strlen($text) + 2);
    echo "\n$text\n$line\n";
}

displayHeader('NOTIFICATION SYSTEM TEST RESULTS');

// Display summary
displaySubheader('DATABASE');
if ($results['summary']['database_exists']) {
    echo "✅ Notifications table exists\n";
    
    if ($results['summary']['has_required_columns']) {
        echo "✅ All required columns present\n";
    } else {
        echo "❌ Missing columns: " . implode(', ', $results['details']['database']['missing_columns']) . "\n";
    }
    
    echo "Total notifications: " . $results['summary']['notification_count'] . "\n";
    
    if (!empty($results['summary']['notification_types'])) {
        echo "\nNotification types found in database:\n";
        foreach ($results['summary']['notification_types'] as $type) {
            echo "- $type\n";
        }
    } else {
        echo "\n❌ No notification types found in database\n";
    }
} else {
    echo "❌ Notifications table does not exist\n";
    echo "You need to create a notifications table with these columns:\n";
    echo "- id (PRIMARY KEY)\n";
    echo "- user_id (Foreign key to users table)\n";
    echo "- message (Text of the notification)\n";
    echo "- type (Type/category of notification)\n";
    echo "- is_read (Boolean flag for read status)\n";
    echo "- created_at (Timestamp)\n";
}

displaySubheader('FILES WITH NOTIFICATION CODE');
if (!empty($results['summary']['files_with_notification_code'])) {
    foreach ($results['summary']['files_with_notification_code'] as $file) {
        echo "- $file\n";
    }
} else {
    echo "❌ No notification code found in controllers\n";
}

displaySubheader('NOTIFICATION VIEWS');
if (!empty($results['summary']['notification_views'])) {
    foreach ($results['summary']['notification_views'] as $view) {
        echo "✅ $view\n";
    }
} else {
    echo "❌ No dedicated notification views found\n";
}

displaySubheader('IMPLEMENTATION STATUS');
if ($results['summary']['database_exists'] && 
    $results['summary']['has_required_columns'] && 
    !empty($results['summary']['notification_types']) &&
    !empty($results['summary']['files_with_notification_code']) && 
    !empty($results['summary']['notification_views'])) {
    echo "✅ Notification system appears to be implemented\n";
} else {
    echo "❌ Notification system is incomplete or missing\n";
}

displayHeader('END OF REPORT');
?>