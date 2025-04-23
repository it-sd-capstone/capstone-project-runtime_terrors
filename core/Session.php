<?php

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            self::secureSession();
        }
    }

    public static function secureSession() {
        // Prevent JavaScript access to session cookies
        ini_set('session.cookie_httponly', 1);
        
        // Use secure cookies for HTTPS environments
        if (!empty($_SERVER['HTTPS'])) {
            ini_set('session.cookie_secure', 1);
        }

        // Regenerate session ID to prevent session fixation
        if (!isset($_SESSION['generated'])) {
            session_regenerate_id(true);
            $_SESSION['generated'] = true;
        }
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
    }

    public static function requireLogin($redirect = "/auth/login") {
        if (!self::isLoggedIn()) {
            header("Location: $redirect");
            exit;
        }
    }

    public static function getRole() {
        return $_SESSION['role'] ?? null;
    }

    public static function requireRole($allowedRoles, $redirect = "/auth/login") {
        if (!self::isLoggedIn() || !in_array(self::getRole(), (array) $allowedRoles)) {
            header("Location: $redirect");
            exit;
        }
    }

    public static function logout() {
        session_unset();
        session_destroy();
        header("Location: /auth/login");
        exit;
    }
}

Session::start();

?>