<?php
// Base configuration (common to all environments)
define('VIEW_PATH', __DIR__ . '/views');
define('APP_NAME', 'Appointment System');
define('DEBUG_MODE', false);
define('TIMEZONE', 'America/Chicago');

// Database configuration (empty by default, overridden per environment)
define('DB_HOST', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', '');