<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';

// Check if database connection details are provided
if (empty(DB_HOST) || empty(DB_USER) || empty(DB_NAME)) {
    die("Database connection details are missing. Please check your .env file.");
}

// Connect to MySQL server
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

echo "Database created successfully.\n";

// Select the database
$conn->select_db(DB_NAME);

// Read SQL file
$sql = file_get_contents(__DIR__ . '/database.sql');

// Execute SQL statements
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    echo "Database tables created successfully.\n";
} else {
    die("Error creating tables: " . $conn->error);
}

// Close connection
$conn->close();

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
    echo "Logs directory created successfully.\n";
}

echo "Setup completed successfully!\n";
echo "You can now access the admin panel at: " . SITE_URL . "/admin\n";
echo "Default login credentials:\n";
echo "Username: quantri\n";
echo "Password: P8j2mK9xL5qR3sT7\n";
echo "Please change the password after your first login.\n";
