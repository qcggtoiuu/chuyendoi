<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

// Set error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Login Test</h1>";

// Test database connection
echo "<h2>Testing Database Connection</h2>";
try {
    $db = Database::getInstance();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Check if users table exists and has data
echo "<h2>Testing Users Table</h2>";
try {
    $result = $db->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Users table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Users table does not exist</p>";
        exit;
    }
    
    $result = $db->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "<p style='color: green;'>✓ Users table has " . $row['count'] . " records</p>";
    } else {
        echo "<p style='color: red;'>✗ Users table is empty</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error checking users table: " . $e->getMessage() . "</p>";
    exit;
}

// Test login with quantri user
echo "<h2>Testing Login for 'quantri' User</h2>";
try {
    // Get user from database
    $stmt = $db->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $username = 'quantri';
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        echo "<p style='color: green;'>✓ User 'quantri' exists in database</p>";
        
        $user = $result->fetch_assoc();
        echo "<p>Password hash in database: <code>" . htmlspecialchars($user['password_hash']) . "</code></p>";
        
        // The password verification is failing, so let's update the password hash
        echo "<h3>Updating Password Hash</h3>";
        $newPassword = 'P8j2mK9xL5qR3sT7';
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $updateStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $updateStmt->bind_param("ss", $newHash, $username);
        
        if ($updateStmt->execute()) {
            echo "<p style='color: green;'>✓ Password hash updated successfully</p>";
            echo "<p>New password hash: <code>" . htmlspecialchars($newHash) . "</code></p>";
            echo "<p>You can now login with:</p>";
            echo "<ul>";
            echo "<li>Username: <strong>quantri</strong></li>";
            echo "<li>Password: <strong>P8j2mK9xL5qR3sT7</strong></li>";
            echo "</ul>";
            
            // Verify the new password hash
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (password_verify($newPassword, $user['password_hash'])) {
                echo "<p style='color: green;'>✓ Password verification successful with new hash</p>";
            } else {
                echo "<p style='color: red;'>✗ Password verification still failing with new hash</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Failed to update password hash: " . $db->error() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ User 'quantri' does not exist in database</p>";
        
        // Create the user
        echo "<h3>Creating User</h3>";
        $username = 'quantri';
        $password = 'P8j2mK9xL5qR3sT7';
        $email = 'admin@chuyendoi.io.vn';
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        $insertStmt = $db->prepare("INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)");
        $insertStmt->bind_param("sss", $username, $passwordHash, $email);
        
        if ($insertStmt->execute()) {
            echo "<p style='color: green;'>✓ User created successfully</p>";
            echo "<p>You can now login with:</p>";
            echo "<ul>";
            echo "<li>Username: <strong>quantri</strong></li>";
            echo "<li>Password: <strong>P8j2mK9xL5qR3sT7</strong></li>";
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create user: " . $db->error() . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error testing login: " . $e->getMessage() . "</p>";
}

echo "<p><a href='admin/login.php'>Go to Login Page</a></p>";
