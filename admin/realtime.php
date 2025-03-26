<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get database instance
$db = Database::getInstance();

// Get user information
$userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();

// Process filters
$siteId = isset($_GET['site_id']) ? (int)$_GET['site_id'] : 0;
$isBot = isset($_GET['is_bot']) ? (int)$_GET['is_bot'] : 0; // Default to humans only

// Get sites for filter
$stmt = $db->prepare("SELECT id, name FROM sites ORDER BY name");
$stmt->execute();
$sites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Page title
$pageTitle = 'Theo Dõi Truy Cập Thời Gian Thực';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Hệ Thống Tracking IP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: rgba(255, 255, 255, 1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .navbar-brand {
            font-weight: 700;
        }
        .dropdown-menu {
            right: 0;
            left: auto;
        }
        .btn-primary {
            background-color: #3961AA;
            border-color: #3961AA;
        }
        .btn-primary:hover {
            background-color: #2c4e8a;
            border-color: #2c4e8a;
        }
        .badge-bot {
            background-color: #dc3545;
        }
        .badge-human {
            background-color: #28a745;
        }
        .filter-card {
            background-color: #f8f9fa;
            border: none;
        }
        .visitor-card {
            transition: all 0.3s ease;
            border-left: 5px solid #3961AA;
        }
        .visitor-card.new {
            animation: highlight 3s ease-out;
        }
        .visitor-card .visitor-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .visitor-card .visitor-details {
            font-size: 0.9rem;
        }
        .visitor-card .visitor-page {
            font-size: 0.85rem;
            color: #495057;
            word-break: break-all;
        }
        .stats-card {
            text-align: center;
            padding: 15px;
        }
        .stats-card .stats-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #3961AA;
        }
        .stats-card .stats-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .stats-card .stats-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .refresh-control {
            display: flex;
            align-items: center;
        }
        .refresh-control select {
            width: auto;
            margin-left: 10px;
        }
        .last-updated {
            font-size: 0.85rem;
            color: #6c757d;
            margin-left: 15px;
        }
        @keyframes highlight {
            0% { background-color: #fffacd; }
            100% { background-color: #ffffff; }
        }
        #visitors-container {
            max-height: 800px;
            overflow-y: auto;
        }
        .location-flag {
            width: 20px;
            height: 15px;
            margin-right: 5px;
        }
        .visitor-actions {
            display: flex;
            gap: 5px;
        }
        .no-visitors {
            text-align: center;
            padding: 50px 0;
        }
        .no-visitors i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 d-none d-md-block sidebar">
                <div class="text-center mb-4">
                    <h5 class="text-white">Tracking IP</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sites.php">
                            <i class="fas fa-globe"></i> Websites
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="visits.php">
                            <i class="fas fa-eye"></i> Visits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="realtime.php">
                            <i class="fas fa-clock"></i> Real-time
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clicks.php">
                            <i class="fas fa-mouse-pointer"></i> Clicks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="anomalies.php">
                            <i class="fas fa-exclamation-triangle"></i> Anomalies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fraud.php">
                            <i class="fas fa-ban"></i> Fraud Patterns
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <main role="main" class="col-md-10 ml-sm-auto px-4">
                <!-- Top navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item active">
                                <a class="nav-link" href="realtime.php">Theo Dõi Truy Cập Thời Gian Thực</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($currentUser['username']); ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="fas fa-user"></i> Profile
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                
                <!-- Page content -->
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h2"><?php echo $pageTitle; ?></h1>
                        <div class="refresh-control">
                            <span>Tự động làm mới:</span>
                            <select id="refresh-interval" class="form-control form-control-sm">
                                <option value="5">5 giây</option>
                                <option value="10" selected>10 giây</option>
                                <option value="30">30 giây</option>
                                <option value="60">1 phút</option>
                            </select>
                            <span class="last-updated" id="last-updated">Cập nhật lần cuối: Vừa xong</span>
                        </div>
                    </div>
                    
                    <!-- Stats cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stats-value" id="total-visitors">0</div>
                                <div class="stats-label">Người truy cập (60 phút qua)</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="stats-value" id="current-visitors">0</div>
                                <div class="stats-label">Đang truy cập (5 phút qua)</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-mouse-pointer"></i>
                                </div>
                                <div class="stats-value" id="total-clicks">0</div>
                                <div class="stats-label">Lượt click (60 phút qua)</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="stats-value" id="bot-visitors">0</div>
                                <div class="stats-label">Bot (60 phút qua)</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card filter-card mb-4">
                        <div class="card-body">
                            <form id="filter-form" class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="site_id">Website</label>
                                    <select class="form-control" id="site_id" name="site_id">
                                        <option value="0">Tất cả websites</option>
                                        <?php foreach ($sites as $site): ?>
                                        <option value="<?php echo $site['id']; ?>" <?php echo $siteId == $site['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($site['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="is_bot">Loại</label>
                                    <select class="form-control" id="is_bot" name="is_bot">
                                        <option value="-1">Tất cả</option>
                                        <option value="0" selected>Người dùng</option>
                                        <option value="1">Bot</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Lọc
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Visitors list -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Người truy cập trong 60 phút qua</h5>
                                <button id="refresh-now" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-sync-alt"></i> Làm mới ngay
                                </button>
                            </div>
                            
                            <div id="visitors-container">
                                <!-- Visitors will be loaded here via AJAX -->
                                <div class="no-visitors" id="no-visitors">
                                    <i class="fas fa-users"></i>
                                    <h4>Không có người truy cập nào</h4>
                                    <p>Chưa có người truy cập nào trong 60 phút qua.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <footer class="mt-5 mb-3">
                    <div class="text-center">
                        <p class="text-muted">&copy; <?php echo date('Y'); ?> IP Tracking System</p>
                    </div>
                </footer>
            </main>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            // Set up variables
            let refreshInterval = 10; // Default refresh interval in seconds
            let refreshTimer;
            let lastVisitId = 0;
            let knownVisitIds = [];
            
            // Initialize
            loadVisitors();
            startRefreshTimer();
            
            // Handle refresh interval change
            $('#refresh-interval').change(function() {
                refreshInterval = parseInt($(this).val());
                restartRefreshTimer();
            });
            
            // Handle manual refresh
            $('#refresh-now').click(function() {
                loadVisitors();
            });
            
            // Handle filter form submission
            $('#filter-form').submit(function(e) {
                e.preventDefault();
                loadVisitors();
            });
            
            // Function to load visitors
            function loadVisitors() {
                const siteId = $('#site_id').val();
                const isBot = $('#is_bot').val();
                
                $.ajax({
                    url: 'api/realtime_data.php',
                    type: 'GET',
                    data: {
                        site_id: siteId,
                        is_bot: isBot,
                        last_visit_id: lastVisitId
                    },
                    dataType: 'json',
                    success: function(response) {
                        updateStats(response.stats);
                        
                        // If this is the first load, replace all content
                        if (lastVisitId === 0) {
                            $('#visitors-container').empty();
                            knownVisitIds = [];
                        }
                        
                        // Check if we have visitors
                        if (response.visitors.length === 0 && knownVisitIds.length === 0) {
                            $('#visitors-container').html(`
                                <div class="no-visitors" id="no-visitors">
                                    <i class="fas fa-users"></i>
                                    <h4>Không có người truy cập nào</h4>
                                    <p>Chưa có người truy cập nào trong 60 phút qua.</p>
                                </div>
                            `);
                            return;
                        } else {
                            $('#no-visitors').remove();
                        }
                        
                        // Add new visitors
                        response.visitors.forEach(function(visitor) {
                            // Skip if we already know this visit
                            if (knownVisitIds.includes(visitor.id)) {
                                return;
                            }
                            
                            // Add to known visits
                            knownVisitIds.push(visitor.id);
                            
                            // Update last visit ID if this is the newest
                            if (visitor.id > lastVisitId) {
                                lastVisitId = visitor.id;
                            }
                            
                            // Format the visitor card
                            const visitorHtml = formatVisitorCard(visitor);
                            
                            // Add to the container
                            $('#visitors-container').prepend(visitorHtml);
                            
                            // Highlight new visitors
                            setTimeout(function() {
                                $(`#visitor-${visitor.id}`).addClass('new');
                            }, 100);
                        });
                        
                        // Update last updated time
                        $('#last-updated').text('Cập nhật lần cuối: ' + moment().format('HH:mm:ss'));
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading visitors:', error);
                    }
                });
            }
            
            // Function to format visitor card
            function formatVisitorCard(visitor) {
                const visitTime = moment(visitor.visit_time).format('HH:mm:ss');
                const timeSince = moment(visitor.visit_time).fromNow();
                
                // Format visitor type badge
                let visitorTypeBadge = '';
                if (visitor.is_bot) {
                    visitorTypeBadge = `<span class="badge badge-bot">Bot (${Math.round(visitor.bot_score * 100)}%)</span>`;
                } else {
                    visitorTypeBadge = `<span class="badge badge-human">Human</span>`;
                }
                
                // Format location
                const location = `${visitor.city}, ${visitor.country}`;
                
                return `
                    <div class="card visitor-card mb-3" id="visitor-${visitor.id}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user mr-2"></i>
                                    ${visitor.ip_address}
                                </h5>
                                <div class="visitor-time">
                                    <i class="far fa-clock mr-1"></i> ${visitTime} (${timeSince})
                                </div>
                            </div>
                            
                            <div class="row visitor-details mb-2">
                                <div class="col-md-3">
                                    <i class="fas fa-globe mr-1"></i> ${visitor.site_name}
                                </div>
                                <div class="col-md-3">
                                    <i class="fas fa-browser mr-1"></i> ${visitor.browser} ${visitor.browser_version}
                                </div>
                                <div class="col-md-3">
                                    <i class="fas fa-desktop mr-1"></i> ${visitor.os} ${visitor.os_version}
                                </div>
                                <div class="col-md-3">
                                    <i class="fas fa-map-marker-alt mr-1"></i> ${location}
                                </div>
                            </div>
                            
                            <div class="visitor-page mb-2">
                                <i class="fas fa-link mr-1"></i> ${visitor.current_page}
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    ${visitorTypeBadge}
                                </div>
                                <div class="visitor-actions">
                                    <a href="visit_detail.php?id=${visitor.id}" class="btn btn-sm btn-info">
                                        <i class="fas fa-info-circle"></i> Chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Function to update stats
            function updateStats(stats) {
                $('#total-visitors').text(stats.total_visitors);
                $('#current-visitors').text(stats.current_visitors);
                $('#total-clicks').text(stats.total_clicks);
                $('#bot-visitors').text(stats.bot_visitors);
            }
            
            // Function to start refresh timer
            function startRefreshTimer() {
                refreshTimer = setInterval(loadVisitors, refreshInterval * 1000);
            }
            
            // Function to restart refresh timer
            function restartRefreshTimer() {
                clearInterval(refreshTimer);
                startRefreshTimer();
            }
        });
    </script>
</body>
</html>
