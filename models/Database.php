<?php
// Load database config
$config = require __DIR__ . '/../config/environments/development.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

        // ✅ Check for connection errors
        if ($this->conn->connect_error) {
            error_log("MySQL Connection Error: " . $this->conn->connect_error);
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

    public function disconnect() {
        if ($this->conn) {
            $this->conn->close();
            self::$instance = null;
        }
    }
}
?>