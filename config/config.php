<?php
// Base configuration (common to all environments)
define('VIEW_PATH', __DIR__ . '/views');
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
