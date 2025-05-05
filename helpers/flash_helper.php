<?php
/**
 * Set a flash message to be displayed on the next page load
 * 
 * @param string $type The type of message (success, error, info, warning)
 * @param string $message The message to display
 */
function set_flash_message($type, $message) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $_SESSION['flash_messages'][$type] = $message;
}

/**
 * Get and clear flash messages
 * 
 * @return array Flash messages
 */
function get_flash_messages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Display flash messages
 */
function display_flash_messages() {
    $messages = get_flash_messages();
    
    foreach ($messages as $type => $message) {
        $alert_class = match($type) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            default => 'alert-info'
        };
        
        echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}