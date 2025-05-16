<?php

require_once 'C:/xampp/htdocs/appointment-system/capstone-project-runtime_terrors/helpers/system_notifications.php';
class LegalController {
    
    /**
     * Display Terms of Service page
     */
    public function terms() {
        include VIEW_PATH . '/legal/terms.php';
    }
    
    /**
     * Display Privacy Policy page
     */
    public function privacy() {
        include VIEW_PATH . '/legal/privacy.php';
    }
}
?>