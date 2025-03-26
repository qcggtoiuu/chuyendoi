<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Get database instance
$db = Database::getInstance();

// Process filters
$siteId = isset($_GET['site_id']) ? (int)$_GET['site_id'] : 0;
$isBot = isset($_GET['is_bot']) ? (int)$_GET['is_bot'] : -1; // -1 = all, 0 = humans, 1 = bots
$lastVisitId = isset($_GET['last_visit_id']) ? (int)$_GET['last_visit_id'] : 0;

// Calculate time 60 minutes ago
$sixtyMinutesAgo = date('Y-m-d H:i:s', strtotime('-60 minutes'));
$fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));

// Build query for visitors
$query = "
    SELECT v.*, s.name as site_name
    FROM visits v
    JOIN sites s ON v.site_id = s.id
    WHERE v.visit_time >= ?
";
$params = [$sixtyMinutesAgo];
$types = "s";

// Add filters
if ($siteId > 0) {
    $query .= " AND v.site_id = ?";
    $params[] = $siteId;
    $types .= "i";
}

if ($isBot >= 0) {
    $query .= " AND v.is_bot = ?";
    $params[] = $isBot;
    $types .= "i";
}

// If we have a last visit ID, only get newer visits
if ($lastVisitId > 0) {
    $query .= " AND v.id > ?";
    $params[] = $lastVisitId;
    $types .= "i";
}

// Add order and limit
$query .= " ORDER BY v.visit_time DESC LIMIT 100";

// Get visitors
$stmt = $db->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$visitors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats = [
    'total_visitors' => 0,
    'current_visitors' => 0,
    'total_clicks' => 0,
    'bot_visitors' => 0
];

// Total visitors in the last 60 minutes
$statsQuery = "
    SELECT COUNT(*) as total
    FROM visits v
    WHERE v.visit_time >= ?
";
$statsParams = [$sixtyMinutesAgo];
$statsTypes = "s";

if ($siteId > 0) {
    $statsQuery .= " AND v.site_id = ?";
    $statsParams[] = $siteId;
    $statsTypes .= "i";
}

$stmt = $db->prepare($statsQuery);
$stmt->bind_param($statsTypes, ...$statsParams);
$stmt->execute();
$stats['total_visitors'] = $stmt->get_result()->fetch_assoc()['total'];

// Current visitors (last 5 minutes)
$currentQuery = "
    SELECT COUNT(*) as total
    FROM visits v
    WHERE v.visit_time >= ?
";
$currentParams = [$fiveMinutesAgo];
$currentTypes = "s";

if ($siteId > 0) {
    $currentQuery .= " AND v.site_id = ?";
    $currentParams[] = $siteId;
    $currentTypes .= "i";
}

$stmt = $db->prepare($currentQuery);
$stmt->bind_param($currentTypes, ...$currentParams);
$stmt->execute();
$stats['current_visitors'] = $stmt->get_result()->fetch_assoc()['total'];

// Total clicks in the last 60 minutes
$clicksQuery = "
    SELECT COUNT(*) as total
    FROM clicks c
    JOIN visits v ON c.visit_id = v.id
    WHERE c.click_time >= ?
";
$clicksParams = [$sixtyMinutesAgo];
$clicksTypes = "s";

if ($siteId > 0) {
    $clicksQuery .= " AND v.site_id = ?";
    $clicksParams[] = $siteId;
    $clicksTypes .= "i";
}

$stmt = $db->prepare($clicksQuery);
$stmt->bind_param($clicksTypes, ...$clicksParams);
$stmt->execute();
$stats['total_clicks'] = $stmt->get_result()->fetch_assoc()['total'];

// Bot visitors in the last 60 minutes
$botQuery = "
    SELECT COUNT(*) as total
    FROM visits v
    WHERE v.visit_time >= ?
    AND v.is_bot = 1
";
$botParams = [$sixtyMinutesAgo];
$botTypes = "s";

if ($siteId > 0) {
    $botQuery .= " AND v.site_id = ?";
    $botParams[] = $siteId;
    $botTypes .= "i";
}

$stmt = $db->prepare($botQuery);
$stmt->bind_param($botTypes, ...$botParams);
$stmt->execute();
$stats['bot_visitors'] = $stmt->get_result()->fetch_assoc()['total'];

// Return response
echo json_encode([
    'visitors' => $visitors,
    'stats' => $stats
]);
