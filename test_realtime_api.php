<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: admin/login.php');
    exit;
}

// Get database instance
$db = Database::getInstance();

// Calculate time 60 minutes ago
$sixtyMinutesAgo = date('Y-m-d H:i:s', strtotime('-60 minutes'));
$fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));

// Get total visitors in the last 60 minutes
$stmt = $db->prepare("
    SELECT COUNT(*) as total
    FROM visits
    WHERE visit_time >= ?
");
$stmt->bind_param("s", $sixtyMinutesAgo);
$stmt->execute();
$totalVisitors = $stmt->get_result()->fetch_assoc()['total'];

// Get current visitors (last 5 minutes)
$stmt = $db->prepare("
    SELECT COUNT(*) as total
    FROM visits
    WHERE visit_time >= ?
");
$stmt->bind_param("s", $fiveMinutesAgo);
$stmt->execute();
$currentVisitors = $stmt->get_result()->fetch_assoc()['total'];

// Get total clicks in the last 60 minutes
$stmt = $db->prepare("
    SELECT COUNT(*) as total
    FROM clicks c
    JOIN visits v ON c.visit_id = v.id
    WHERE c.click_time >= ?
");
$stmt->bind_param("s", $sixtyMinutesAgo);
$stmt->execute();
$totalClicks = $stmt->get_result()->fetch_assoc()['total'];

// Get bot visitors in the last 60 minutes
$stmt = $db->prepare("
    SELECT COUNT(*) as total
    FROM visits
    WHERE visit_time >= ?
    AND is_bot = 1
");
$stmt->bind_param("s", $sixtyMinutesAgo);
$stmt->execute();
$botVisitors = $stmt->get_result()->fetch_assoc()['total'];

// Get recent visitors
$stmt = $db->prepare("
    SELECT v.*, s.name as site_name
    FROM visits v
    JOIN sites s ON v.site_id = s.id
    WHERE v.visit_time >= ?
    ORDER BY v.visit_time DESC
    LIMIT 10
");
$stmt->bind_param("s", $sixtyMinutesAgo);
$stmt->execute();
$visitors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Realtime API</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Test Realtime API</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Visitors</h5>
                                <p class="card-text display-4"><?php echo $totalVisitors; ?></p>
                                <p class="text-muted">Last 60 minutes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Current Visitors</h5>
                                <p class="card-text display-4"><?php echo $currentVisitors; ?></p>
                                <p class="text-muted">Last 5 minutes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Clicks</h5>
                                <p class="card-text display-4"><?php echo $totalClicks; ?></p>
                                <p class="text-muted">Last 60 minutes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Bot Visitors</h5>
                                <p class="card-text display-4"><?php echo $botVisitors; ?></p>
                                <p class="text-muted">Last 60 minutes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Visitors</h5>
            </div>
            <div class="card-body">
                <?php if (count($visitors) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Site</th>
                                <th>IP Address</th>
                                <th>Browser</th>
                                <th>OS</th>
                                <th>Visit Time</th>
                                <th>Is Bot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visitors as $visitor): ?>
                            <tr>
                                <td><?php echo $visitor['id']; ?></td>
                                <td><?php echo htmlspecialchars($visitor['site_name']); ?></td>
                                <td><?php echo htmlspecialchars($visitor['ip_address']); ?></td>
                                <td><?php echo htmlspecialchars($visitor['browser'] . ' ' . $visitor['browser_version']); ?></td>
                                <td><?php echo htmlspecialchars($visitor['os'] . ' ' . $visitor['os_version']); ?></td>
                                <td><?php echo $visitor['visit_time']; ?></td>
                                <td><?php echo $visitor['is_bot'] ? 'Yes' : 'No'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">No visitors found in the last 60 minutes.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">API Response</h5>
            </div>
            <div class="card-body">
                <p>The following is the raw API response from admin/api/realtime_data.php:</p>
                <pre id="api-response" class="bg-light p-3">Loading...</pre>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="admin/realtime.php" class="btn btn-primary">Go to Realtime Dashboard</a>
            <button id="refresh-btn" class="btn btn-secondary ml-2">Refresh Data</button>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load API data
            function loadApiData() {
                $.ajax({
                    url: 'admin/api/realtime_data.php',
                    type: 'GET',
                    data: {
                        site_id: 0,
                        is_bot: -1,
                        last_visit_id: 0
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#api-response').text(JSON.stringify(response, null, 2));
                    },
                    error: function(xhr, status, error) {
                        $('#api-response').text('Error: ' + error + '\n\nResponse: ' + xhr.responseText);
                    }
                });
            }
            
            // Load API data on page load
            loadApiData();
            
            // Refresh button
            $('#refresh-btn').click(function() {
                loadApiData();
            });
        });
    </script>
</body>
</html>
