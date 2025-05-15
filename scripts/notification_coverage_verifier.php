<?php
/**
 * Comprehensive Notification System Verification
 * 
 * This script:
 * 1. Scans the codebase for all set_flash_message() calls
 * 2. Maps notifications to contexts and user roles
 * 3. Verifies complete coverage of the appointment lifecycle
 * 4. Generates suggestions for missing notifications
 * 5. Can generate test notifications for verification
 */

// Configuration
$codebasePath = __DIR__; // Root of your project
$reportOnly = true; // Set to false to actually trigger test notifications

// User role to context mapping (which notifications should appear for which roles)
$roleToContextMap = [
    'admin' => [
        'admin_dashboard', 'admin_providers', 'admin_services', 'admin_appointments', 
        'admin_users', 'admin_add_provider', 'admin_provider_services', 
        'admin_provider_availability', 'admin_user_view', 'admin_user_edit', 'global'
    ],
    'provider' => [
        'provider_dashboard', 'provider_profile', 'provider_appointments',
        'provider_schedule', 'provider_services', 'provider_view_appointment', 'global'
    ],
    'patient' => [
        'patient_dashboard', 'patient_profile', 'patient_appointments',
        'patient_book', 'patient_view_appointment', 'global'
    ],
    'all' => ['auth_login', 'auth_register', 'auth_verify', 'auth_forgot_password', 'auth_reset_password', 'global']
];

// Required appointment-related notifications by user role and operation
$requiredNotifications = [
    // Patient perspective
    'patient' => [
        'booking' => [
            'start_booking' => 'patient_book',
            'service_selected' => 'patient_book',
            'provider_selected' => 'patient_book',
            'time_selected' => 'patient_book',
            'booking_confirmed' => 'patient_book',
            'booking_failed' => 'patient_book'
        ],
        'appointments' => [
            'view_details' => 'patient_appointments',
            'cancel_success' => 'patient_appointments',
            'cancel_failed' => 'patient_appointments',
            'reschedule_success' => 'patient_appointments',
            'reschedule_failed' => 'patient_appointments',
            'reminder' => 'patient_dashboard'
        ]
    ],
    
    // Provider perspective
    'provider' => [
        'appointments' => [
            'new_appointment' => 'provider_dashboard',
            'update_status_success' => 'provider_appointments',
            'update_status_failed' => 'provider_appointments',
            'reschedule_success' => 'provider_appointments',
            'reschedule_failed' => 'provider_appointments',
            'cancel_success' => 'provider_appointments',
            'cancel_failed' => 'provider_appointments'
        ],
        'schedule' => [
            'availability_updated' => 'provider_schedule',
            'availability_update_failed' => 'provider_schedule',
            'time_slot_booked' => 'provider_schedule'
        ]
    ],
    
    // Admin perspective
    'admin' => [
        'appointments' => [
            'add_success' => 'admin_appointments',
            'add_failed' => 'admin_appointments',
            'update_success' => 'admin_appointments',
            'update_failed' => 'admin_appointments',
            'cancel_success' => 'admin_appointments',
            'cancel_failed' => 'admin_appointments',
            'delete_success' => 'admin_appointments',
            'delete_failed' => 'admin_appointments'
        ]
    ]
];

// Function to recursively get all PHP files in a directory
function getAllPhpFiles($dir) {
    $files = [];
    
    if (!is_dir($dir)) {
        echo "Warning: Directory not found: $dir\n";
        return $files;
    }
    
    try {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
    } catch (Exception $e) {
        echo "Error scanning directory: " . $e->getMessage() . "\n";
    }
    
    return $files;
}

// Function to find all set_flash_message calls
function findNotifications($filePath) {
    $content = file_get_contents($filePath);
    $notifications = [];
    
    if ($content === false) {
        echo "Warning: Could not read file: $filePath\n";
        return $notifications;
    }
    
    // Match all set_flash_message calls
    $pattern = "/set_flash_message\(\s*['\"](success|error|warning|info)['\"],\s*(.+?),\s*['\"]([\w_]+)['\"\)]/";
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $type = $match[1]; // success, error, warning, info
        $message = $match[2]; // Message content
        $context = $match[3]; // Context name
        
        // Identify appointment-related notifications
        $isAppointmentRelated = false;
        $operationType = null;
        
        // Check if message contains appointment-related keywords
        $appointmentKeywords = [
            'appointment', 'book', 'schedul', 'cancel', 'time slot', 
            'availab', 'reschedul', 'status'
        ];
        
        foreach ($appointmentKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                $isAppointmentRelated = true;
                break;
            }
        }
        
        // Determine operation type
        if ($isAppointmentRelated) {
            if (stripos($message, 'add') !== false || stripos($message, 'creat') !== false || 
                stripos($message, 'book') !== false || stripos($message, 'new') !== false) {
                $operationType = 'create';
            } elseif (stripos($message, 'updat') !== false || stripos($message, 'edit') !== false || 
                     stripos($message, 'chang') !== false || stripos($message, 'modif') !== false) {
                $operationType = 'update';
            } elseif (stripos($message, 'cancel') !== false || stripos($message, 'delet') !== false || 
                     stripos($message, 'remov') !== false) {
                $operationType = 'cancel_delete';
            } elseif (stripos($message, 'reschedul') !== false) {
                $operationType = 'reschedule';
            } elseif (stripos($message, 'status') !== false) {
                $operationType = 'status';
            } elseif (stripos($message, 'view') !== false || stripos($message, 'detail') !== false) {
                $operationType = 'view';
            }
        }
        
        // Truncate long messages for readability
        if (strlen($message) > 50) {
            $displayMessage = substr($message, 0, 47) . '...';
        } else {
            $displayMessage = $message;
        }
        
        $notifications[] = [
            'type' => $type,
            'message' => $message,
            'display_message' => $displayMessage,
            'context' => $context,
            'file' => basename($filePath),
            'full_path' => $filePath,
            'appointment_related' => $isAppointmentRelated,
            'operation_type' => $operationType
        ];
    }
    
    return $notifications;
}

// Function to verify notification context coverage
function verifyContextCoverage($notifications, $roleToContextMap) {
    $contextCoverage = [];
    $allContexts = [];
    
    // Combine all contexts from the role mapping
    foreach ($roleToContextMap as $contexts) {
        $allContexts = array_merge($allContexts, $contexts);
    }
    $allContexts = array_unique($allContexts);
    
    // Initialize context coverage tracking
    foreach ($allContexts as $context) {
        $contextCoverage[$context] = [
            'success' => 0,
            'error' => 0,
            'warning' => 0,
            'info' => 0,
            'total' => 0
        ];
    }
    
    // Count notifications by context and type
    foreach ($notifications as $notification) {
        $context = $notification['context'];
        $type = $notification['type'];
        
        if (isset($contextCoverage[$context])) {
            $contextCoverage[$context][$type]++;
            $contextCoverage[$context]['total']++;
        } else {
            // Context used in code but not defined in our mapping
            $contextCoverage[$context] = [
                'success' => ($type == 'success' ? 1 : 0),
                'error' => ($type == 'error' ? 1 : 0),
                'warning' => ($type == 'warning' ? 1 : 0),
                'info' => ($type == 'info' ? 1 : 0),
                'total' => 1
            ];
        }
    }
    
    return $contextCoverage;
}

// Function to check for missing appointment notifications
function checkAppointmentNotificationCoverage($allNotifications, $requiredNotifications) {
    $coverage = [];
    $missingNotifications = [];
    
    // Group notifications by context
    $notificationsByContext = [];
    foreach ($allNotifications as $notification) {
        $context = $notification['context'];
        if (!isset($notificationsByContext[$context])) {
            $notificationsByContext[$context] = [];
        }
        $notificationsByContext[$context][] = $notification;
    }
    
    // Check required notifications
    foreach ($requiredNotifications as $role => $categories) {
        foreach ($categories as $category => $operations) {
            foreach ($operations as $operation => $context) {
                $found = false;
                
                // Check if we have notifications for this context
                if (isset($notificationsByContext[$context])) {
                    // For success notifications, look for messages related to the operation
                    foreach ($notificationsByContext[$context] as $notification) {
                        if ($notification['appointment_related']) {
                            // Try to match the operation with notification message
                            $operationWords = explode('_', $operation);
                            $matchCount = 0;
                            
                            foreach ($operationWords as $word) {
                                if (stripos($notification['message'], $word) !== false) {
                                    $matchCount++;
                                }
                            }
                            
                            // If at least half the words match, consider it a match
                            if ($matchCount >= count($operationWords) / 2) {
                                $found = true;
                                break;
                            }
                        }
                    }
                }
                
                // Record coverage
                $key = "$role:$category:$operation";
                $coverage[$key] = $found;
                
                if (!$found) {
                    $missingNotifications[] = [
                        'role' => $role,
                        'category' => $category,
                        'operation' => $operation,
                        'context' => $context
                    ];
                }
            }
        }
    }
    
    return [
        'coverage' => $coverage,
        'missing' => $missingNotifications
    ];
}

// Function to suggest code for missing notifications
function suggestCode($missing) {
    $suggestions = [];
    
    foreach ($missing as $item) {
        $role = $item['role'];
        $category = $item['category'];
        $operation = $item['operation'];
        $context = $item['context'];
        
        // Determine message type and appropriate controller
        $messageType = 'success'; // Default for most operations
        $errorOperation = false;
        
        if (strpos($operation, 'failed') !== false) {
            $messageType = 'error';
            $errorOperation = true;
        }
        
        $controller = $role . '_controller.php';
        $methodName = '';
        
        // Suggest method name based on operation
        $opWords = explode('_', $operation);
        if ($errorOperation) {
            // For failed operations, only use the first parts
            array_pop($opWords);
        }
        
        $methodName = implode('', array_map('ucfirst', $opWords));
        $methodName = lcfirst($methodName);
        
        // Create message based on operation
        $message = str_replace('_', ' ', $operation);
        $message = ucfirst($message);
        
        if ($messageType === 'success') {
            if (strpos($operation, 'success') === false) {
                $message .= ' successful';
            }
        } else {
            if (strpos($operation, 'failed') === false) {
                $message .= ' failed';
            }
        }
        
        $suggestions[] = [
            'role' => $role,
            'category' => $category,
            'operation' => $operation,
            'context' => $context,
            'controller' => $controller,
            'method' => $methodName,
            'code' => "set_flash_message('$messageType', \"$message\", '$context');"
        ];
    }
    
    return $suggestions;
}

// Function to generate test notifications
function generateTestNotifications($contextCoverage, $reportOnly = true) {
    if ($reportOnly) {
        echo "Skipping notification generation (report mode)\n";
        return;
    }
    
    // Include necessary bootstrap file to get access to set_flash_message function
    if (file_exists(__DIR__ . '/bootstrap.php')) {
        require_once __DIR__ . '/bootstrap.php';
    } else {
        echo "Error: bootstrap.php not found. Cannot generate test notifications.\n";
        return;
    }
    
    echo "Generating test notifications for all contexts...\n";
    
    foreach ($contextCoverage as $context => $stats) {
        // Only test contexts that have actual notifications in the codebase
        if ($stats['total'] > 0) {
            // Generate a test for each message type found in this context
            if ($stats['success'] > 0) {
                set_flash_message('success', "TEST: Success notification for $context", $context);
                echo "  - Set success notification for $context\n";
            }
            if ($stats['error'] > 0) {
                set_flash_message('error', "TEST: Error notification for $context", $context);
                echo "  - Set error notification for $context\n";
            }
            if ($stats['warning'] > 0) {
                set_flash_message('warning', "TEST: Warning notification for $context", $context);
                                echo "  - Set warning notification for $context\n";
            }
            if ($stats['info'] > 0) {
                set_flash_message('info', "TEST: Info notification for $context", $context);
                echo "  - Set info notification for $context\n";
            }
        }
    }
    
    echo "Done! Visit the site as different user types to see the notifications.\n";
}

// Main execution
echo "Comprehensive Notification System Verification\n";
echo "============================================\n\n";

if ($reportOnly) {
    echo "RUNNING IN REPORT MODE - No test notifications will be generated\n";
    echo "Set \$reportOnly = false to generate test notifications\n\n";
}

$files = getAllPhpFiles($codebasePath);
$allNotifications = [];

echo "Scanning " . count($files) . " PHP files for notification calls...\n\n";

foreach ($files as $file) {
    $notifications = findNotifications($file);
    if (!empty($notifications)) {
        $allNotifications = array_merge($allNotifications, $notifications);
    }
}

// ===== GENERAL CONTEXT COVERAGE =====
$contextCoverage = verifyContextCoverage($allNotifications, $roleToContextMap);

echo "NOTIFICATION COVERAGE REPORT:\n";
echo "===========================\n\n";

echo "Found " . count($allNotifications) . " notification calls across " . count(array_unique(array_column($allNotifications, 'file'))) . " files.\n\n";

echo "CONTEXT COVERAGE:\n";
echo "-----------------\n";
echo sprintf("%-30s | %-8s | %-8s | %-8s | %-8s | %-8s\n", "Context", "Success", "Error", "Warning", "Info", "Total");
echo str_repeat("-", 80) . "\n";

foreach ($contextCoverage as $context => $stats) {
    echo sprintf("%-30s | %-8d | %-8d | %-8d | %-8d | %-8d\n",
        $context,
        $stats['success'],
        $stats['error'],
        $stats['warning'],
        $stats['info'],
        $stats['total']
    );
}

echo "\nNOTIFICATION BY USER ROLE:\n";
echo "-------------------------\n";

foreach ($roleToContextMap as $role => $contexts) {
    $roleTotals = ['success' => 0, 'error' => 0, 'warning' => 0, 'info' => 0, 'total' => 0];
    
    foreach ($contexts as $context) {
        if (isset($contextCoverage[$context])) {
            $roleTotals['success'] += $contextCoverage[$context]['success'];
            $roleTotals['error'] += $contextCoverage[$context]['error'];
            $roleTotals['warning'] += $contextCoverage[$context]['warning'];
            $roleTotals['info'] += $contextCoverage[$context]['info'];
            $roleTotals['total'] += $contextCoverage[$context]['total'];
        }
    }
    
    echo sprintf("%-10s: %d success, %d error, %d warning, %d info (%d total)\n",
        ucfirst($role),
        $roleTotals['success'],
        $roleTotals['error'],
        $roleTotals['warning'],
        $roleTotals['info'],
        $roleTotals['total']
    );
}

// ===== APPOINTMENT COVERAGE =====
// Filter appointment-related notifications
$appointmentNotifications = array_filter($allNotifications, function($notification) {
    return $notification['appointment_related'];
});

echo "\nAPPOINTMENT NOTIFICATION COVERAGE:\n";
echo "================================\n\n";

echo "Found " . count($appointmentNotifications) . " appointment-related notifications out of " . 
     count($allNotifications) . " total notifications.\n\n";

// Check appointment notification coverage
$coverageResults = checkAppointmentNotificationCoverage($allNotifications, $requiredNotifications);

echo "COVERAGE BY ROLE AND OPERATION:\n";
echo "---------------------------\n";

// Sort by role, category then operation
$sortedCoverage = [];
foreach ($coverageResults['coverage'] as $key => $found) {
    list($role, $category, $operation) = explode(':', $key);
    $sortedCoverage[] = [
        'role' => $role,
        'category' => $category,
        'operation' => $operation,
        'found' => $found
    ];
}

// Group by role and category for display
$currentRole = '';
$currentCategory = '';

usort($sortedCoverage, function($a, $b) {
    if ($a['role'] !== $b['role']) {
        return strcmp($a['role'], $b['role']);
    }
    if ($a['category'] !== $b['category']) {
        return strcmp($a['category'], $b['category']);
    }
    return strcmp($a['operation'], $b['operation']);
});

foreach ($sortedCoverage as $item) {
    if ($item['role'] !== $currentRole) {
        echo "\n" . strtoupper($item['role']) . " ROLE:\n";
        echo str_repeat("-", strlen($item['role']) + 6) . "\n";
        $currentRole = $item['role'];
        $currentCategory = '';
    }
    
    if ($item['category'] !== $currentCategory) {
        echo "  " . ucfirst($item['category']) . ":\n";
        $currentCategory = $item['category'];
    }
    
    $status = $item['found'] ? "✓ Found" : "✗ Missing";
    $statusColor = $item['found'] ? "" : "  <!> "; // Highlight missing
    
    echo "    $statusColor" . str_pad(str_replace('_', ' ', $item['operation']), 30) . " $status\n";
}

// ===== GENERATE SUGGESTIONS =====
// Generate suggestions for missing notifications
if (!empty($coverageResults['missing'])) {
    $suggestions = suggestCode($coverageResults['missing']);
    
    echo "\nMISSING NOTIFICATIONS (" . count($suggestions) . "):\n";
    echo "------------------------\n";
    
    // Group suggestions by controller file
    $suggestionsByController = [];
    foreach ($suggestions as $suggestion) {
        $controller = $suggestion['controller'];
        if (!isset($suggestionsByController[$controller])) {
            $suggestionsByController[$controller] = [];
        }
        $suggestionsByController[$controller][] = $suggestion;
    }
    
    // Display suggestions grouped by controller
    foreach ($suggestionsByController as $controller => $controllerSuggestions) {
        echo "\nIn $controller:\n";
        
        foreach ($controllerSuggestions as $i => $suggestion) {
            echo ($i + 1) . ". " . ucfirst($suggestion['operation']) . 
                 " (" . $suggestion['context'] . "):\n";
            echo "   " . $suggestion['code'] . "\n";
            
            // Suggested placement
            echo "   Suggested placement: In the " . $suggestion['method'] . "() method\n";
        }
    }
    
    echo "\nTo ensure comprehensive notification coverage, add these missing notifications to the appropriate controllers.\n";
} else {
    echo "\nCongratulations! Your system has complete notification coverage for appointments.\n";
}

// ===== OPERATION TYPE STATISTICS =====
// Display statistics on operation types
echo "\nAPPOINTMENT OPERATION COVERAGE:\n";
echo "-----------------------------\n";

$operationTypes = ['create', 'update', 'cancel_delete', 'reschedule', 'status', 'view', null];
$operationNames = [
    'create' => 'Creation/Booking',
    'update' => 'Updates/Edits',
    'cancel_delete' => 'Cancellations/Deletions',
    'reschedule' => 'Rescheduling',
    'status' => 'Status Changes',
    'view' => 'Viewing Details',
    null => 'Unclassified'
];

$operationCounts = [];
foreach ($operationTypes as $type) {
    $operationCounts[$type] = 0;
}

foreach ($appointmentNotifications as $notification) {
    $type = $notification['operation_type'];
    $operationCounts[$type]++;
}

foreach ($operationTypes as $type) {
    $name = $operationNames[$type];
    $count = $operationCounts[$type];
    echo sprintf("%-25s: %d notifications\n", $name, $count);
}

// ===== NOTIFICATION DETAILS =====
echo "\nNOTIFICATION EXAMPLES:\n";
echo "--------------------\n";

// Group by context for better readability
$notificationsByContext = [];
foreach ($allNotifications as $notification) {
    $context = $notification['context'];
    if (!isset($notificationsByContext[$context])) {
        $notificationsByContext[$context] = [];
    }
    $notificationsByContext[$context][] = $notification;
}

// Show a few examples from each context
$contextCount = 0;
foreach ($notificationsByContext as $context => $contextNotifications) {
    echo "\n$context:\n";
    echo str_repeat("-", strlen($context)) . "\n";
    
    for ($i = 0; $i < min(3, count($contextNotifications)); $i++) {
        $notification = $contextNotifications[$i];
        echo "  - [" . $notification['type'] . "] " . $notification['display_message'] . "\n";
    }
    
    if (count($contextNotifications) > 3) {
        echo "  ... and " . (count($contextNotifications) - 3) . " more\n";
    }
    
    $contextCount++;
    if ($contextCount >= 10) {
        echo "\n... and " . (count($notificationsByContext) - 10) . " more contexts\n";
        break;
    }
}

// ===== GENERATE TEST NOTIFICATIONS =====
echo "\nTEST NOTIFICATION GENERATION:\n";
echo "----------------------------\n";
generateTestNotifications($contextCoverage, $reportOnly);

echo "\n=================================================\n";
echo "COMPLETE! This report shows all notification coverage in your system.\n";
if ($reportOnly) {
    echo "To generate test notifications, set \$reportOnly = false and run again.\n";
} else {
    echo "Test notifications have been generated. Log in as different user types to view them.\n";
}
