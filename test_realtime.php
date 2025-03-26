<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<h1>Unauthorized</h1>';
    echo '<p>Please <a href="admin/login.php">login</a> first.</p>';
    exit;
}

// Get database instance
$db = Database::getInstance();

// Function to generate a random IP address
function generateRandomIP() {
    return mt_rand(1, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255);
}

// Function to generate a random user agent
function generateRandomUserAgent() {
    $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge'];
    $browser = $browsers[array_rand($browsers)];
    
    $version = mt_rand(70, 110) . '.' . mt_rand(0, 9) . '.' . mt_rand(1000, 9999) . '.' . mt_rand(10, 99);
    
    $os = ['Windows NT 10.0', 'Macintosh; Intel Mac OS X 10_15', 'X11; Linux x86_64', 'iPhone; CPU iPhone OS 14_0'];
    $platform = $os[array_rand($os)];
    
    return "Mozilla/5.0 ({$platform}) AppleWebKit/537.36 (KHTML, like Gecko) {$browser}/{$version}";
}

// Function to generate random location
function generateRandomLocation() {
    $cities = ['Hà Nội', 'Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ', 'Biên Hòa', 'Nha Trang', 'Huế'];
    $city = $cities[array_rand($cities)];
    
    return [
        'city' => $city,
        'country' => 'Vietnam',
        'isp' => 'VNPT'
    ];
}

// Function to generate a random page URL
function generateRandomPage() {
    $pages = [
        'https://chuyendoi.io.vn/',
        'https://chuyendoi.io.vn/about',
        'https://chuyendoi.io.vn/services',
        'https://chuyendoi.io.vn/contact',
        'https://chuyendoi.io.vn/products/1',
        'https://chuyendoi.io.vn/products/2',
        'https://chuyendoi.io.vn/blog/post-1',
        'https://chuyendoi.io.vn/blog/post-2'
    ];
    
    return $pages[array_rand($pages)];
}

// Function to generate test visits
function generateTestVisits($db, $count = 5) {
    // Get all sites
    $stmt = $db->prepare("SELECT id FROM sites");
    $stmt->execute();
    $sites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($sites)) {
        return "No sites found. Please add a site first.";
    }
    
    $results = [];
    
    for ($i = 0; $i < $count; $i++) {
        // Select a random site
        $site = $sites[array_rand($sites)];
        $siteId = $site['id'];
        
        // Generate random data
        $ipAddress = generateRandomIP();
        $userAgent = generateRandomUserAgent();
        $browserInfo = getBrowserInfo($userAgent);
        $browser = $browserInfo['name'];
        $browserVersion = $browserInfo['version'];
        $osInfo = getOsInfo($userAgent);
        $os = $osInfo['name'];
        $osVersion = $osInfo['version'];
        $screenWidth = mt_rand(320, 1920);
        $screenHeight = mt_rand(480, 1080);
        $currentPage = generateRandomPage();
        $locationInfo = generateRandomLocation();
        $city = $locationInfo['city'];
        $country = $locationInfo['country'];
        $isp = $locationInfo['isp'];
        $connectionType = 'WiFi';
        $botScore = mt_rand(0, 100) / 100;
        $isBot = $botScore > BOT_SCORE_THRESHOLD ? 1 : 0;
        
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
            $siteId, $ipAddress, $browser, $browserVersion, $isp, $connectionType,
            $os, $osVersion, $screenWidth, $screenHeight, $city, $country,
            $currentPage, $botScore, $isBot
        );
        
        $stmt->execute();
        $visitId = $db->lastInsertId();
        
        // Add to results
        $results[] = [
            'id' => $visitId,
            'ip' => $ipAddress,
            'browser' => $browser . ' ' . $browserVersion,
            'os' => $os . ' ' . $osVersion,
            'location' => $city . ', ' . $country,
            'is_bot' => $isBot ? 'Bot' : 'Human'
        ];
        
        // Randomly add a click (30% chance)
        if (mt_rand(1, 100) <= 30) {
            $clickTypes = ['phone', 'zalo', 'messenger', 'maps'];
            $clickType = $clickTypes[array_rand($clickTypes)];
            $clickUrl = 'tel:+84123456789';
            
            if ($clickType == 'zalo') {
                $clickUrl = 'https://zalo.me/123456789';
            } else if ($clickType == 'messenger') {
                $clickUrl = 'https://m.me/chuyendoi';
            } else if ($clickType == 'maps') {
                $clickUrl = 'https://maps.google.com/?q=ChuyenDoi';
            }
            
            $stmt = $db->prepare("
                INSERT INTO clicks (visit_id, click_type, click_url)
                VALUES (?, ?, ?)
            ");
            
            $stmt->bind_param("iss", $visitId, $clickType, $clickUrl);
            $stmt->execute();
        }
    }
    
    return $results;
}

// Function to clean up test data
function cleanupTestData($db) {
    // Get current timestamp
    $currentTime = date('Y-m-d H:i:s');
    
    // Delete clicks associated with test visits
    $stmt = $db->prepare("
        DELETE c FROM clicks c
        JOIN visits v ON c.visit_id = v.id
        WHERE v.visit_time > ?
    ");
    $stmt->bind_param("s", $currentTime);
    $stmt->execute();
    $clicksDeleted = $db->getConnection()->affected_rows;
    
    // Delete test visits
    $stmt = $db->prepare("DELETE FROM visits WHERE visit_time > ?");
    $stmt->bind_param("s", $currentTime);
    $stmt->execute();
    $visitsDeleted = $db->getConnection()->affected_rows;
    
    return [
        'visits_deleted' => $visitsDeleted,
        'clicks_deleted' => $clicksDeleted
    ];
}

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$result = null;

if ($action === 'generate') {
    $count = isset($_GET['count']) ? (int)$_GET['count'] : 5;
    $result = generateTestVisits($db, $count);
} else if ($action === 'cleanup') {
    $result = cleanupTestData($db);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Real-time Tracking - Hệ Thống Tracking IP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
        .btn-group {
            margin-bottom: 20px;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Test Real-time Tracking</h1>
        
        <div class="alert alert-info">
            <p><strong>Instructions:</strong></p>
            <ol>
                <li>Open the <a href="admin/realtime.php" target="_blank">Real-time Tracking Page</a> in a new tab</li>
                <li>Click "Generate Test Visits" below to create test data</li>
                <li>Observe the real-time tracking page to see the new visits appear</li>
                <li>When finished testing, click "Clean Up Test Data" to remove all test data</li>
            </ol>
        </div>
        
        <div class="btn-group">
            <a href="?action=generate&count=1" class="btn btn-primary">Generate 1 Test Visit</a>
            <a href="?action=generate&count=5" class="btn btn-primary">Generate 5 Test Visits</a>
            <a href="?action=generate&count=10" class="btn btn-primary">Generate 10 Test Visits</a>
            <a href="?action=cleanup" class="btn btn-danger">Clean Up Test Data</a>
        </div>
        
        <?php if ($result): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Result</h5>
            </div>
            <div class="card-body">
                <?php if ($action === 'generate'): ?>
                <h6>Generated Test Visits:</h6>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>IP Address</th>
                            <th>Browser</th>
                            <th>OS</th>
                            <th>Location</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result as $visit): ?>
                        <tr>
                            <td><?php echo $visit['id']; ?></td>
                            <td><?php echo $visit['ip']; ?></td>
                            <td><?php echo $visit['browser']; ?></td>
                            <td><?php echo $visit['os']; ?></td>
                            <td><?php echo $visit['location']; ?></td>
                            <td><?php echo $visit['is_bot']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php elseif ($action === 'cleanup'): ?>
                <div class="alert alert-success">
                    <p><strong>Clean Up Complete:</strong></p>
                    <ul>
                        <li><?php echo $result['visits_deleted']; ?> test visits deleted</li>
                        <li><?php echo $result['clicks_deleted']; ?> test clicks deleted</li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Links</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li><a href="admin/realtime.php" target="_blank">Real-time Tracking Page</a></li>
                    <li><a href="admin/index.php">Admin Dashboard</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
