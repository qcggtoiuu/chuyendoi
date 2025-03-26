<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if API key is provided
if (!isset($_GET['api_key'])) {
    http_response_code(401);
    echo json_encode(['error' => 'API key is required']);
    exit;
}

// Get API key
$apiKey = $_GET['api_key'];

// Initialize database
$db = Database::getInstance();

// Validate API key and get site information
$stmt = $db->prepare("SELECT * FROM sites WHERE api_key = ?");
$stmt->bind_param("s", $apiKey);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key']);
    exit;
}

// Get site information
$site = $result->fetch_assoc();

// Return show_buttons value
echo json_encode([
    'success' => true,
    'show_buttons' => (bool)$site['show_buttons']
]);
