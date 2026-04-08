<?php
/**
 * Database Configuration
 * Smart Pantry – A Recipe Recommendation System
 * Database: smartpantry | Host: localhost | User: root | Pass: 1234
 */

class Database {
    private string $host     = 'localhost';
    private string $db_name  = 'smartpantry';
    private string $username = 'root';
    private string $password = '1234';
    private ?PDO   $conn     = null;

    public function getConnection(): ?PDO {
        if ($this->conn !== null) return $this->conn;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('DB Connection Error: ' . $e->getMessage());
            return null;
        }
        return $this->conn;
    }
}

/** Singleton helper — use getDB() everywhere */
function getDB(): ?PDO {
    static $db = null;
    if ($db === null) {
        $database = new Database();
        $db = $database->getConnection();
    }
    return $db;
}
