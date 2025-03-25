<?php
/**
 * Password Hash Generator
 * 
 * This script generates a bcrypt hash for a given password.
 * You can use this to generate a new password hash for the admin user.
 * 
 * Usage:
 * 1. Run this script in your browser or from the command line
 * 2. Enter the password when prompted (if running from command line)
 * 3. Copy the generated hash and use it in your SQL query
 */

// Check if running from command line
$isCli = (php_sapi_name() === 'cli');

// Get password
if ($isCli) {
    echo "Enter password: ";
    $password = trim(fgets(STDIN));
} else {
    // Get password from GET parameter or use default
    $password = isset($_GET['password']) ? $_GET['password'] : 'P8j2mK9xL5qR3sT7';
}

// Generate hash
$hash = password_hash($password, PASSWORD_BCRYPT);

// Output
if ($isCli) {
    echo "Password: $password\n";
    echo "Bcrypt Hash: $hash\n";
    echo "\nSQL Query:\n";
    echo "UPDATE users SET password_hash = '$hash' WHERE username = 'quantri';\n";
} else {
    echo "<html><head><title>Password Hash Generator</title>";
    echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }";
    echo ".container { max-width: 800px; margin: 0 auto; }";
    echo "pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }";
    echo "h1 { color: #333; }";
    echo "label { display: block; margin-bottom: 5px; font-weight: bold; }";
    echo "input[type=text] { width: 300px; padding: 8px; margin-bottom: 15px; }";
    echo "button { background: #4CAF50; color: white; border: none; padding: 10px 15px; cursor: pointer; }";
    echo "button:hover { background: #45a049; }";
    echo ".result { margin-top: 20px; }";
    echo "</style></head><body>";
    echo "<div class='container'>";
    echo "<h1>Password Hash Generator</h1>";
    
    echo "<form method='get'>";
    echo "<label for='password'>Password:</label>";
    echo "<input type='text' id='password' name='password' value='" . htmlspecialchars($password) . "'>";
    echo "<button type='submit'>Generate Hash</button>";
    echo "</form>";
    
    echo "<div class='result'>";
    echo "<h2>Result:</h2>";
    echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
    echo "<p><strong>Bcrypt Hash:</strong> " . htmlspecialchars($hash) . "</p>";
    
    echo "<h3>SQL Query:</h3>";
    echo "<pre>UPDATE users SET password_hash = '" . htmlspecialchars($hash) . "' WHERE username = 'quantri';</pre>";
    echo "</div>";
    
    echo "</div></body></html>";
}
