<?php
/**
 * Database Configuration
 * KopuGive - MRSM Kota Putra Donation System
 */

// Database credentials
define('DB_HOST', 'localhost:3307');
define('DB_NAME', 'kopugive');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Create database connection
class Database {
    private $conn = null;
    
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch(PDOException $e) {
                error_log("Connection Error: " . $e->getMessage());
                die("Database connection failed. Please check your configuration.");
            }
        }
        
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn = null;
    }
}
?>

