<?php
/**
 * Application-wide helper functions
 */

/**
 * Get the base URL for the application
 * @param string $path Path to append to the base URL
 * @return string The complete URL
 */
function base_url($path = '') {
    // Get the protocol
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    
    // Get the host
    $host = $_SERVER['HTTP_HOST'];
    
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