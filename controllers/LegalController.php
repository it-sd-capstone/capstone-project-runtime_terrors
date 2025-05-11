<?php

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