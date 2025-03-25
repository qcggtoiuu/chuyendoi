<?php
// Prevent direct access to this file
if (!defined('TRACKING_SYSTEM')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Load environment variables from .env file
function loadEnv() {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse line
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Set environment variable
            if (!empty($name)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
}

// Load environment variables
loadEnv();

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'tracking');

// API configuration
define('SITE_URL', 'https://chuyendoi.io.vn');
define('API_URL', SITE_URL . '/api');
define('IPINFO_TOKEN', getenv('IPINFO_TOKEN') ?: ''); // Get token from ipinfo.io

// Security configuration
define('HASH_SALT', getenv('HASH_SALT') ?: 'change-this-salt-in-production');
define('SESSION_LIFETIME', 3600); // 1 hour

// Bot detection configuration
define('BOT_SCORE_THRESHOLD', 0.7); // Score above which a visitor is considered a bot
define('CONVERSION_ANOMALY_THRESHOLD', 3.0); // Z-score threshold for conversion anomalies

// Error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Time zone
date_default_timezone_set('Asia/Ho_Chi_Minh');
