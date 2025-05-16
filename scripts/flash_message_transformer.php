<?php
/**
 * Flash Message Transformation Script
 *
 * This script transforms traditional session-based flash messages to context-aware flash messages.
 * It scans all controllers and replaces $_SESSION['success'] and $_SESSION['error'] with set_flash_message().
 */

// Configuration
$controllersPath = __DIR__ . '/controllers'; // Path to controllers directory
$dryRun = false; // Set to false to actually modify files

// Context mapping based on redirect URLs
$urlToContextMap = [
    // Admin routes
    'admin/appointments' => 'admin_appointments',
    'admin/appointments/edit' => 'admin_appointment_edit',
    'admin/providers' => 'admin_providers',
    'admin/services' => 'admin_services',
    'admin/users' => 'admin_users',
    'admin/user_view' => 'admin_user_view',
    'admin/user_edit' => 'admin_user_edit',
    'admin/addProvider' => 'admin_add_provider',
    'admin/manageProviderServices' => 'admin_provider_services',
    'admin/viewAvailability' => 'admin_provider_availability',
    'admin/toggleUserStatus' => 'admin_providers',
    'admin/toggleAcceptingPatients' => 'admin_providers',
    
    // Provider routes
    'provider/profile' => 'provider_profile',
    'provider/appointments' => 'provider_appointments',
    'provider/schedule' => 'provider_schedule',
    'provider/services' => 'provider_services',
    'provider/view_appointment' => 'provider_view_appointment',
    'provider/updateProfile' => 'provider_profile',
    'provider/updateAppointment' => 'provider_appointments',
    
    // Patient routes
    'patient/book' => 'patient_book',
    'patient/profile' => 'patient_profile',
    'patient/appointments' => 'patient_appointments',
    'patient/viewAppointment' => 'patient_appointments',
    'patient/updateProfile' => 'patient_profile',
    'patient/selectService' => 'patient_book',
    'patient/selectProvider' => 'patient_book',
    'patient/selectDateTime' => 'patient_book',
    'patient/confirmAppointment' => 'patient_book',
    
    // Auth routes
    'auth' => 'auth_login',
    'auth/register' => 'auth_register',
    'auth/forgot_password' => 'auth_forgot_password',
    'auth/reset_password' => 'auth_reset_password',
    'auth/verify' => 'auth_verify',
    'auth/logout' => 'auth_login',
    
    // Default/fallback
    'home' => 'home'
];

// Function mapping for class methods to contexts
$methodToContextMap = [
    // Admin Controller methods
    'AdminController::index' => 'admin_dashboard',
    'AdminController::users' => 'admin_users',
    'AdminController::viewUser' => 'admin_user_view',
    'AdminController::editUser' => 'admin_user_edit',
    'AdminController::updateUser' => 'admin_users',
    'AdminController::services' => 'admin_services',
    'AdminController::addService' => 'admin_services',
    'AdminController::editService' => 'admin_services',
    'AdminController::deleteService' => 'admin_services',
    'AdminController::appointments' => 'admin_appointments',
    'AdminController::viewAppointment' => 'admin_appointments',
    'AdminController::editAppointment' => 'admin_appointment_edit',
    'AdminController::updateAppointment' => 'admin_appointments',
    'AdminController::providers' => 'admin_providers',
    'AdminController::addProvider' => 'admin_add_provider',
    'AdminController::manageProviderServices' => 'admin_provider_services',
    'AdminController::viewAvailability' => 'admin_provider_availability',
    'AdminController::toggleUserStatus' => 'admin_providers',
    'AdminController::toggleAcceptingPatients' => 'admin_providers',
    
    // Provider Controller methods
    'ProviderController::index' => 'provider_dashboard',
    'ProviderController::appointments' => 'provider_appointments',
    'ProviderController::viewAppointment' => 'provider_view_appointment',
    'ProviderController::updateAppointment' => 'provider_appointments',
    'ProviderController::schedule' => 'provider_schedule',
    'ProviderController::updateSchedule' => 'provider_schedule',
    'ProviderController::services' => 'provider_services',
    'ProviderController::updateServices' => 'provider_services',
    'ProviderController::profile' => 'provider_profile',
    'ProviderController::updateProfile' => 'provider_profile',
    
    // Patient Controller methods
    'PatientController::index' => 'patient_dashboard',
    'PatientController::appointments' => 'patient_appointments',
    'PatientController::viewAppointment' => 'patient_appointments',
    'PatientController::cancelAppointment' => 'patient_appointments',
    'PatientController::book' => 'patient_book',
    'PatientController::selectService' => 'patient_book',
    'PatientController::selectProvider' => 'patient_book',
    'PatientController::selectDateTime' => 'patient_book',
    'PatientController::confirmAppointment' => 'patient_book',
    'PatientController::profile' => 'patient_profile',
    'PatientController::updateProfile' => 'patient_profile',
    
    // Auth Controller methods
    'AuthController::index' => 'auth_login',
    'AuthController::login' => 'auth_login',
    'AuthController::register' => 'auth_register',
    'AuthController::forgotPassword' => 'auth_forgot_password',
    'AuthController::resetPassword' => 'auth_reset_password',
    'AuthController::verify' => 'auth_verify',
    'AuthController::logout' => 'auth_login'
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

// Function to determine context based on the redirect URL
function determineContextFromRedirect($line, $urlToContextMap) {
    foreach ($urlToContextMap as $url => $context) {
        if (strpos($line, $url) !== false) {
            return $context;
        }
    }
    return 'global'; // Default context if no match
}

// Function to determine the current class and method
function getCurrentClassAndMethod($content, $linePosition) {
    // Get all content before the current line
    $codeBefore = substr($content, 0, $linePosition);
    
    // Find the last class declaration
    preg_match('/class\s+(\w+)/is', $codeBefore, $classMatches);
    $className = $classMatches[1] ?? 'Unknown';
    
    // Find the last method declaration
    preg_match_all('/function\s+(\w+)/is', $codeBefore, $methodMatches);
    $methodName = 'Unknown';
    
    if (!empty($methodMatches[1])) {
        $methodName = end($methodMatches[1]);
    }
    
    return $className . '::' . $methodName;
}

// Enhanced function to transform flash messages
function transformFlashMessages($filePath, $urlToContextMap, $methodToContextMap, $dryRun = true) {
    $content = file_get_contents($filePath);
    if ($content === false) {
        echo "Warning: Could not read file: $filePath\n";
        return false;
    }
    
    $lines = explode("\n", $content);
    $modified = false;
    $contextCache = [];
    
    // Process the file line by line
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
        // Check if the line contains a session flash message
        if (preg_match('/\$_SESSION\[(\'|")(success|error|warning|info)(\'|")\]\s*=\s*(.+?);/', $line, $matches)) {
            $messageType = $matches[2]; // 'success', 'error', etc.
            $messageContent = $matches[4]; // Could be a string literal or variable
            
            // Determine if the message is a string literal or variable/expression
            $isStringLiteral = (substr(trim($messageContent), 0, 1) === '"' || substr(trim($messageContent), 0, 1) === "'");
            
            // Look ahead for redirect to determine context
            $context = 'global';
            $lookaheadLimit = 10;  // Look up to 10 lines ahead
            
            for ($j = $i + 1; $j < min($i + 1 + $lookaheadLimit, count($lines)); $j++) {
                if (preg_match('/(redirect|header.*Location).*\(.*[\'"]([^\'"]+)[\'"]/', $lines[$j], $redirectMatches)) {
                    $redirectUrl = $redirectMatches[2];
                    foreach ($urlToContextMap as $url => $ctx) {
                        if (strpos($redirectUrl, $url) !== false) {
                            $context = $ctx;
                            break 2; // Break out of both loops
                        }
                    }
                }
            }
            
            // If no redirect found, try to determine from class and method
            if ($context === 'global') {
                $classMethod = getCurrentClassAndMethod($content, strpos($content, $line));
                $context = $methodToContextMap[$classMethod] ?? 'global';
            }
            
            // Store context for future similar calls in same area
            $contextCache[$messageType] = $context;
            
            // Create the replacement line
            $newLine = "set_flash_message('$messageType', $messageContent, '$context');";
            
            // Special case for complicated strings with HTML
            if (strpos($messageContent, '<strong>') !== false && strpos($messageContent, '</strong>') !== false) {
                // Handle HTML special case more safely
                $modified = true;
                echo "Transformed in " . basename($filePath) . ":\n";
                echo "  From: $line\n";
                echo "  To:   $newLine\n";
                echo "  Context: $context\n\n";
                $lines[$i] = $newLine;
                continue;
            }
            
            // Direct string replacement to avoid regex issues
            $original = "\$_SESSION['$messageType'] = $messageContent;";
            $replacement = "set_flash_message('$messageType', $messageContent, '$context');";
            
            // Try several pattern variations to handle quotes properly
            $patterns = [
                "\$_SESSION['$messageType'] = $messageContent;",
                "\$_SESSION[\"$messageType\"] = $messageContent;",
                "\$_SESSION[\"$messageType\"] = " . trim($messageContent) . ";",
                "\$_SESSION['$messageType'] = " . trim($messageContent) . ";"
            ];
            
            $replaced = false;
            foreach ($patterns as $pattern) {
                if (trim($line) === trim($pattern)) {
                    $lines[$i] = $replacement;
                    $replaced = true;
                    break;
                }
            }
            
            if ($replaced) {
                $modified = true;
                echo "Transformed in " . basename($filePath) . ":\n";
                echo "  From: $line\n";
                echo "  To:   " . $lines[$i] . "\n";
                echo "  Context: $context\n\n";
            } else {
                // If direct replacement failed, try with a safer generic regex
                $safePattern = '/\$_SESSION\[(\'|")(success|error|warning|info)(\'|")\]\s*=\s*(.+?);/';
                $safeReplacement = "set_flash_message('$2', $4, '$context');";
                
                $newLine = preg_replace($safePattern, $safeReplacement, $line);
                
                if ($newLine !== $line) {
                    $lines[$i] = $newLine;
                    $modified = true;
                    echo "Transformed in " . basename($filePath) . " (using regex):\n";
                    echo "  From: $line\n";
                    echo "  To:   " . $lines[$i] . "\n";
                    echo "  Context: $context\n\n";
                }
            }
        }
        
        // Handle unset($_SESSION['success']) or similar patterns
        if (preg_match('/unset\(\$_SESSION\[(\'|")(success|error|warning|info)(\'|")\]\);/', $line)) {
            // Simply remove these lines as they're not needed with the new system
            $lines[$i] = '// ' . $line . ' // Removed by transformer - no longer needed with context-aware flash messages';
            $modified = true;
            echo "Commented out unset in " . basename($filePath) . ":\n";
            echo "  Line: $line\n\n";
        }
        
        // Also handle isset checks for flash messages that might need to be removed or transformed
        if (preg_match('/if\s*\(\s*isset\s*\(\s*\$_SESSION\[(\'|")(success|error|warning|info)(\'|")\]\s*\)\s*\)\s*(?:unset|{)/', $line, $matches)) {
            $messageType = $matches[2];
            $lines[$i] = '// ' . $line . ' // Modified by transformer - isset check not needed with context-aware flash messages';
            $modified = true;
            echo "Commented out isset check in " . basename($filePath) . ":\n";
            echo "  Line: $line\n\n";
        }
    }
    
    // Write changes back to the file if modified and not in dry run mode
    if ($modified && !$dryRun) {
                $result = file_put_contents($filePath, implode("\n", $lines));
        if ($result === false) {
            echo "Error: Failed to write to file: $filePath\n";
        } else {
            echo "Updated file: $filePath\n";
        }
    } elseif ($modified) {
        echo "Would update file (dry run): $filePath\n";
    }
    
    return $modified;
}

// Main execution
echo "Flash Message Transformation Script\n";
echo "=================================\n\n";

if ($dryRun) {
    echo "RUNNING IN DRY RUN MODE - No files will be modified\n";
    echo "Set \$dryRun = false to apply changes\n\n";
}

$files = getAllPhpFiles($controllersPath);
$transformedCount = 0;

foreach ($files as $file) {
    $transformed = transformFlashMessages($file, $urlToContextMap, $methodToContextMap, $dryRun);
    if ($transformed) {
        $transformedCount++;
    }
}

echo "\nSummary:\n";
echo "- " . count($files) . " PHP files scanned\n";
echo "- " . $transformedCount . " files " . ($dryRun ? "would be" : "were") . " transformed\n";

if ($dryRun) {
    echo "\nTo apply these changes, set \$dryRun = false and run the script again.\n";
}
