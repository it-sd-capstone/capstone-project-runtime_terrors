<?php
/**
 * Application-wide helper functions
 */

/**
 * CSRF Protection System
 * 
 * This application implements Cross-Site Request Forgery (CSRF) protection through
 * token-based validation. All forms include a hidden CSRF token field, and all 
 * POST requests are validated against the stored token in the session.
 * 
 * The implementation includes:
 * - Token generation with 32 bytes of entropy
 * - Constant-time comparison for validation (prevents timing attacks)
 * - Token expiration after 1 hour
 * - Automatic redirection on failed validation
 */

/**
 * Get the base URL for the application
 *
 * @param string $path Path to append to the base URL
 * @return string The complete URL
 */
function base_url($path = '') {
    // Get the protocol
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    
    // Handle CLI environment for PHPStan
    if (php_sapi_name() === 'cli') {
        return 'http://localhost/' . ltrim($path, '/');
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Get the script name and its directory
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_dir = dirname($script_name);
    
    // Normalize base directory path
    if ($base_dir == '/' || $base_dir == '\\') {
        $base_dir = '';
    }
    
    // Build the base URL
    $base_url = "{$protocol}://{$host}{$base_dir}";
    
    // Remove trailing slash from base URL and leading slash from path
    $base_url = rtrim($base_url, '/');
    $path = ltrim($path, '/');
    
    // Combine and return
    return $path ? "{$base_url}/{$path}" : $base_url;
}

/**
 * Generate a CSRF token and store it in the session
 *
 * @return string The generated CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    // Regenerate token if it's older than 1 hour
    if (time() - $_SESSION['csrf_token_time'] > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token against the one stored in session
 *
 * @param string $token The token to validate
 * @return bool Whether the token is valid
 */
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Use hash_equals for constant-time comparison to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate an HTML hidden input field containing the CSRF token
 *
 * @return string HTML hidden input with CSRF token
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verify CSRF token from POST request and handle invalid tokens
 * 
 * @param bool $redirect Whether to redirect on failure (true) or just return result (false)
 * @return bool Whether the token is valid
 */
function verify_csrf_token($redirect = true) {
    $token = $_POST['csrf_token'] ?? '';
    $valid = validate_csrf_token($token);
    
    if (!$valid && $redirect) {
        // Set error message in session
        $_SESSION['error_message'] = 'Invalid security token. Please try again.';
        
        // Redirect back to the previous page
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }
    
    return $valid;
}