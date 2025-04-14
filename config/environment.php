<?php
class Environment {
    private static $env = null;
    private static $config = [];
    
    public static function detect() {
        // Already detected
        if (self::$env !== null) return self::$env;
        
        $hostname = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        
        // Detect environment based on hostname
        if (strpos($hostname, 'localhost') !== false || 
            strpos($hostname, '127.0.0.1') !== false) {
            self::$env = 'development';
        } elseif (strpos($hostname, 'capstone.mommabearsweetz.com') !== false) {
            self::$env = 'production';
        } else {
            // Default to development for safety
            self::$env = 'development';
        }
        
        // Load environment-specific configuration
        self::loadConfig();
        
        return self::$env;
    }
    
    public static function get($key, $default = null) {
        self::detect(); // Ensure environment is detected
        return self::$config[$key] ?? $default;
    }
    
    private static function loadConfig() {
        // Base configuration (common to all environments)
        $baseConfig = require __DIR__ . '/config.php';
        
        // Environment-specific configuration
        $envConfig = [];
        $envConfigFile = __DIR__ . '/environments/' . self::$env . '.php';
        if (file_exists($envConfigFile)) {
            $envConfig = require $envConfigFile;
        }
        
        // Merge configurations (environment-specific overrides base)
        self::$config = array_merge($baseConfig, $envConfig);
    }
}

