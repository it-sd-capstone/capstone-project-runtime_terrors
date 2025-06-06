<?php
/**
 * Application Bootstrap
 * Central access point to resources outside public_html
 */

// Define key application paths - adjusted for your structure
define('APP_ROOT', dirname(__DIR__));
define('CONFIG_PATH', APP_ROOT . '/config');
define('CONTROLLER_PATH', APP_ROOT . '/controllers');
define('MODEL_PATH', APP_ROOT . '/models');
define('VIEW_PATH', APP_ROOT . '/views');
define('CORE_PATH', APP_ROOT . '/core');
define('ROUTES_PATH', APP_ROOT . '/routes');
define('SQL_PATH', APP_ROOT . '/sql');
define('TESTS_PATH', APP_ROOT . '/tests');

// Error reporting - comment out in production
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once APP_ROOT . "/public_html/includes/security_headers.php";
// Set secure cookie parameters before starting the session
$currentCookieParams = session_get_cookie_params();
session_set_cookie_params(
    $currentCookieParams["lifetime"],
    $currentCookieParams["path"],
    $currentCookieParams["domain"],
    isset($_SERVER['HTTPS']), // Set secure flag automatically based on HTTPS
    true // Set httpOnly flag to true
);

// Now start the session
session_start();

// Load .env file - ADD THIS SECTION
$env_file = APP_ROOT . '/.env';
if (file_exists($env_file)) {
    // error_log("Loading environment variables from: $env_file");
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        error_log("Failed to read .env file");
    } else {
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                error_log("Loaded env var: $key = " . substr($value, 0, 5) . "...");
            }
        }
    }
}
// Load helper functions
require_once APP_ROOT . '/core/helpers.php';
// Load environment configuration
require_once CONFIG_PATH . '/environment.php';
// Load helper functions
require_once CORE_PATH . '/helpers.php';
$env = Environment::detect();

// Load environment-specific configuration
$env_config = require_once CONFIG_PATH . '/environments/' . $env . '.php';

// Database connection
require_once CONFIG_PATH . '/connection_bridge.php';

/**
 * Safely include a file from outside public_html
 * @param string $path Path relative to APP_ROOT
 * @return mixed Result of the include
 */
function app_include($path) {
    $full_path = APP_ROOT . '/' . ltrim($path, '/');
    if (!file_exists($full_path)) {
        throw new Exception("File not found: {$path}");
    }
    return require_once $full_path;
}

/**
 * Get a configuration value
 * @param string $key Configuration key
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value
 */
function config($key, $default = null) {
    global $env_config;
    return $env_config[$key] ?? $default;
}

/**
 * Get the database connection
 * @return mysqli Database connection object
 */
function get_db() {
    static $db = null;
    
    // Only create the connection once
    if ($db === null) {
        // Use the constants defined in connection_bridge.php
        if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($db->connect_error) {
                throw new Exception("Database connection failed: " . $db->connect_error);
            }
            
            // Set character set
            $db->set_charset('utf8mb4');
        } else {
            throw new Exception("Database constants not defined");
        }
    }
    
    return $db;
}


/**
 * Get the current environment
 * @return string Environment name
 */

function get_environment() {
    global $env;
    return isset($env) ? $env : 'unknown';
}

// Register an autoloader for classes
spl_autoload_register(function($class_name) {
    // Try to load from models directory
    $model_path = MODEL_PATH . '/' . $class_name . '.php';
    if (file_exists($model_path)) {
        require_once $model_path;
        return true;
    }
    
    // Try to load from controllers directory
    $controller_path = CONTROLLER_PATH . '/' . $class_name . '.php';
    if (file_exists($controller_path)) {
        require_once $controller_path;
        return true;
    }
    
    return false;
});

// Return true to indicate bootstrap loaded successfully
return true;
