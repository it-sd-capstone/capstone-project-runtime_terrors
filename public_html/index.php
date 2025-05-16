<?php
/** 
 * Front Controller 
 * All requests are routed through this file 
 */

// Set timezone to your local timezone
date_default_timezone_set('America/Chicago'); // Change to your timezone 

// Load the bootstrap
require_once 'bootstrap.php';

// Load helpers
require_once __DIR__ . '/../helpers/flash_helper.php';

// Auth check functions
function is_authenticated() {
    return isset($_SESSION['user_id']) && ($_SESSION['logged_in'] ?? false) === true;
}

function get_user_role() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
}

function require_authentication($redirect_url = 'auth/login') {
    if (!is_authenticated()) {
        set_flash_message('error', 'Please log in to access this page.', 'auth_login');
        header('Location: ' . base_url('index.php/' . $redirect_url));
        exit;
    }
}

function has_role($required_roles) {
    if (!is_authenticated()) {
        return false;
    }
    
    if (is_string($required_roles)) {
        $required_roles = [$required_roles];
    }
    
    return in_array($_SESSION['role'], $required_roles);
}

function require_role($required_roles, $redirect_url = 'home') {
    require_authentication();
    
    if (!has_role($required_roles)) {
        set_flash_message('error', 'You do not have permission to access this page.', 'home');
        header('Location: ' . base_url('index.php/' . $redirect_url));
        exit;
    }
}

// Get the requested path
$path = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? '/'; 

// Remove query string if present
if (($pos = strpos($path, '?')) !== false) {
    $path = substr($path, 0, $pos);
} 

// Remove script name and base directory from path if present
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$base_dir = dirname($script_name);
if ($base_dir != '/' && strpos($path, $base_dir) === 0) {
    $path = substr($path, strlen($base_dir));
} 

// Basic routing
try {
    // Remove leading/trailing slashes and sanitize
    $path = trim($path, '/');
    $path = filter_var($path, FILTER_SANITIZE_URL);
    
    // Default to home if path is empty
    if (empty($path)) {
        $path = 'home';
    }
    
    // Split into segments (controller/action/params)
    $segments = explode('/', $path);
    $controller_name = $segments[0];
    $action = $segments[1] ?? 'index';
    $params = array_slice($segments, 2);
    
    // Define public pages that don't require authentication
    $public_controllers = ['home', 'auth', 'terms', 'privacy', 'test'];
    $public_routes = [
        'auth/login', 
        'auth/register', 
        'auth/forgot_password', 
        'auth/reset_password'
    ];
    
    // Check if current route requires authentication
    $current_route = strtolower($controller_name . '/' . $action);
    $is_public_route = in_array($controller_name, $public_controllers) || 
                      in_array($current_route, $public_routes);
    
    // Enforce authentication for non-public routes
    if (!$is_public_route) {
        require_authentication();
        
        // Role-based access control
        switch ($controller_name) {
            case 'admin':
                require_role('admin');
                break;
                
            case 'provider':
                require_role(['admin', 'provider']);
                break;
                
            case 'patient':
                require_role(['admin', 'patient']);
                break;
                
            // Add other role-specific controllers as needed
        }
    }
    
    // Special case for test files
    if ($controller_name == 'test' && isset($segments[1])) {
        // Allow direct access to test files
        $test_name = $segments[1];
        if ($test_name == 'env') {
            // Handle test_env.php in root
            require_once APP_ROOT . '/test_env.php';
        } else {
            // Handle tests in tests directory
            app_include("tests/{$test_name}.php");
        }
        exit;
    }
    
    // Special case for legal pages (terms and privacy)
    if ($controller_name == 'terms') {
        require_once APP_ROOT . '/controllers/LegalController.php';
        $controller = new LegalController();
        $controller->terms();
        exit;
    }
    
    if ($controller_name == 'privacy') {
        require_once APP_ROOT . '/controllers/LegalController.php';
        $controller = new LegalController();
        $controller->privacy();
        exit;
    }
    
    // Debug controller loading
    $controller_file = APP_ROOT . '/controllers/' . strtolower($controller_name) . '_controller.php';
    error_log("Attempting to load controller file: {$controller_file}");
    error_log("Controller name: {$controller_name}");
    error_log("Class name will be: " . ucfirst($controller_name) . 'Controller');
    
    if (file_exists($controller_file)) {
        error_log("Controller file exists, loading it");
        require_once $controller_file;
        
        // Create controller class name (e.g., 'home' -> 'HomeController')
        $class_name = ucfirst($controller_name) . 'Controller';
        
        // Instantiate the controller
        $controller = new $class_name();
        
        // Call the action method
        if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $params);
        } else {
            throw new Exception("Action '{$action}' not found in controller '{$controller_name}'");
        }
    } else {
        // Check for views in subdirectories
        $possible_view_locations = [
            // Check for views in the subdirectory structure first
            VIEW_PATH . "/{$controller_name}/{$action}.php", // e.g., views/home/index.php
            VIEW_PATH . "/{$controller_name}/index.php",     // e.g., views/home/index.php (default action)
            VIEW_PATH . "/{$controller_name}.php",           // e.g., views/home.php (flat structure)
            VIEW_PATH . "/home/index.php"                    // Default to home page if nothing found
        ];
        
        $view_found = false;
        foreach ($possible_view_locations as $view_file) {
            if (file_exists($view_file)) {
                // View rendering
                require_once $view_file;
                $view_found = true;
                break;
            }
        }
        
        if (!$view_found) {
            throw new Exception("View not found for '{$controller_name}/{$action}'");
        }
    }
} catch (Exception $e) {
    // Handle errors
    header('HTTP/1.1 404 Not Found');
    echo "<h1>Error</h1>";
    echo "<p>{$e->getMessage()}</p>";
    
    // Show available views and controllers for easier debugging
    echo "<h2>Available Views:</h2>";
    echo "<ul>";
    
    // List view directories
    $view_dirs = glob(VIEW_PATH . "/*", GLOB_ONLYDIR);
    foreach ($view_dirs as $dir) {
        $dir_name = basename($dir);
        echo "<li><strong>{$dir_name}/</strong>";
        
        // List files in this directory
        $view_files = glob($dir . "/*.php");
        if (!empty($view_files)) {
            echo "<ul>";
            foreach ($view_files as $file) {
                $file_name = basename($file, ".php");
                echo "<li><a href='/{$dir_name}/{$file_name}'>{$file_name}</a></li>";
            }
            echo "</ul>";
        }
        
        echo "</li>";
    }
    
    // List standalone view files (if any)
    $view_files = glob(VIEW_PATH . "/*.php");
    if (!empty($view_files)) {
        echo "<li><strong>Root Views:</strong><ul>";
        foreach ($view_files as $file) {
            $file_name = basename($file, ".php");
            echo "<li><a href='/{$file_name}'>{$file_name}</a></li>";
        }
        echo "</ul></li>";
    }
    
    echo "</ul>";
    
    echo "<h2>Available Controllers:</h2>";
    echo "<ul>";
    $controllers = glob(CONTROLLER_PATH . "/*_controller.php");
    foreach ($controllers as $controller) {
        $controller_name = basename($controller, "_controller.php");
        echo "<li>{$controller_name}</li>";
    }
    echo "</ul>";
    
    // Debug information
    echo "<h2>Debug Information</h2>";
    echo "<p>Requested Path: {$path}</p>";
    echo "<p>Controller: {$controller_name}</p>";
    echo "<p>Action: {$action}</p>";
    
    if (get_environment() === 'development' || config('debug', false)) {
        echo "<pre>{$e->getTraceAsString()}</pre>";
    }
}
