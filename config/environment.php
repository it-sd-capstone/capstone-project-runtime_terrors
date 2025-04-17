<?php

class Environment {
    private static $env = null;
    private static $config = [];

    /**
     * Detect the current environment based on hostname.
     * Defaults to 'development' if no match is found.
     */
    public static function detect() {
        if (self::$env !== null) return self::$env; // Already detected
        
        $hostname = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';

        // Determine environment based on hostname
        if (strpos($hostname, 'localhost') !== false || strpos($hostname, '127.0.0.1') !== false) {
            self::$env = 'development';
        } elseif (strpos($hostname, 'your-production-domain.com') !== false) {
            self::$env = 'production';
        } else {
            self::$env = 'development'; // Default to development for safety
        }

        self::loadConfig(); // Load respective configuration
        return self::$env;
    }

    /**
     * Retrieve a configuration value.
     */
    public static function get($key, $default = null) {
        self::detect(); // Ensure environment is detected first
        return self::$config[$key] ?? $default;
    }

    /**
     * Load configuration based on the detected environment.
     */
    private static function loadConfig() {
        // Base configuration (common across environments)
        $baseConfig = require __DIR__ . '/config.php';

        // Environment-specific configuration
        $envConfigFile = __DIR__ . "/environments/" . self::$env . ".php";
        $envConfig = file_exists($envConfigFile) ? require $envConfigFile : [];

        // Merge configurations (environment-specific overrides base)
        self::$config = array_merge($baseConfig, $envConfig);
    }
}
?>