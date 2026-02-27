<?php
/**
 * Database Configuration
 * Handles database connection and provides database instance
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'SmartPantryFull';
    private $username = 'root';
    private $password = '1234';
    private $conn;

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            $errorMsg = "Connection Error: " . $e->getMessage();
            error_log($errorMsg);
            
            // Log more specific error information
            if (strpos($e->getMessage(), "Unknown database") !== false) {
                error_log("Database '{$this->db_name}' does not exist. Please create it and import schema.sql");
            } elseif (strpos($e->getMessage(), "Access denied") !== false) {
                error_log("Database access denied. Check username/password in config/database.php");
            } elseif (strpos($e->getMessage(), "Connection refused") !== false) {
                error_log("MySQL service is not running. Please start MySQL in XAMPP");
            }
            
            return null;
        }

        return $this->conn;
    }
}

// Create global database instance
function getDB() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->getConnection();
}

