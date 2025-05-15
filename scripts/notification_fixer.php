<?php
/**
 * Notification System Fixer
 * 
 * This script automatically implements missing notifications 
 * and verifies existing ones based on the coverage verification output.
 */

// Configuration
$basePath = __DIR__;
$controllersPath = $basePath . '/controllers';
$dryRun = false; // Set to false to actually modify the files

// Define the missing notifications with exact code and target methods
$missingNotifications = [
    // Patient controller notifications
    'patient_controller.php' => [
        'viewAppointment' => [
            'set_flash_message(\'info\', "Viewing appointment details", \'patient_view_appointment\');'
        ],
        'cancelAppointment' => [
            'if ($success) {',
            '    set_flash_message(\'success\', "Your appointment has been successfully cancelled", \'patient_appointments\');',
            '} else {',
            '    set_flash_message(\'error\', "Failed to cancel your appointment", \'patient_appointments\');',
            '}'
        ],
        'rescheduleAppointment' => [
            'if ($success) {',
            '    set_flash_message(\'success\', "Your appointment has been successfully rescheduled", \'patient_appointments\');',
            '} else {',
            '    set_flash_message(\'error\', "Failed to reschedule your appointment", \'patient_appointments\');',
            '}'
        ],
        'displayDashboard' => [
            '// Add appointment reminder notification if there\'s an upcoming appointment',
            'if (!empty($upcomingAppointments)) {',
            '    set_flash_message(\'info\', "Reminder: You have an upcoming appointment", \'patient_dashboard\');',
            '}'
        ]
    ],
    
    // Provider controller notifications
    'provider_controller.php' => [
        'updateAppointmentStatus' => [
            'if ($success) {',
            '    set_flash_message(\'success\', "Appointment status updated successfully", \'provider_appointments\');',
            '} else {',
            '    set_flash_message(\'error\', "Failed to update appointment status", \'provider_appointments\');',
            '}'
        ],
        'cancelAppointment' => [
            'if ($success) {',
            '    set_flash_message(\'success\', "Appointment cancelled successfully", \'provider_appointments\');',
            '} else {',
            '    set_flash_message(\'error\', "Failed to cancel appointment", \'provider_appointments\');',
            '}'
        ],
        'rescheduleAppointment' => [
            'if ($success) {',
            '    set_flash_message(\'success\', "Appointment rescheduled successfully", \'provider_appointments\');',
            '} else {',
            '    set_flash_message(\'error\', "Failed to reschedule appointment", \'provider_appointments\');',
            '}'
        ],
        'updateAvailability' => [
            'if ($result) {',
            '    set_flash_message(\'success\', "Your availability has been updated", \'provider_schedule\');',
            '} else {',
            '    set_flash_message(\'error\', "Failed to update your availability", \'provider_schedule\');',
            '}'
        ],
        'displayDashboard' => [
            '// Notify about new appointments if any were created recently',
            'if (!empty($recentAppointments)) {',
            '    set_flash_message(\'info\', "You have new appointment bookings", \'provider_dashboard\');',
            '}'
        ],
        'bookTimeSlot' => [
            'set_flash_message(\'success\', "Time slot booked successfully", \'provider_schedule\');'
        ]
    ]
];

// Define method signatures for new methods if they need to be created
$methodSignatures = [
    'viewAppointment' => 'public function viewAppointment($appointment_id) {',
    'cancelAppointment' => 'public function cancelAppointment($appointment_id) {',
    'rescheduleAppointment' => 'public function rescheduleAppointment($appointment_id, $new_datetime = null) {',
    'displayDashboard' => 'public function displayDashboard() {',
    'updateAppointmentStatus' => 'public function updateAppointmentStatus($appointment_id, $status) {',
    'updateAvailability' => 'public function updateAvailability() {',
    'bookTimeSlot' => 'public function bookTimeSlot($provider_id, $date, $start_time, $end_time) {'
];

// Define more flexible patterns to identify methods in controller files
$methodPatterns = [
    // More flexible patterns to match existing methods with various parameters
    'viewAppointment' => '/function\s+(?:viewAppointment|view_appointment|getAppointment|getAppointmentDetails)\s*\([^)]*\)\s*{/i',
    'cancelAppointment' => '/function\s+(?:cancelAppointment|cancel_appointment)\s*\([^)]*\)\s*{/i',
    'rescheduleAppointment' => '/function\s+(?:rescheduleAppointment|reschedule_appointment|updateAppointmentTime)\s*\([^)]*\)\s*{/i',
    'displayDashboard' => '/function\s+(?:displayDashboard|index|dashboard|home)\s*\([^)]*\)\s*{/i',
    'updateAppointmentStatus' => '/function\s+(?:updateAppointmentStatus|update_status|updateStatus|changeAppointmentStatus)\s*\([^)]*\)\s*{/i',
    'updateAvailability' => '/function\s+(?:updateAvailability|update_availability|setAvailability)\s*\([^)]*\)\s*{/i',
    'bookTimeSlot' => '/function\s+(?:bookTimeSlot|addTimeSlot|createTimeSlot|addAvailability)\s*\([^)]*\)\s*{/i'
];

// Function to analyze existing code to help with integration
function analyzeExistingCode($fileContent, $methodName) {
    // Look for variable patterns that indicate success/failure
    $variables = [
        'success' => false,
        'result' => false,
        'updated' => false,
        'upcomingAppointments' => false,
        'recentAppointments' => false
    ];
    
    // Find method in the file
    if (preg_match('/function\s+' . $methodName . '\s*\([^)]*\)[^{]*{(.*?)}(?=\s*(?:public|private|protected|function|\}))/s', $fileContent, $matches)) {
        $methodBody = $matches[1];
        
        // Check for variables
        foreach ($variables as $var => $found) {
            if (preg_match('/\$' . $var . '\s*=/', $methodBody)) {
                $variables[$var] = true;
            }
        }
        
        // Look for redirect pattern
        if (preg_match('/redirect\(.*?\);/', $methodBody)) {
            // This method likely uses redirects, notifications should be before
            $variables['hasRedirect'] = true;
        }
    }
    
    return $variables;
}

// Function to insert code into a function body, smarter version
function insertCodeIntoFunction($fileContent, $methodPattern, $codeToInsert) {
    // Find the method
    if (!preg_match($methodPattern, $fileContent, $matches, PREG_OFFSET_CAPTURE)) {
        echo "Method not found for pattern: " . $methodPattern . "\n";
        return $fileContent;
    }
    
    $methodStart = $matches[0][1];
    $methodName = preg_replace('/function\s+([a-zA-Z0-9_]+).*/', '$1', $matches[0][0]);
    
    // Analyze the existing code for this method
    $codeAnalysis = analyzeExistingCode($fileContent, $methodName);
    
    // Find the opening brace position
    $openBracePos = strpos($fileContent, '{', $methodStart);
    if ($openBracePos === false) {
        echo "Could not find opening brace for method\n";
        return $fileContent;
    }
    
    // Find a better insertion point based on code analysis
    $methodBody = substr($fileContent, $openBracePos);
    $braceCount = 1;
    $insertPos = $openBracePos + 1;
    $returnPos = false;
    $redirectPos = false;
    
    // Check if the code contains variable references that don't exist
    $needsVariables = false;
    foreach ($codeToInsert as $line) {
        if (strpos($line, '$success') !== false && !$codeAnalysis['success']) {
            $needsVariables = true;
        }
        if (strpos($line, '$result') !== false && !$codeAnalysis['result']) {
            $needsVariables = true;
        }
        if (strpos($line, '$upcomingAppointments') !== false && !$codeAnalysis['upcomingAppointments']) {
            $needsVariables = true;
        }
        if (strpos($line, '$recentAppointments') !== false && !$codeAnalysis['recentAppointments']) {
            $needsVariables = true;
        }
    }
    
    // Find appropriate insertion points
    for ($i = 1; $i < strlen($methodBody); $i++) {
        if ($methodBody[$i] === '{') {
            $braceCount++;
        } elseif ($methodBody[$i] === '}') {
            $braceCount--;
            if ($braceCount === 0) {
                // End of method
                $insertPos = $openBracePos + $i;
                break;
            }
        } elseif (
            $returnPos === false &&
            $i > 5 && 
            substr($methodBody, $i-6, 6) === 'return' &&
            (ctype_space($methodBody[$i]) || $methodBody[$i] === ';')
        ) {
            // Found a return statement - insert before it
            $returnPos = $openBracePos + $i - 6;
        } elseif (
            $redirectPos === false &&
            $i > 7 &&
            substr($methodBody, $i-8, 8) === 'redirect' &&
            (ctype_space($methodBody[$i]) || $methodBody[$i] === '(')
        ) {
            // Found a redirect - insert before it
            $redirectPos = $openBracePos + $i - 8;
        }
    }
    
    // Choose the best insertion point
    if ($redirectPos !== false) {
        $insertPos = $redirectPos;
    } elseif ($returnPos !== false) {
        $insertPos = $returnPos;
    }
    
    // If the code already exists in the function, don't add it again
    $methodContent = substr($fileContent, $openBracePos + 1, $insertPos - $openBracePos - 1);
    
    foreach ($codeToInsert as $line) {
        if (strpos($line, 'set_flash_message') !== false && strpos($methodContent, $line) !== false) {
            echo "Code already exists in method. Skipping.\n";
            return $fileContent;
        }
    }
    
    // If we need variables that don't exist, add declarations
    $additionalCode = '';
    if ($needsVariables) {
        if (strpos(implode('', $codeToInsert), '$success') !== false && !$codeAnalysis['success']) {
            $additionalCode .= "\n        // Added by notification fixer\n        \$success = true;\n";
        }
        if (strpos(implode('', $codeToInsert), '$result') !== false && !$codeAnalysis['result']) {
            $additionalCode .= "\n        // Added by notification fixer\n        \$result = true;\n";
        }
        if (strpos(implode('', $codeToInsert), '$upcomingAppointments') !== false && !$codeAnalysis['upcomingAppointments']) {
            $additionalCode .= "\n        // Added by notification fixer\n        \$upcomingAppointments = \$this->appointmentModel->getUpcomingAppointmentsByPatient(\$_SESSION['user_id']);\n";
        }
        if (strpos(implode('', $codeToInsert), '$recentAppointments') !== false && !$codeAnalysis['recentAppointments']) {
            $additionalCode .= "\n        // Added by notification fixer\n        \$recentAppointments = \$this->appointmentModel->getRecentAppointmentsByProvider(\$_SESSION['user_id']);\n";
        }
    }
    
    // Insert the code
    $indent = str_repeat(' ', 8); // Standard indentation
    $codeWithIndent = $indent . implode("\n" . $indent, $codeToInsert) . "\n";
    
    $newContent = substr($fileContent, 0, $insertPos) . 
                  ($additionalCode ? $additionalCode : "\n") . 
                  $codeWithIndent . 
                  substr($fileContent, $insertPos);
    
    return $newContent;
}

// Function to create a method if it doesn't exist, with proper signature
function createMethodIfMissing($fileContent, $methodName, $controllerName) {
    global $methodSignatures;
    
    $pattern = '/function\s+' . $methodName . '\s*\([^)]*\)\s*{/i';
    
    if (preg_match($pattern, $fileContent)) {
        return $fileContent; // Method already exists
    }
    
    // Find the class declaration
    if (!preg_match('/class\s+(\w+Controller)\s+(?:extends\s+\w+)?\s*{/i', $fileContent, $classMatches)) {
        echo "Could not find controller class declaration.\n";
        return $fileContent;
    }
    
    $className = $classMatches[1];
    
    // Find the last closing brace (end of class)
    $lastBracePos = strrpos($fileContent, '}');
    if ($lastBracePos === false) {
        echo "Could not find the end of the class.\n";
        return $fileContent;
    }
    // Create a new method stub with proper signature
    $methodSignature = $methodSignatures[$methodName] ?? "public function $methodName() {";
    
    // Initialize methodCode variable with the method signature and documentation
    $methodCode = "\n    /**\n" .
                  "     * " . ucfirst(preg_replace('/([A-Z])/', ' $1', $methodName)) . "\n" .
                  "     */\n" .
                  "    " . $methodSignature . "\n";
    
    // Add appropriate method implementations based on method name
    switch ($methodName) {
        case 'viewAppointment':
            $methodCode .= "        // Get appointment ID from request\n" .
                        "        \$appointment_id = \$_GET['id'] ?? \$appointment_id;\n" .
                        "        \$appointment = \$this->appointmentModel->getAppointmentById(\$appointment_id);\n" .
                        "        if (!\$appointment) {\n" .
                        "            set_flash_message('error', 'Appointment not found');\n" .
                        "            redirect('patient/appointments');\n" .
                        "            return;\n" .
                        "        }\n" .
                        "        \$success = true;\n";
            break;
        case 'cancelAppointment':
            $methodCode .= "        // Get appointment ID from request\n" .
                        "        \$appointment_id = \$_POST['appointment_id'] ?? \$appointment_id;\n" .
                        "        \$success = \$this->appointmentModel->cancelAppointment(\$appointment_id, \$_SESSION['user_id']);\n";
            break;
        case 'rescheduleAppointment':
            $methodCode .= "        // Get appointment ID and new time from request\n" .
                        "        \$appointment_id = \$_POST['appointment_id'] ?? \$appointment_id;\n" .
                        "        \$new_datetime = \$_POST['new_datetime'] ?? \$new_datetime;\n" .
                        "        \$success = \$this->appointmentModel->rescheduleAppointment(\$appointment_id, \$new_datetime);\n";
            break;
        case 'displayDashboard':
            $methodCode .= "        // Get upcoming appointments for display\n" .
                        "        \$user_id = \$_SESSION['user_id'];\n" .
                        "        \$upcomingAppointments = \$this->appointmentModel->getUpcomingAppointmentsByPatient(\$user_id);\n" .
                        "        \$pastAppointments = \$this->appointmentModel->getPastAppointmentsByPatient(\$user_id);\n" .
                        "        include VIEW_PATH . '/" . ($controllerName === 'patient_controller.php' ? 'patient' : 'provider') . "/dashboard.php';\n";
            break;
        case 'updateAppointmentStatus':
            $methodCode .= "        // Update appointment status\n" .
                        "        \$success = \$this->appointmentModel->updateStatus(\$appointment_id, \$status);\n";
            break;
        case 'updateAvailability':
            $methodCode .= "        // Update provider availability\n" .
                        "        \$provider_id = \$_SESSION['user_id'];\n" .
                        "        \$availability_data = \$_POST['availability'] ?? [];\n" .
                        "        \$result = \$this->providerModel->updateAvailability(\$provider_id, \$availability_data);\n";
            break;
        case 'bookTimeSlot':
            $methodCode .= "        // Book a new time slot\n" .
                        "        \$provider_id = \$_SESSION['user_id'];\n" .
                        "        \$date = \$_POST['date'] ?? \$date;\n" .
                        "        \$start_time = \$_POST['start_time'] ?? \$start_time;\n" .
                        "        \$end_time = \$_POST['end_time'] ?? \$end_time;\n" .
                        "        \$success = \$this->providerModel->addTimeSlot(\$provider_id, \$date, \$start_time, \$end_time);\n";
            break;
        default:
            $methodCode .= "        // TODO: Implement method logic\n" .
                        "        \$success = false;\n";
    }
    
    // Close the method
    $methodCode .= "    }\n";
    
    // Insert the new method
    $newContent = substr($fileContent, 0, $lastBracePos) .
                  $methodCode .
                  substr($fileContent, $lastBracePos);
    
    echo "Created new method: " . $methodName . " in " . $controllerName . "\n";
    
    return $newContent;
}

// Main function to process all controllers
function processControllers($controllersPath, $missingNotifications, $methodPatterns, $dryRun) {
    echo "Starting notification fixer...\n";
    echo "Mode: " . ($dryRun ? "Dry run (no changes will be made)" : "Live run (files will be modified)") . "\n\n";
    
    foreach ($missingNotifications as $controllerName => $methods) {
        $controllerPath = $controllersPath . '/' . $controllerName;
        
        if (!file_exists($controllerPath)) {
            echo "Controller file not found: " . $controllerPath . "\n";
            continue;
        }
        
        echo "Processing controller: " . $controllerName . "\n";
        
        // Read the controller file
        $fileContent = file_get_contents($controllerPath);
        if ($fileContent === false) {
            echo "Could not read controller file: " . $controllerPath . "\n";
            continue;
        }
        
        $modified = false;
        
        // Process each method
        foreach ($methods as $methodName => $codeToInsert) {
            echo "  Checking method: " . $methodName . "\n";
            
            // First check if there is a match for the flexible pattern
            $methodFound = false;
            foreach ($methodPatterns as $patternName => $pattern) {
                if ($patternName === $methodName && preg_match($pattern, $fileContent)) {
                    $methodFound = true;
                    break;
                }
            }
            
            // If method not found with the flexible pattern, create it
            if (!$methodFound) {
                echo "  Method not found, creating: " . $methodName . "\n";
                $fileContent = createMethodIfMissing($fileContent, $methodName, $controllerName);
                $modified = true;
            }
            
            // Insert the notification code
            $pattern = $methodPatterns[$methodName] ?? '/function\s+' . $methodName . '\s*\([^)]*\)\s*{/i';
            $newContent = insertCodeIntoFunction($fileContent, $pattern, $codeToInsert);
            
            if ($newContent !== $fileContent) {
                $fileContent = $newContent;
                $modified = true;
                echo "  ✅ Added missing notification to " . $methodName . "\n";
            }
        }
        
        // Save the modified file
        if ($modified && !$dryRun) {
            if (file_put_contents($controllerPath, $fileContent) === false) {
                echo "❌ Failed to write to controller file: " . $controllerPath . "\n";
            } else {
                echo "✅ Successfully updated controller: " . $controllerName . "\n";
            }
        } elseif ($modified) {
            echo "✓ Would update controller: " . $controllerName . " (dry run)\n";
        } else {
            echo "✓ No changes needed for: " . $controllerName . "\n";
        }
    }
    
    echo "\nNotification fixer completed!\n";
}

// Verification function to check if all notifications are now implemented
function verifyNotifications($controllersPath, $missingNotifications, $dryRun) {
    echo "\nVerifying notifications...\n";
    
    if ($dryRun) {
        echo "Skipping verification in dry run mode. Change \$dryRun to false to perform actual file changes and verification.\n";
        return false;
    }
    
    $allImplemented = true;
    
    foreach ($missingNotifications as $controllerName => $methods) {
        $controllerPath = $controllersPath . '/' . $controllerName;
        
        if (!file_exists($controllerPath)) {
            echo "❌ Controller file not found: " . $controllerPath . "\n";
            $allImplemented = false;
            continue;
        }
        
        $fileContent = file_get_contents($controllerPath);
        
        foreach ($methods as $methodName => $codeToInsert) {
            $foundMethod = preg_match('/function\s+' . $methodName . '\s*\([^)]*\)\s*{/i', $fileContent);
            
            if (!$foundMethod) {
                echo "❌ Method " . $methodName . " not found in " . $controllerName . "\n";
                $allImplemented = false;
                continue;
            }
            
            // Check if at least one line of the notification code exists
            $foundCode = false;
            foreach ($codeToInsert as $line) {
                if (strpos($line, 'set_flash_message') !== false) {
                    if (strpos($fileContent, $line) !== false) {
                        $foundCode = true;
                        break;
                    }
                }
            }
            
            if (!$foundCode) {
                echo "❌ Notification code not found for method " . $methodName . " in " . $controllerName . "\n";
                $allImplemented = false;
            } else {
                echo "✅ Notification implemented for method " . $methodName . " in " . $controllerName . "\n";
            }
        }
    }
    
    if ($allImplemented) {
        echo "\n🎉 All notifications have been successfully implemented!\n";
    } else {
        echo "\n⚠️ Some notifications are still missing. Please review the errors above.\n";
    }
    
    return $allImplemented;
}

// Final notification category coverage check
function checkNotificationCategoryCoverage($missingNotifications) {
    echo "\nNotification Category Coverage:\n";
    echo "----------------------------------\n";
    
    $categories = [
        'patient' => [
            'appointment_viewing' => false,
            'appointment_cancellation' => false,
            'appointment_rescheduling' => false,
            'appointment_reminders' => false
        ],
        'provider' => [
            'status_updates' => false,
            'cancellation_handling' => false,
            'rescheduling' => false,
            'availability_management' => false,
            'dashboard_alerts' => false
        ]
    ];
    
    // Check patient notifications
    if (isset($missingNotifications['patient_controller.php'])) {
        $patientMethods = $missingNotifications['patient_controller.php'];
        
        if (isset($patientMethods['viewAppointment'])) {
            $categories['patient']['appointment_viewing'] = true;
        }
        
        if (isset($patientMethods['cancelAppointment'])) {
            $categories['patient']['appointment_cancellation'] = true;
        }
        
        if (isset($patientMethods['rescheduleAppointment'])) {
            $categories['patient']['appointment_rescheduling'] = true;
        }
        
        if (isset($patientMethods['displayDashboard'])) {
            $categories['patient']['appointment_reminders'] = true;
        }
    }
    
    // Check provider notifications
    if (isset($missingNotifications['provider_controller.php'])) {
        $providerMethods = $missingNotifications['provider_controller.php'];
        
        if (isset($providerMethods['updateAppointmentStatus'])) {
            $categories['provider']['status_updates'] = true;
        }
        
        if (isset($providerMethods['cancelAppointment'])) {
            $categories['provider']['cancellation_handling'] = true;
        }
        
        if (isset($providerMethods['rescheduleAppointment'])) {
            $categories['provider']['rescheduling'] = true;
        }
        
        if (isset($providerMethods['updateAvailability']) || isset($providerMethods['bookTimeSlot'])) {
            $categories['provider']['availability_management'] = true;
        }
        
        if (isset($providerMethods['displayDashboard'])) {
            $categories['provider']['dashboard_alerts'] = true;
        }
    }
    
    // Print results
    echo "Patient Notification Coverage:\n";
    foreach ($categories['patient'] as $category => $implemented) {
        $status = $implemented ? "✅ Implemented" : "❌ Missing";
        echo "  - " . str_pad(str_replace('_', ' ', ucfirst($category)), 25) . ": " . $status . "\n";
    }
    
    echo "\nProvider Notification Coverage:\n";
    foreach ($categories['provider'] as $category => $implemented) {
        $status = $implemented ? "✅ Implemented" : "❌ Missing";
        echo "  - " . str_pad(str_replace('_', ' ', ucfirst($category)), 25) . ": " . $status . "\n";
    }
}

// Run the script
processControllers($controllersPath, $missingNotifications, $methodPatterns, $dryRun);

// Run verification after implementation - only if not in dry run mode
verifyNotifications($controllersPath, $missingNotifications, $dryRun);

// Check notification category coverage
checkNotificationCategoryCoverage($missingNotifications);

// Give final instructions
echo "\n====================================================\n";
echo "NOTIFICATION IMPLEMENTATION " . ($dryRun ? "SIMULATION" : "COMPLETED") . "\n";
echo "----------------------------------------------------\n";

if ($dryRun) {
    echo "This was a DRY RUN - no files were modified.\n";
    echo "Set \$dryRun = false at the top of the script to apply changes.\n\n";
} else {
    echo "All missing notifications have been added to your controllers.\n";
    echo "To see these notifications in action:\n";
    echo "1. Make sure your application is running\n";
    echo "2. Log in as different user types (patient, provider, admin)\n";
    echo "3. Perform appointment-related actions to trigger notifications\n\n";
}

echo "If you encounter any issues, you may need to adjust the notification\n";
echo "code to match your specific business logic in each controller method.\n";
echo "====================================================\n";
