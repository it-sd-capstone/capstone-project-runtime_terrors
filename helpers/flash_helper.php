<?php
/**
 * Set a flash message with context
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 * @param string $context The specific page context this message belongs to (optional)
 */
function set_flash_message($type, $message, $context = 'global') {
    // Initialize the flash messages array if it doesn't exist
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    // Add the message to the specific context
    $_SESSION['flash_messages'][$context][] = [
        'type' => $type,
        'message' => $message,
        'created' => time()
    ];
}

/**
 * Get and clear flash messages for a specific context
 * 
 * @param string $context The context to retrieve messages for (default: global)
 * @return array Array of flash messages
 */
function get_flash_messages($context = 'global') {
    $messages = [];
    
    if (isset($_SESSION['flash_messages'][$context])) {
        $messages = $_SESSION['flash_messages'][$context];
        // Clear only this context's messages
        $_SESSION['flash_messages'][$context] = [];
    }
    
    return $messages;
}

/**
 * Check if there are flash messages for a context
 * 
 * @param string $context The context to check
 * @return boolean True if messages exist
 */
function has_flash_messages($context = 'global') {
    return isset($_SESSION['flash_messages'][$context]) && !empty($_SESSION['flash_messages'][$context]);
}

/**
 * Clear all flash messages or for a specific context
 * 
 * @param string $context Optional context to clear (null clears all)
 */
function clear_flash_messages($context = null) {
    if ($context === null) {
        $_SESSION['flash_messages'] = [];
    } elseif (isset($_SESSION['flash_messages'][$context])) {
        $_SESSION['flash_messages'][$context] = [];
    }
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