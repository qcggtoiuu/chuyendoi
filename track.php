<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/button.php';

// Check if API key is provided
$apiKey = isset($_GET['api_key']) ? $_GET['api_key'] : '';

// Validate API key
$site = null;
if (!empty($apiKey)) {
    $site = validateApiKey($apiKey);
}

// If API key is invalid, show error
if (!$site) {
    header('HTTP/1.0 403 Forbidden');
    exit('Invalid API key');
}

// Generate button options
$buttonOptions = [
    'style' => isset($_GET['style']) ? $_GET['style'] : 'fab',
    'phone' => isset($_GET['phone']) ? $_GET['phone'] : $site['phone'] ?? '',
    'zalo' => isset($_GET['zalo']) ? $_GET['zalo'] : $site['zalo'] ?? '',
    'messenger' => isset($_GET['messenger']) ? $_GET['messenger'] : $site['messenger'] ?? '',
    'maps' => isset($_GET['maps']) ? $_GET['maps'] : $site['maps'] ?? '',
    'show_labels' => isset($_GET['show_labels']) ? ($_GET['show_labels'] === '1') : true,
    'primary_color' => isset($_GET['primary_color']) ? $_GET['primary_color'] : '#3961AA',
    'animation' => isset($_GET['animation']) ? ($_GET['animation'] === '1') : true
];

// Generate script options
$scriptOptions = [
    'debug' => isset($_GET['debug']) ? ($_GET['debug'] === '1') : false,
    'apiUrl' => API_URL . '/track.php'
];

// Generate tracking code
$trackingCode = generateTrackingCode($apiKey, $buttonOptions, $scriptOptions);

// Output tracking code
header('Content-Type: text/javascript');
echo "document.write(" . json_encode($trackingCode) . ");";
