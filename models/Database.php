<?php
// Load environment-specific database configuration
$config = require __DIR__ . '/../config/environments/development.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = 'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];
            $this->conn = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);

            // Debugging to verify database connection
            error_log("Database connection established to: " . $config['db_name']);

        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new Exception("Database connection failed.");
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // Optional: Allow manual disconnection
    public function disconnect() {
        self::$instance = null;
    }
}
?>