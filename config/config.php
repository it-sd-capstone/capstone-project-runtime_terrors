<?php
// Define the environment if not already defined
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

define('SEND_EMAILS', true);

// Base configuration (common to all environments)
return [
    'app_name' => 'Appointment System',
    'debug' => false,
    'timezone' => 'America/Chicago',
    
    // Include empty database settings (will be overridden by environment-specific)
    'db_host' => '',
    'db_user' => '',
    'db_pass' => '',
    'db_name' => ''
];