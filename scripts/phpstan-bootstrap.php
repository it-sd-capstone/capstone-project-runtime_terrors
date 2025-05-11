<?php
// Define the application root directory (adjust path if needed)
define('APP_ROOT', realpath(__DIR__ . '/../'));

// Define paths to key directories
define('VIEW_PATH', APP_ROOT . '/views');
define('MODEL_PATH', APP_ROOT . '/models');
define('CONTROLLER_PATH', APP_ROOT . '/controllers');
define('CONFIG_PATH', APP_ROOT . '/config');
define('CORE_PATH', APP_ROOT . '/core');
define('SQL_PATH', APP_ROOT . '/sql');

// Define any other constants your includes may need
function base_url($path = '') {
    return 'http://localhost/index.php/' . $path;
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="token">';
}