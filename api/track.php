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
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Initialize database
$db = Database::getInstance();

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON data from request body
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Check if data is valid JSON
if ($data === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Check if API key is provided
if (!isset($data['api_key'])) {
    http_response_code(401);
    echo json_encode(['error' => 'API key is required']);
    exit;
}

// Validate API key
$site = validateApiKey($data['api_key']);
if (!$site) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key']);
    exit;
}

// Determine action type
$action = isset($data['action']) ? $data['action'] : 'pageview';

// Process based on action type
switch ($action) {
    case 'pageview':
        handlePageView($data, $site);
        break;
    
    case 'click':
        handleClick($data, $site);
        break;
    
    case 'timespent':
        handleTimeSpent($data, $site);
        break;
    
    case 'botcheck':
        handleBotCheck($data, $site);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

/**
 * Handle page view action
 * 
 * @param array $data Request data
 * @param array $site Site data
 * @return void
 */
function handlePageView($data, $site) {
    global $db;
    
    // Get IP address
    $ipAddress = isset($data['ip']) ? $data['ip'] : getClientIp();
    
    // Get user agent
    $userAgent = isset($data['user_agent']) ? $data['user_agent'] : $_SERVER['HTTP_USER_AGENT'];
    
    // Get browser info
    $browserInfo = getBrowserInfo($userAgent);
    $browser = $browserInfo['name'];
    $browserVersion = $browserInfo['version'];
    
    // Get OS info
    $osInfo = getOsInfo($userAgent);
    $os = $osInfo['name'];
    $osVersion = $osInfo['version'];
    
    // Get screen size
    $screenWidth = isset($data['screen_width']) ? intval($data['screen_width']) : 0;
    $screenHeight = isset($data['screen_height']) ? intval($data['screen_height']) : 0;
    
    // Get current page
    $currentPage = isset($data['current_page']) ? $data['current_page'] : '';
    
    // Get location info
    $locationInfo = getLocationInfo($ipAddress);
    $city = $locationInfo['city'];
    $country = $locationInfo['country'];
    $isp = $locationInfo['isp'];
    
    // Get connection type
    $connectionType = isset($data['connection_type']) ? $data['connection_type'] : getConnectionType($_SERVER);
    
    // Calculate bot score
    $botFactors = [];
    
    // User agent analysis
    if (preg_match('/(bot|crawl|spider|slurp|baidu|yandex|mediapartners|adsbot)/i', $userAgent)) {
        $botFactors['user_agent'] = ['score' => 1.0, 'weight' => 0.4];
    } else {
        $botFactors['user_agent'] = ['score' => 0.0, 'weight' => 0.4];
    }
    
    // Headers analysis
    $suspiciousHeaders = 0;
    $totalHeaders = count($_SERVER);
    
    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $suspiciousHeaders++;
    }
    
    if (!isset($_SERVER['HTTP_ACCEPT'])) {
        $suspiciousHeaders++;
    }
    
    $headerScore = $totalHeaders > 0 ? $suspiciousHeaders / $totalHeaders : 0;
    $botFactors['headers'] = ['score' => $headerScore, 'weight' => 0.2];
    
    // IP analysis
    if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        $botFactors['ip'] = ['score' => 0.8, 'weight' => 0.2];
    } else {
        $botFactors['ip'] = ['score' => 0.0, 'weight' => 0.2];
    }
    
    // JavaScript capabilities
    $jsEnabled = isset($data['js_enabled']) ? $data['js_enabled'] : false;
    $botFactors['js'] = ['score' => $jsEnabled ? 0.0 : 1.0, 'weight' => 0.2];
    
    // Calculate final bot score
    $botScore = calculateBotScore($botFactors);
    $isBot = isBot($botScore);
    
    // Insert visit into database
    $stmt = $db->prepare("
        INSERT INTO visits (
            site_id, ip_address, browser, browser_version, isp, connection_type, 
            os, os_version, screen_width, screen_height, city, country, 
            current_page, bot_score, is_bot
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "isssssssiisssdi",
        $site['id'], $ipAddress, $browser, $browserVersion, $isp, $connectionType,
        $os, $osVersion, $screenWidth, $screenHeight, $city, $country,
        $currentPage, $botScore, $isBot
    );
    
    $stmt->execute();
    $visitId = $db->lastInsertId();
    
    // Check if buttons should be hidden
    $visitorData = [
        'ip_address' => $ipAddress,
        'browser' => $browser,
        'os' => $os,
        'city' => $city,
        'country' => $country,
        'isp' => $isp,
        'bot_score' => $botScore
    ];
    
    $hideButtons = shouldHideButtons($visitorData);
    
    // If buttons should be hidden, log it
    if ($hideButtons) {
        $fraudMatch = matchesFraudPattern($visitorData);
        
        $stmt = $db->prepare("
            INSERT INTO button_hide_logs (
                site_id, visit_id, reason, matching_pattern_id, similarity_score
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        $reason = $isBot ? 'Bot detection' : 'Fraud pattern match';
        $patternId = $fraudMatch['isMatch'] ? $fraudMatch['patternId'] : null;
        $similarityScore = $fraudMatch['isMatch'] ? $fraudMatch['similarityScore'] : null;
        
        $stmt->bind_param(
            "iisid",
            $site['id'], $visitId, $reason, $patternId, $similarityScore
        );
        
        $stmt->execute();
    }
    
    // Return response
    echo json_encode([
        'success' => true,
        'visit_id' => $visitId,
        'bot_score' => $botScore,
        'is_bot' => $isBot,
        'hide_buttons' => $hideButtons
    ]);
}

/**
 * Handle click action
 * 
 * @param array $data Request data
 * @param array $site Site data
 * @return void
 */
function handleClick($data, $site) {
    global $db;
    
    // Check if required fields are provided
    if (!isset($data['visit_id']) || !isset($data['click_type']) || !isset($data['click_url'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    $visitId = intval($data['visit_id']);
    $clickType = $data['click_type'];
    $clickUrl = $data['click_url'];
    
    // Validate click type
    $validClickTypes = ['phone', 'zalo', 'messenger', 'maps'];
    if (!in_array($clickType, $validClickTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid click type']);
        exit;
    }
    
    // Validate visit ID
    $stmt = $db->prepare("SELECT id FROM visits WHERE id = ? AND site_id = ?");
    $stmt->bind_param("ii", $visitId, $site['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid visit ID']);
        exit;
    }
    
    // Insert click into database
    $stmt = $db->prepare("
        INSERT INTO clicks (visit_id, click_type, click_url)
        VALUES (?, ?, ?)
    ");
    
    $stmt->bind_param("iss", $visitId, $clickType, $clickUrl);
    $stmt->execute();
    $clickId = $db->lastInsertId();
    
    // Check for anomalies in conversion rate
    checkConversionAnomalies($site['id'], $clickType);
    
    // Return response
    echo json_encode([
        'success' => true,
        'click_id' => $clickId
    ]);
}

/**
 * Handle time spent action
 * 
 * @param array $data Request data
 * @param array $site Site data
 * @return void
 */
function handleTimeSpent($data, $site) {
    global $db;
    
    // Check if required fields are provided
    if (!isset($data['visit_id']) || !isset($data['time_spent'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    $visitId = intval($data['visit_id']);
    $timeSpent = intval($data['time_spent']);
    
    // Validate visit ID
    $stmt = $db->prepare("SELECT id FROM visits WHERE id = ? AND site_id = ?");
    $stmt->bind_param("ii", $visitId, $site['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid visit ID']);
        exit;
    }
    
    // Update time spent in database
    $stmt = $db->prepare("
        UPDATE visits SET time_spent = ? WHERE id = ?
    ");
    
    $stmt->bind_param("ii", $timeSpent, $visitId);
    $stmt->execute();
    
    // Return response
    echo json_encode([
        'success' => true
    ]);
}

/**
 * Handle bot check action
 * 
 * @param array $data Request data
 * @param array $site Site data
 * @return void
 */
function handleBotCheck($data, $site) {
    global $db;
    
    // Check if required fields are provided
    if (!isset($data['visit_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    $visitId = intval($data['visit_id']);
    
    // Validate visit ID
    $stmt = $db->prepare("SELECT * FROM visits WHERE id = ? AND site_id = ?");
    $stmt->bind_param("ii", $visitId, $site['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid visit ID']);
        exit;
    }
    
    $visit = $result->fetch_assoc();
    
    // Additional bot detection factors
    $botFactors = [];
    
    // Mouse movement analysis
    if (isset($data['mouse_movements'])) {
        $mouseMovements = $data['mouse_movements'];
        $straightLineRatio = calculateStraightLineRatio($mouseMovements);
        $botFactors['mouse'] = ['score' => $straightLineRatio, 'weight' => 0.3];
    }
    
    // Timing analysis
    if (isset($data['timing_data'])) {
        $timingData = $data['timing_data'];
        $timingRegularity = calculateTimingRegularity($timingData);
        $botFactors['timing'] = ['score' => $timingRegularity, 'weight' => 0.3];
    }
    
    // Browser fingerprinting
    if (isset($data['fingerprint'])) {
        $fingerprint = $data['fingerprint'];
        $fingerprintScore = analyzeBrowserFingerprint($fingerprint);
        $botFactors['fingerprint'] = ['score' => $fingerprintScore, 'weight' => 0.4];
    }
    
    // Calculate updated bot score
    if (!empty($botFactors)) {
        // Combine with existing factors
        $existingBotScore = $visit['bot_score'];
        $botFactors['existing'] = ['score' => $existingBotScore, 'weight' => 0.5];
        
        $updatedBotScore = calculateBotScore($botFactors);
        $isBot = isBot($updatedBotScore);
        
        // Update bot score in database
        $stmt = $db->prepare("
            UPDATE visits SET bot_score = ?, is_bot = ? WHERE id = ?
        ");
        
        $stmt->bind_param("dii", $updatedBotScore, $isBot, $visitId);
        $stmt->execute();
        
        // Check if buttons should be hidden
        $visitorData = [
            'ip_address' => $visit['ip_address'],
            'browser' => $visit['browser'],
            'os' => $visit['os'],
            'city' => $visit['city'],
            'country' => $visit['country'],
            'isp' => $visit['isp'],
            'bot_score' => $updatedBotScore
        ];
        
        $hideButtons = shouldHideButtons($visitorData);
        
        // Return response
        echo json_encode([
            'success' => true,
            'bot_score' => $updatedBotScore,
            'is_bot' => $isBot,
            'hide_buttons' => $hideButtons
        ]);
    } else {
        // No new factors, return existing data
        echo json_encode([
            'success' => true,
            'bot_score' => $visit['bot_score'],
            'is_bot' => $visit['is_bot'],
            'hide_buttons' => shouldHideButtons([
                'ip_address' => $visit['ip_address'],
                'browser' => $visit['browser'],
                'os' => $visit['os'],
                'city' => $visit['city'],
                'country' => $visit['country'],
                'isp' => $visit['isp'],
                'bot_score' => $visit['bot_score']
            ])
        ]);
    }
}

/**
 * Calculate straight line ratio for mouse movements
 * 
 * @param array $movements Mouse movement data
 * @return float Straight line ratio (0-1)
 */
function calculateStraightLineRatio($movements) {
    if (count($movements) < 3) {
        return 0.5; // Not enough data
    }
    
    $straightLines = 0;
    $totalSegments = count($movements) - 2;
    
    for ($i = 0; $i < $totalSegments; $i++) {
        $p1 = $movements[$i];
        $p2 = $movements[$i + 1];
        $p3 = $movements[$i + 2];
        
        // Calculate angle between segments
        $angle = calculateAngle($p1, $p2, $p3);
        
        // If angle is close to 180 degrees (straight line)
        if (abs($angle - 180) < 10) {
            $straightLines++;
        }
    }
    
    return $totalSegments > 0 ? $straightLines / $totalSegments : 0.5;
}

/**
 * Calculate angle between three points
 * 
 * @param array $p1 First point [x, y]
 * @param array $p2 Second point [x, y]
 * @param array $p3 Third point [x, y]
 * @return float Angle in degrees
 */
function calculateAngle($p1, $p2, $p3) {
    $a = sqrt(pow($p2[0] - $p3[0], 2) + pow($p2[1] - $p3[1], 2));
    $b = sqrt(pow($p1[0] - $p3[0], 2) + pow($p1[1] - $p3[1], 2));
    $c = sqrt(pow($p1[0] - $p2[0], 2) + pow($p1[1] - $p2[1], 2));
    
    // Law of cosines
    $cosB = ($a * $a + $c * $c - $b * $b) / (2 * $a * $c);
    
    // Prevent domain errors
    $cosB = max(-1, min(1, $cosB));
    
    return rad2deg(acos($cosB));
}

/**
 * Calculate timing regularity for interaction timing
 * 
 * @param array $timingData Timing data
 * @return float Timing regularity (0-1)
 */
function calculateTimingRegularity($timingData) {
    if (count($timingData) < 3) {
        return 0.5; // Not enough data
    }
    
    $intervals = [];
    for ($i = 1; $i < count($timingData); $i++) {
        $intervals[] = $timingData[$i] - $timingData[$i - 1];
    }
    
    // Calculate mean and standard deviation
    $mean = array_sum($intervals) / count($intervals);
    $variance = 0;
    
    foreach ($intervals as $interval) {
        $variance += pow($interval - $mean, 2);
    }
    
    $variance /= count($intervals);
    $stdDev = sqrt($variance);
    
    // Calculate coefficient of variation (CV)
    $cv = $mean > 0 ? $stdDev / $mean : 0;
    
    // Human interactions typically have higher CV (more variability)
    // Bot interactions typically have lower CV (more regular)
    // CV < 0.1 is highly suspicious (too regular)
    // CV > 0.5 is typical for humans
    
    // Convert CV to a score between 0 and 1
    // Lower CV = higher bot score
    $regularityScore = max(0, min(1, 1 - ($cv * 2)));
    
    return $regularityScore;
}

/**
 * Analyze browser fingerprint for bot detection
 * 
 * @param array $fingerprint Browser fingerprint data
 * @return float Bot score (0-1)
 */
function analyzeBrowserFingerprint($fingerprint) {
    $suspiciousFactors = 0;
    $totalFactors = 0;
    
    // Check WebDriver
    if (isset($fingerprint['webdriver']) && $fingerprint['webdriver']) {
        $suspiciousFactors++;
    }
    $totalFactors++;
    
    // Check navigator properties
    if (isset($fingerprint['navigator'])) {
        $nav = $fingerprint['navigator'];
        
        // Check plugins length
        if (isset($nav['plugins_length']) && $nav['plugins_length'] === 0) {
            $suspiciousFactors++;
        }
        $totalFactors++;
        
        // Check languages
        if (isset($nav['languages_length']) && $nav['languages_length'] === 0) {
            $suspiciousFactors++;
        }
        $totalFactors++;
        
        // Check user agent consistency
        if (isset($nav['user_agent']) && isset($nav['app_version'])) {
            if (strpos($nav['app_version'], $nav['user_agent']) === false) {
                $suspiciousFactors++;
            }
        }
        $totalFactors++;
    }
    
    // Check canvas fingerprint
    if (isset($fingerprint['canvas']) && $fingerprint['canvas'] === 'anomaly') {
        $suspiciousFactors++;
    }
    $totalFactors++;
    
    // Check WebGL
    if (isset($fingerprint['webgl']) && $fingerprint['webgl'] === 'anomaly') {
        $suspiciousFactors++;
    }
    $totalFactors++;
    
    // Calculate score
    return $totalFactors > 0 ? $suspiciousFactors / $totalFactors : 0.5;
}

/**
 * Check for anomalies in conversion rates
 * 
 * @param int $siteId Site ID
 * @param string $clickType Click type
 * @return void
 */
function checkConversionAnomalies($siteId, $clickType) {
    global $db;
    
    // Get current hour
    $currentHour = date('Y-m-d H:00:00');
    
    // Get total visits in the current hour
    $stmt = $db->prepare("
        SELECT COUNT(*) as total_visits
        FROM visits
        WHERE site_id = ?
        AND visit_time >= ?
    ");
    
    $stmt->bind_param("is", $siteId, $currentHour);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalVisits = $result->fetch_assoc()['total_visits'];
    
    // Get total clicks in the current hour
    $stmt = $db->prepare("
        SELECT COUNT(*) as total_clicks
        FROM clicks c
        JOIN visits v ON c.visit_id = v.id
        WHERE v.site_id = ?
        AND c.click_type = ?
        AND c.click_time >= ?
    ");
    
    $stmt->bind_param("iss", $siteId, $clickType, $currentHour);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalClicks = $result->fetch_assoc()['total_clicks'];
    
    // Calculate current conversion rate
    $currentRate = $totalVisits > 0 ? ($totalClicks / $totalVisits) : 0;
    
    // Get baseline conversion rate
    $stmt = $db->prepare("
        SELECT avg_rate, std_deviation
        FROM conversion_baselines
        WHERE site_id = ?
        AND segment_type = 'overall'
        AND conversion_type = ?
    ");
    
    $stmt->bind_param("is", $siteId, $clickType);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $baseline = $result->fetch_assoc();
        $avgRate = $baseline['avg_rate'];
        $stdDev = $baseline['std_deviation'];
        
        // Calculate Z-score
        $zScore = calculateZScore($currentRate, $avgRate, $stdDev);
        
        // Check if anomaly
        if (isAnomaly($zScore, CONVERSION_ANOMALY_THRESHOLD)) {
            // Insert anomaly into database
            $stmt = $db->prepare("
                INSERT INTO anomalies (
                    site_id, anomaly_type, severity, segment_type, segment_value,
                    expected_value, actual_value, deviation_percent, affected_visits,
                    description
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $anomalyType = 'high_cr';
            $severity = abs($zScore) > 5 ? 'high' : (abs($zScore) > 4 ? 'medium' : 'low');
            $segmentType = 'overall';
            $segmentValue = null;
            $deviationPercent = $avgRate > 0 ? (($currentRate - $avgRate) / $avgRate) * 100 : 0;
            $description = "Unusual conversion rate for {$clickType} clicks. Current rate: " . 
                           round($currentRate * 100, 2) . "%, Expected: " . 
                           round($avgRate * 100, 2) . "%, Z-score: " . round($zScore, 2);
            
            $stmt->bind_param(
                "issssdddis",
                $siteId, $anomalyType, $severity, $segmentType, $segmentValue,
                $avgRate, $currentRate, $deviationPercent, $totalVisits, $description
            );
            
            $stmt->execute();
        }
    } else {
        // No baseline exists, create one
        $stmt = $db->prepare("
            INSERT INTO conversion_baselines (
                site_id, segment_type, segment_value, conversion_type,
                avg_rate, std_deviation, min_rate, max_rate, sample_size
            ) VALUES (?, 'overall', NULL, ?, ?, 0.01, ?, ?, 1)
        ");
        
        $minRate = max(0, $currentRate - 0.01);
        $maxRate = min(1, $currentRate + 0.01);
        
        $stmt->bind_param(
            "isddd",
            $siteId, $clickType, $currentRate, $minRate, $maxRate
        );
        
        $stmt->execute();
    }
}
