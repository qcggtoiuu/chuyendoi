<?php
// Prevent direct access to this file
if (!defined('TRACKING_SYSTEM')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Include configuration
require_once __DIR__ . '/config.php';

/**
 * Database connection class
 */
class Database {
    private static $instance = null;
    private $conn;
    
    /**
     * Constructor - Connect to the database
     */
    private function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new Exception("Database connection failed: " . $this->conn->connect_error);
            }
            
            // Set charset to utf8mb4
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log($e->getMessage());
            die("Database connection error. Please check the logs for more information.");
        }
    }
    
    /**
     * Get database instance (Singleton pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Prepare a statement
     */
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    /**
     * Execute a query
     */
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    /**
     * Get the last inserted ID
     */
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    /**
     * Commit a transaction
     */
    public function commit() {
        $this->conn->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public function rollback() {
        $this->conn->rollback();
    }
    
    /**
     * Close the connection
     */
    public function close() {
        $this->conn->close();
    }
    
    /**
     * Escape a string
     */
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
    
    /**
     * Get error message
     */
    public function error() {
        return $this->conn->error;
    }
}
