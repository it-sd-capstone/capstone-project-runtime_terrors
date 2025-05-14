<?php
/**
 * Utility function for validating names
 */
function validateName($name) {
    // Trim whitespace
    $name = trim($name);
    
    // Check for titles (Dr., Mr., Mrs., etc.)
    if (preg_match('/^(Dr|Mr|Mrs|Ms|Prof|Rev|Hon)\.\s/i', $name)) {
        return [
            'valid' => false,
            'error' => "Please enter your name without titles (e.g., Dr., Mr., Mrs.)"
        ];
    }
    
    // Check for special characters (allowing letters, spaces, hyphens, and apostrophes)
    if (preg_match('/[^a-zA-Z\s\-\']/', $name)) {
        return [
            'valid' => false,
            'error' => "Name should only contain letters, spaces, hyphens, and apostrophes"
        ];
    }
    
    // Ensure name is not empty after trimming
    if (empty($name)) {
        return [
            'valid' => false,
            'error' => "Name cannot be empty"
        ];
    }
    
    return [
        'valid' => true,
        'sanitized' => $name
    ];
}
?>