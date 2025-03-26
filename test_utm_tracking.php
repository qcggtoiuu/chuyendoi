<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Get database instance
$db = Database::getInstance();

// Get a valid API key from the database
$stmt = $db->prepare("SELECT api_key FROM sites LIMIT 1");
$stmt->execute();
$site = $stmt->get_result()->fetch_assoc();

if (!$site) {
    die("No sites found in the database. Please add a site first.");
}

$apiKey = $site['api_key'];

// Simulate a visit with referrer and UTM parameters
$visitData = [
    'api_key' => $apiKey,
    'action' => 'pageview',
    'current_page' => 'https://example.com/landing-page',
    'referrer' => 'https://google.com/search?q=example+website',
    'utm_source' => 'google',
    'utm_medium' => 'cpc',
    'utm_campaign' => 'spring_sale',
    'utm_term' => 'example+website',
    'utm_content' => 'ad1',
    'ip' => '203.113.152.5', // Example IP
    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    'screen_width' => 1920,
    'screen_height' => 1080,
    'connection_type' => 'wifi',
    'js_enabled' => true
];

// Send the data to the API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/api/track.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($visitData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Display the result
echo "<h1>UTM Tracking Test</h1>";
echo "<h2>Request Data:</h2>";
echo "<pre>" . print_r($visitData, true) . "</pre>";

echo "<h2>Response:</h2>";
echo "<p>HTTP Code: $httpCode</p>";
echo "<pre>" . print_r(json_decode($response, true), true) . "</pre>";

// If successful, get the visit ID and display a link to the visit detail page
if ($httpCode == 200) {
    $responseData = json_decode($response, true);
    if (isset($responseData['success']) && $responseData['success'] && isset($responseData['visit_id'])) {
        $visitId = $responseData['visit_id'];
        echo "<h2>Success!</h2>";
        echo "<p>Visit ID: $visitId</p>";
        echo "<p><a href='admin/visit_detail.php?id=$visitId' target='_blank'>View Visit Details</a></p>";
    }
}
