<?php
// Include the Environment class
require_once __DIR__ . '/environment.php';

// Get database settings from environment configuration
$env_db_host = Environment::get('db_host', 'localhost');
$env_db_user = Environment::get('db_user', 'root');
$env_db_pass = Environment::get('db_pass', '');
$env_db_name = Environment::get('db_name', 'kholley_appointment_system');

// Define constants if they aren't already defined
if (!defined('DB_HOST')) define('DB_HOST', $env_db_host);
if (!defined('DB_USER')) define('DB_USER', $env_db_user);
if (!defined('DB_PASS')) define('DB_PASS', $env_db_pass);
if (!defined('DB_NAME')) define('DB_NAME', $env_db_name);
