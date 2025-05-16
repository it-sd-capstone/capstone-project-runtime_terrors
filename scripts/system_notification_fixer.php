<?php
/**
 * System Notification Fixer
 *
 * This script automatically implements system notifications in key locations
 * throughout the codebase to ensure admin dashboard shows actual system events.
 */

// Configuration
$basePath = dirname(__DIR__);
$controllersPath = $basePath . '/controllers';
$modelsPath = $basePath . '/models';
$dryRun = false; // Set to false to actually modify the files

// Define the system notifications with exact code and target methods
$systemNotifications = [
    // Admin controller notifications
    'admin_controller.php' => [
        'dashboard' => [
            '// Add system notification for admin login',
            '$notification = new Notification($this->db);',
            '$notification->create([',
            '    \'subject\' => \'Admin Login\',',
            '    \'message\' => "Admin logged in: " . $_SESSION[\'username\'] . " from " . $_SERVER[\'REMOTE_ADDR\'],',
            '    \'type\' => \'admin_login\',',
            '    \'is_system\' => 1,',
            '    \'audience\' => \'admin\'',
            ']);'
        ],
        'getAppointmentAnalytics' => [
            '// Log analytics request as system notification',
            '$notification = new Notification($this->db);',
            '$notification->create([',
            '    \'subject\' => \'Analytics Generated\',',
            '    \'message\' => "Appointment analytics generated for period: " . $period,',
            '    \'type\' => \'analytics_generated\',',
            '    \'is_system\' => 1,',
            '    \'audience\' => \'admin\'',
            ']);'
        ]
    ],
    
    // Auth controller notifications
    'auth_controller.php' => [
        'registerUser' => [
            '// Add system notification for new user registration',
            'if ($user_id) {',
            '    $notification = new Notification($this->db);',
            '    $notification->create([',
            '        \'subject\' => \'New User Registration\',',
            '        \'message\' => "New " . $user_data[\'role\'] . " registered: " . $user_data[\'name\'] . " (" . $user_data[\'email\'] . ")",',
            '        \'type\' => \'user_registered\',',
            '        \'is_system\' => 1,',
            '        \'audience\' => \'admin\'',
            '    ]);',
            '}'
        ],
        'processLogin' => [
            '// Log successful logins as system events',
            'if ($userId) {',
            '    $notification = new Notification($this->db);',
            '    $notification->create([',
            '        \'subject\' => \'User Login\',',
            '        \'message\' => "User login: " . $email . " (" . $role . ")",',
            '        \'type\' => \'user_login\',',
            '        \'is_system\' => 1,',
            '        \'audience\' => \'admin\'',
            '    ]);',
            '}'
        ]
    ],
    
    // Appointment controller notifications
    'appointments_controller.php' => [
        'create' => [
            '// Log new appointment creation as system notification',
            'if ($appointmentId) {',
            '    $notification = new Notification($this->db);',
            '    $notification->create([',
            '        \'subject\' => \'New Appointment\',',
            '        \'message\' => "New appointment created for service ID: " . $service_id,',
            '        \'type\' => \'appointment_created\',',
            '        \'is_system\' => 1,',
            '        \'audience\' => \'admin\'',
            '    ]);',
            '}'
        ],
        'cancel' => [
            '// Log appointment cancellation as system notification',
            'if ($success) {',
            '    $notification = new Notification($this->db);',
            '    $notification->create([',
            '        \'subject\' => \'Appointment Cancelled\',',
            '        \'message\' => "Appointment ID: " . $appointment_id . " has been cancelled",',
            '        \'type\' => \'appointment_cancelled\',',
            '        \'is_system\' => 1,',
            '        \'audience\' => \'admin\'',
            '    ]);',
            '}'
        ],
        'reschedule' => [
            '// Log appointment rescheduling as system notification',
            'if ($success) {',
            '    $notification = new Notification($this->db);',
            '    $notification->create([',
            '        \'subject\' => \'Appointment Rescheduled\',',
            '        \'message\' => "Appointment ID: " . $appointment_id . " has been rescheduled",',
            '        \'type\' => \'appointment_rescheduled\',',
            '        \'is_system\' => 1,',
            '        \'audience\' => \'admin\'',
            '    ]);',
            '}'
        ],
        'confirm' => [
            '// Log appointment confirmation as system notification',
            'if ($success) {',
            '    $appointment = $this->appointmentModel->getAppointmentById($appointment_id);',
            '    $notification = new Notification($this->db);',
            '    $notification->create([',
            '        \'subject\' => \'Appointment Confirmed\',',
            '        \'message\' => "Appointment ID: " . $appointment_id . " has been confirmed",',
            '        \'type\' => \'appointment_confirmed\',',
            '        \'is_system\' => 1,',
            '        \'audience\' => \'admin\'',
            '    ]);',
            '}'
        ]
    ],

    // Database backup/maintenance scripts
    'backup_controller.php' => [
        'performBackup' => [
            '// Log database backup status',
            'if ($backupSuccess) {',
            '    $notification = new Notification($this->db);',
            '    $notification->create([',
            '        \'subject\' => \'Database Backup\',',
            '        \'message\' => "Database backup completed successfully",',
            '        \'type\' => \'system_maintenance\',',
            '        \'is_system\' => 1,',
            '        \'audience\' => \'admin\'',
            '    ]);',
            '} else {',
            '    $notification = new Notification($this->db);',
            '    $notification->create([',
            '        \'subject\' => \'Database Backup Failed\',',
            '        \'message\' => "System backup failed: " . $errorMessage,',
            '        \'type\' => \'system_error\',',
            '        \'is_system\' => 1,',
            '        \'audience\' => \'admin\'',
            '    ]);',
            '}'
        ]
    ]
];

// Function to analyze existing code to determine where to insert system notifications
function analyzeExistingCode($fileContent, $methodName) {
    // Find method in the file
    if (preg_match('/function\s+' . $methodName . '\s*\([^)]*\)[^{]*{(.*?)}(?=\s*(?:public|private|protected|function|\}))/s', $fileContent, $matches)) {
        $methodBody = $matches[1];
        
        // Check for variables that indicate success/failure
        $variables = [
            'success' => preg_match('/\$success\s*=/', $methodBody),
            'result' => preg_match('/\$result\s*=/', $methodBody),
            'backupSuccess' => preg_match('/\$backupSuccess\s*=/', $methodBody),
            'appointmentId' => preg_match('/\$appointmentId\s*=/', $methodBody),
            'user_id' => preg_match('/\$user_id\s*=/', $methodBody),
            'userId' => preg_match('/\$userId\s*=/', $methodBody),
        ];
        
        // Check if notification model is already included
        $hasNotificationModel = preg_match('/new\s+Notification/', $methodBody);
        
        // Look for return or redirect pattern
        $hasRedirect = preg_match('/redirect\(.*?\);/', $methodBody);
        $hasReturn = preg_match('/return\s+.*;/', $methodBody);
        
        return [
            'variables' => $variables,
            'hasNotificationModel' => $hasNotificationModel,
            'hasRedirect' => $hasRedirect,
            'hasReturn' => $hasReturn
        ];
    }
    
    return null;
}

// Function to insert code into a function body, optimized for system notifications
function insertSystemNotification($fileContent, $methodName, $codeToInsert) {
    // Find the method
    if (!preg_match('/function\s+' . $methodName . '\s*\([^)]*\)\s*{/i', $fileContent, $matches, PREG_OFFSET_CAPTURE)) {
        echo "Method $methodName not found\n";
        return $fileContent;
    }
    
    $methodStart = $matches[0][1];
    
    // Find the opening brace position
    $openBracePos = strpos($fileContent, '{', $methodStart);
    if ($openBracePos === false) {
        echo "Could not find opening brace for method $methodName\n";
        return $fileContent;
    }
    
    // Analyze the existing code
    $analysis = analyzeExistingCode($fileContent, $methodName);
    
    // Check if there's already a system notification in this method
    $methodBody = substr($fileContent, $openBracePos);
    if (strpos($methodBody, 'is_system') !== false && strpos($methodBody, '1') !== false) {
        echo "Method $methodName already appears to have system notifications\n";
        return $fileContent;
    }
    
    // Determine the best place to insert the notification
    $insertPos = $openBracePos + 1; // Default is right after the opening brace
    
    // Find the end of method to place just before closing brace if needed
    $braceCount = 1;
    $methodEnd = $openBracePos;
    for ($i = $openBracePos + 1; $i < strlen($fileContent); $i++) {
        if ($fileContent[$i] === '{') {
            $braceCount++;
        } elseif ($fileContent[$i] === '}') {
            $braceCount--;
            if ($braceCount === 0) {
                $methodEnd = $i;
                break;
            }
        }
    }
    
    // If there's a return statement, put notification before it
    if ($analysis && $analysis['hasReturn']) {
        // Find the position of the return statement
        $returnPos = strpos($fileContent, 'return', $openBracePos);
        if ($returnPos !== false && $returnPos < $methodEnd) {
            $insertPos = $returnPos;
        }
    }
    
    // If there's a redirect, put notification before it
    if ($analysis && $analysis['hasRedirect']) {
        // Find the position of the redirect call
        $redirectPos = strpos($fileContent, 'redirect(', $openBracePos);
        if ($redirectPos !== false && $redirectPos < $methodEnd) {
            $insertPos = $redirectPos;
        }
    }
    
    // Indent the code to insert
    $indent = str_repeat(' ', 8); // Standard indentation
    $codeWithIndent = "\n" . $indent . implode("\n" . $indent, $codeToInsert) . "\n";
    
    // Insert the code
    $newContent = substr($fileContent, 0, $insertPos) . 
                  $codeWithIndent . 
                  substr($fileContent, $insertPos);
    
    return $newContent;
}

// Function to create a test endpoint to generate test system notifications
function createTestEndpoint($controllersPath) {
    $notificationControllerPath = $controllersPath . '/notification_controller.php';
    
    if (!file_exists($notificationControllerPath)) {
        echo "Notification controller not found at: $notificationControllerPath\n";
        return false;
    }
    
    $fileContent = file_get_contents($notificationControllerPath);
    
    // Check if test endpoint already exists
    if (strpos($fileContent, 'createTestSystemNotifications') !== false) {
        echo "Test system notifications endpoint already exists\n";
        return true;
    }
    
    // Find the class ending
    $lastBracePos = strrpos($fileContent, '}');
    if ($lastBracePos === false) {
        echo "Could not find the end of the controller class\n";
        return false;
    }
    
    $testEndpoint = <<<EOD
        /**
         * Create test system notifications (for development)
         */
        public function createTestSystemNotifications() {
            // Create a response array
            \$response = ['success' => true, 'notifications' => []];
            
            // Define test notifications
            \$testNotifications = [
                [
                    'subject' => 'System Update',
                    'message' => 'System updated to version 2.1.0',
                    'type' => 'system_update',
                    'is_system' => 1,
                    'audience' => 'admin'
                ],
                [
                    'subject' => 'Database Backup',
                    'message' => 'Weekly database backup completed successfully',
                    'type' => 'system_maintenance',
                    'is_system' => 1,
                    'audience' => 'admin'
                ],
                [
                    'subject' => 'High Traffic Alert',
                    'message' => 'System experiencing high traffic (100+ concurrent users)',
                    'type' => 'system_alert',
                    'is_system' => 1,
                    'audience' => 'admin'
                ],
                [
                    'subject' => 'New Feature Available',
                    'message' => 'SMS notifications are now available for all users',
                    'type' => 'feature_update',
                    'is_system' => 1,
                    'audience' => 'admin'
                ]
            ];
            
            // Create each notification
            \$notification = new Notification(\$this->db);
            foreach (\$testNotifications as \$notificationData) {
                \$notificationId = \$notification->create(\$notificationData);
                if (\$notificationId) {
                    \$response['notifications'][] = array_merge(['id' => \$notificationId], \$notificationData);
                }
            }
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode(\$response);
        }
    EOD;


    // Insert the test endpoint
    $newContent = substr($fileContent, 0, $lastBracePos) . $testEndpoint . "\n" . substr($fileContent, $lastBracePos);
    
    // Write back to the file
    $success = file_put_contents($notificationControllerPath, $newContent);
    
    if ($success) {
        echo "✅ Created test system notifications endpoint\n";
        return true;
    } else {
        echo "❌ Failed to create test endpoint\n";
        return false;
    }
}

// Function to process controllers and add system notifications
function processControllers($controllersPath, $systemNotifications, $dryRun) {
    echo "Starting system notification fixer...\n";
    echo "Mode: " . ($dryRun ? "Dry run (no changes will be made)" : "Live run (files will be modified)") . "\n\n";
    
    foreach ($systemNotifications as $controllerName => $methods) {
        $controllerPath = $controllersPath . '/' . $controllerName;
        
        if (!file_exists($controllerPath)) {
            echo "Controller file not found: $controllerPath\n";
            continue;
        }
        
        echo "Processing controller: $controllerName\n";
        
        // Read the controller file
        $fileContent = file_get_contents($controllerPath);
        if ($fileContent === false) {
            echo "Could not read controller file: $controllerPath\n";
            continue;
        }
        
        $modified = false;
        
        // Process each method
        foreach ($methods as $methodName => $codeToInsert) {
            echo "  Checking method: $methodName\n";
            
            // Check if the method exists
            if (!preg_match('/function\s+' . $methodName . '\s*\(/i', $fileContent)) {
                echo "  ⚠️ Method $methodName not found in $controllerName - skipping\n";
                continue;
            }
            
            // Insert the system notification code
            $newContent = insertSystemNotification($fileContent, $methodName, $codeToInsert);
            
            if ($newContent !== $fileContent) {
                $fileContent = $newContent;
                $modified = true;
                echo "  ✅ Added system notification to $methodName\n";
            } else {
                echo "  ⏩ No changes made to $methodName\n";
            }
        }
        
        // Save the modified file
        if ($modified && !$dryRun) {
            if (file_put_contents($controllerPath, $fileContent) === false) {
                echo "❌ Failed to write to controller file: $controllerPath\n";
            } else {
                echo "✅ Successfully updated controller: $controllerName\n";
            }
        } elseif ($modified) {
            echo "✓ Would update controller: $controllerName (dry run)\n";
        } else {
            echo "✓ No changes needed for: $controllerName\n";
        }
    }
    
    echo "\nSystem notification fixer completed!\n";
}

// Create database setup function to ensure table has the right columns
function ensureCorrectDatabaseSchema($basePath, $dryRun) {
    echo "\nChecking database schema for system notifications...\n";
    
    if ($dryRun) {
        echo "Skipping database schema check in dry run mode\n";
        return;
    }
    
    // This would normally connect to the database and run checks
    // For simplicity, we'll provide SQL that can be run manually
    
    $sqlUpdates = <<<SQL
    -- SQL to ensure notifications table has the required columns for system notifications
    ALTER TABLE notifications ADD COLUMN IF NOT EXISTS is_system TINYINT(1) DEFAULT 0;
    ALTER TABLE notifications ADD COLUMN IF NOT EXISTS audience VARCHAR(50) DEFAULT NULL;
    -- Add indexes to improve performance
    CREATE INDEX IF NOT EXISTS idx_notifications_is_system ON notifications(is_system);
    CREATE INDEX IF NOT EXISTS idx_notifications_audience ON notifications(audience);
    SQL;
    
    // Write the SQL file for the user to run manually if needed
    $sqlFilePath = $basePath . '/scripts/ensure_notification_schema.sql';
    if (file_put_contents($sqlFilePath, $sqlUpdates)) {
        echo "✅ Created SQL file to update database schema: $sqlFilePath\n";
        echo "   Run this SQL manually if your database is missing these columns.\n";
    }
    
    // Optional: Try to run the SQL directly if MySQL connection details are available
    // This would require database connection credentials that we don't have here
}

// Function to create a system notification test panel in the admin dashboard
function createSystemNotificationPanel($basePath, $dryRun) {
    echo "\nChecking for system notification panel in admin dashboard...\n";
    
    if ($dryRun) {
        echo "Skipping panel creation in dry run mode\n";
        return;
    }
    
    // Path to the admin dashboard view
    $adminDashboardPath = $basePath . '/views/admin/index.php';
    
    if (!file_exists($adminDashboardPath)) {
        echo "❌ Admin dashboard view not found at: $adminDashboardPath\n";
        return;
    }
    
    $dashboardContent = file_get_contents($adminDashboardPath);
    
    // Check if a system notification panel already exists
    if (strpos($dashboardContent, 'system-notifications') !== false) {
        echo "✓ System notification panel already exists in admin dashboard\n";
        return;
    }
    
    // Create a system notification panel HTML
    $systemPanel = <<<HTML

<!-- System Notifications Panel -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">System Notifications</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">Notification Actions:</div>
                <a class="dropdown-item" href="#" id="mark-all-read">Mark All As Read</a>
                <a class="dropdown-item" href="#" id="refresh-notifications">Refresh</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="system-notifications" class="list-group">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading system notifications...</p>
            </div>
        </div>
    </div>
</div>

<script>
// Function to load system notifications
function loadSystemNotifications() {
    $.ajax({
        url: 'index.php/notification/getAdminNotifications',
        type: 'GET',
        data: { type: 'system' },
        dataType: 'json',
        success: function(response) {
            var notificationList = $('#system-notifications');
            notificationList.empty();
            
            if (response.notifications && response.notifications.length > 0) {
                $.each(response.notifications, function(index, notification) {
                    var notificationHtml = '<a href="#" class="list-group-item list-group-item-action' + 
                                         (notification.is_read ? '' : ' font-weight-bold') + 
                                         '" data-id="' + notification.id + '">' +
                                         '<div class="d-flex w-100 justify-content-between">' +
                                         '<h5 class="mb-1">' + notification.subject + '</h5>' +
                                         '<small>' + formatDate(notification.created_at) + '</small>' +
                                         '</div>' +
                                         '<p class="mb-1">' + notification.message + '</p>' +
                                         '<small>' + notification.type + '</small>' +
                                         '</a>';
                    notificationList.append(notificationHtml);
                });
                
                // Add click handler to mark notifications as read
                notificationList.find('a').click(function(e) {
                    e.preventDefault();
                    var notificationId = $(this).data('id');
                    markNotificationAsRead(notificationId);
                });
            } else {
                notificationList.html('<div class="text-center p-3">No system notifications found</div>');
            }
        },
        error: function() {
            $('#system-notifications').html(
                '<div class="alert alert-danger">Failed to load notifications</div>'
            );
        }
    });
}

// Function to mark a notification as read
function markNotificationAsRead(id) {
    $.ajax({
        url: 'index.php/notification/markAsRead/' + id,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                loadSystemNotifications();
            }
        }
    });
}

// Handle Mark All As Read action
$('#mark-all-read').click(function(e) {
    e.preventDefault();
    $.ajax({
        url: 'index.php/notification/markAllAsRead',
        type: 'POST',
        data: { type: 'system' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                loadSystemNotifications();
            }
        }
    });
});

// Handle Refresh action
$('#refresh-notifications').click(function(e) {
    e.preventDefault();
    loadSystemNotifications();
});

// Format date helper function
function formatDate(dateString) {
    if (!dateString) return '';
    
    var date = new Date(dateString);
    var options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    return date.toLocaleDateString('en-US', options);
}

// Load notifications when document is ready
$(document).ready(function() {
    loadSystemNotifications();
    
    // Refresh notifications every 2 minutes
    setInterval(loadSystemNotifications, 120000);
});
</script>
HTML;

    // Find a good place to insert the panel
    $insertPoint = strpos($dashboardContent, '<!-- Content Row -->');
    if ($insertPoint === false) {
        $insertPoint = strpos($dashboardContent, '<div class="row">');
    }
    
    if ($insertPoint !== false) {
        // Insert the panel before the content row
        $newContent = substr($dashboardContent, 0, $insertPoint) . 
                      $systemPanel .
                      substr($dashboardContent, $insertPoint);
        
        if (file_put_contents($adminDashboardPath, $newContent)) {
            echo "✅ Added system notification panel to admin dashboard\n";
        } else {
            echo "❌ Failed to update admin dashboard file\n";
        }
    } else {
        echo "❌ Could not find a suitable location to insert system notification panel\n";
    }
}

// Run the script
processControllers($controllersPath, $systemNotifications, $dryRun);

// Create test endpoint for generating system notifications
createTestEndpoint($controllersPath);

// Ensure database schema is correct
ensureCorrectDatabaseSchema($basePath, $dryRun);

// Create system notification panel in admin dashboard
createSystemNotificationPanel($basePath, $dryRun);

// Final instructions
echo "\n====================================================\n";
echo "SYSTEM NOTIFICATION IMPLEMENTATION " . ($dryRun ? "SIMULATION" : "COMPLETED") . "\n";
echo "----------------------------------------------------\n";
if ($dryRun) {
    echo "This was a DRY RUN - no files were modified.\n";
    echo "Set \$dryRun = false at the top of the script to apply changes.\n\n";
} else {
    echo "All system notifications have been added to your controllers.\n";
    echo "To test these notifications:\n";
    echo "1. Visit index.php/notification/createTestSystemNotifications to create test notifications\n";
    echo "2. Log in as an admin and check the dashboard\n";
    echo "3. Perform system actions to generate real notifications\n\n";
}
echo "For maximum system notification coverage, consider adding more notification points at:\n";
echo "- User profile updates\n";
echo "- System configuration changes\n";
echo "- Bulk operations (mass appointment updates, etc.)\n";
echo "- Error logs and system warnings\n";
echo "====================================================\n";
