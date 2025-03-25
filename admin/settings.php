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

// Process actions
$message = '';
$messageType = '';

// Update bot detection settings
if (isset($_POST['action']) && $_POST['action'] === 'update_bot_settings') {
    $botScoreThreshold = isset($_POST['bot_score_threshold']) ? (float)$_POST['bot_score_threshold'] : 0.7;
    $enableBotDetection = isset($_POST['enable_bot_detection']) ? 1 : 0;
    $hideButtonsForBots = isset($_POST['hide_buttons_for_bots']) ? 1 : 0;
    
    // Update settings in config file
    $configFile = __DIR__ . '/../includes/config.php';
    $configContent = file_get_contents($configFile);
    
    // Update bot score threshold
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]BOT_SCORE_THRESHOLD[\'"]\s*,\s*([0-9.]+)\s*\)\s*;/i',
        "define('BOT_SCORE_THRESHOLD', $botScoreThreshold);",
        $configContent
    );
    
    // Update enable bot detection
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]ENABLE_BOT_DETECTION[\'"]\s*,\s*(true|false)\s*\)\s*;/i',
        "define('ENABLE_BOT_DETECTION', " . ($enableBotDetection ? 'true' : 'false') . ");",
        $configContent
    );
    
    // Update hide buttons for bots
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]HIDE_BUTTONS_FOR_BOTS[\'"]\s*,\s*(true|false)\s*\)\s*;/i',
        "define('HIDE_BUTTONS_FOR_BOTS', " . ($hideButtonsForBots ? 'true' : 'false') . ");",
        $configContent
    );
    
    // Save updated config
    if (file_put_contents($configFile, $configContent)) {
        $message = 'Cài đặt phát hiện bot đã được cập nhật thành công';
        $messageType = 'success';
    } else {
        $message = 'Lỗi khi cập nhật cài đặt phát hiện bot';
        $messageType = 'danger';
    }
}

// Update notification settings
if (isset($_POST['action']) && $_POST['action'] === 'update_notification_settings') {
    $enableEmailNotifications = isset($_POST['enable_email_notifications']) ? 1 : 0;
    $notificationEmail = isset($_POST['notification_email']) ? sanitizeInput($_POST['notification_email']) : '';
    $notifyOnHighSeverity = isset($_POST['notify_on_high_severity']) ? 1 : 0;
    $notifyOnMediumSeverity = isset($_POST['notify_on_medium_severity']) ? 1 : 0;
    $notifyOnLowSeverity = isset($_POST['notify_on_low_severity']) ? 1 : 0;
    
    // Update settings in config file
    $configFile = __DIR__ . '/../includes/config.php';
    $configContent = file_get_contents($configFile);
    
    // Update enable email notifications
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]ENABLE_EMAIL_NOTIFICATIONS[\'"]\s*,\s*(true|false)\s*\)\s*;/i',
        "define('ENABLE_EMAIL_NOTIFICATIONS', " . ($enableEmailNotifications ? 'true' : 'false') . ");",
        $configContent
    );
    
    // Update notification email
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]NOTIFICATION_EMAIL[\'"]\s*,\s*[\'"].*[\'"]\s*\)\s*;/i',
        "define('NOTIFICATION_EMAIL', '$notificationEmail');",
        $configContent
    );
    
    // Update notify on high severity
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]NOTIFY_ON_HIGH_SEVERITY[\'"]\s*,\s*(true|false)\s*\)\s*;/i',
        "define('NOTIFY_ON_HIGH_SEVERITY', " . ($notifyOnHighSeverity ? 'true' : 'false') . ");",
        $configContent
    );
    
    // Update notify on medium severity
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]NOTIFY_ON_MEDIUM_SEVERITY[\'"]\s*,\s*(true|false)\s*\)\s*;/i',
        "define('NOTIFY_ON_MEDIUM_SEVERITY', " . ($notifyOnMediumSeverity ? 'true' : 'false') . ");",
        $configContent
    );
    
    // Update notify on low severity
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]NOTIFY_ON_LOW_SEVERITY[\'"]\s*,\s*(true|false)\s*\)\s*;/i',
        "define('NOTIFY_ON_LOW_SEVERITY', " . ($notifyOnLowSeverity ? 'true' : 'false') . ");",
        $configContent
    );
    
    // Save updated config
    if (file_put_contents($configFile, $configContent)) {
        $message = 'Cài đặt thông báo đã được cập nhật thành công';
        $messageType = 'success';
    } else {
        $message = 'Lỗi khi cập nhật cài đặt thông báo';
        $messageType = 'danger';
    }
}

// Update system settings
if (isset($_POST['action']) && $_POST['action'] === 'update_system_settings') {
    $systemName = isset($_POST['system_name']) ? sanitizeInput($_POST['system_name']) : '';
    $systemUrl = isset($_POST['system_url']) ? sanitizeInput($_POST['system_url']) : '';
    $debugMode = isset($_POST['debug_mode']) ? 1 : 0;
    
    // Update settings in config file
    $configFile = __DIR__ . '/../includes/config.php';
    $configContent = file_get_contents($configFile);
    
    // Update system name
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]SYSTEM_NAME[\'"]\s*,\s*[\'"].*[\'"]\s*\)\s*;/i',
        "define('SYSTEM_NAME', '$systemName');",
        $configContent
    );
    
    // Update system URL
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]SYSTEM_URL[\'"]\s*,\s*[\'"].*[\'"]\s*\)\s*;/i',
        "define('SYSTEM_URL', '$systemUrl');",
        $configContent
    );
    
    // Update debug mode
    $configContent = preg_replace(
        '/define\s*\(\s*[\'"]DEBUG_MODE[\'"]\s*,\s*(true|false)\s*\)\s*;/i',
        "define('DEBUG_MODE', " . ($debugMode ? 'true' : 'false') . ");",
        $configContent
    );
    
    // Save updated config
    if (file_put_contents($configFile, $configContent)) {
        $message = 'Cài đặt hệ thống đã được cập nhật thành công';
        $messageType = 'success';
    } else {
        $message = 'Lỗi khi cập nhật cài đặt hệ thống';
        $messageType = 'danger';
    }
}

// Get current settings
$botScoreThreshold = defined('BOT_SCORE_THRESHOLD') ? BOT_SCORE_THRESHOLD : 0.7;
$enableBotDetection = defined('ENABLE_BOT_DETECTION') ? ENABLE_BOT_DETECTION : true;
$hideButtonsForBots = defined('HIDE_BUTTONS_FOR_BOTS') ? HIDE_BUTTONS_FOR_BOTS : true;

$enableEmailNotifications = defined('ENABLE_EMAIL_NOTIFICATIONS') ? ENABLE_EMAIL_NOTIFICATIONS : false;
$notificationEmail = defined('NOTIFICATION_EMAIL') ? NOTIFICATION_EMAIL : '';
$notifyOnHighSeverity = defined('NOTIFY_ON_HIGH_SEVERITY') ? NOTIFY_ON_HIGH_SEVERITY : true;
$notifyOnMediumSeverity = defined('NOTIFY_ON_MEDIUM_SEVERITY') ? NOTIFY_ON_MEDIUM_SEVERITY : false;
$notifyOnLowSeverity = defined('NOTIFY_ON_LOW_SEVERITY') ? NOTIFY_ON_LOW_SEVERITY : false;

$systemName = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'IP Tracking System';
$systemUrl = defined('SYSTEM_URL') ? SYSTEM_URL : '';
$debugMode = defined('DEBUG_MODE') ? DEBUG_MODE : false;

// Page title
$pageTitle = 'Cài Đặt Hệ Thống';
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
        .settings-icon {
            font-size: 1.5rem;
            color: #3961AA;
            margin-right: 15px;
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
                        <a class="nav-link active" href="settings.php">
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
                                <a class="nav-link" href="settings.php">Cài Đặt Hệ Thống</a>
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
                    <h1 class="h2 mb-4"><?php echo $pageTitle; ?></h1>
                    
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Settings tabs -->
                    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="system-tab" data-toggle="tab" href="#system" role="tab" aria-controls="system" aria-selected="true">
                                <i class="fas fa-server"></i> Hệ thống
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="bot-tab" data-toggle="tab" href="#bot" role="tab" aria-controls="bot" aria-selected="false">
                                <i class="fas fa-robot"></i> Phát hiện Bot
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="notification-tab" data-toggle="tab" href="#notification" role="tab" aria-controls="notification" aria-selected="false">
                                <i class="fas fa-bell"></i> Thông báo
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="settingsTabsContent">
                        <!-- System settings -->
                        <div class="tab-pane fade show active" id="system" role="tabpanel" aria-labelledby="system-tab">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <i class="fas fa-server settings-icon"></i>
                                    <h5 class="card-title mb-0">Cài đặt hệ thống</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="update_system_settings">
                                        
                                        <div class="form-group row">
                                            <label for="system_name" class="col-sm-3 col-form-label">Tên hệ thống</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="system_name" name="system_name" value="<?php echo htmlspecialchars($systemName); ?>">
                                                <small class="form-text text-muted">Tên hiển thị của hệ thống</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="system_url" class="col-sm-3 col-form-label">URL hệ thống</label>
                                            <div class="col-sm-9">
                                                <input type="url" class="form-control" id="system_url" name="system_url" value="<?php echo htmlspecialchars($systemUrl); ?>">
                                                <small class="form-text text-muted">URL gốc của hệ thống (ví dụ: https://chuyendoi.io.vn)</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <div class="col-sm-3">Chế độ gỡ lỗi</div>
                                            <div class="col-sm-9">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="debug_mode" name="debug_mode" <?php echo $debugMode ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="debug_mode">Bật chế độ gỡ lỗi</label>
                                                </div>
                                                <small class="form-text text-muted">Hiển thị thông tin gỡ lỗi chi tiết (chỉ nên bật khi cần thiết)</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <div class="col-sm-9 offset-sm-3">
                                                <button type="submit" class="btn btn-primary">Lưu cài đặt hệ thống</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bot detection settings -->
                        <div class="tab-pane fade" id="bot" role="tabpanel" aria-labelledby="bot-tab">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <i class="fas fa-robot settings-icon"></i>
                                    <h5 class="card-title mb-0">Cài đặt phát hiện Bot</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="update_bot_settings">
                                        
                                        <div class="form-group row">
                                            <div class="col-sm-3">Phát hiện Bot</div>
                                            <div class="col-sm-9">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="enable_bot_detection" name="enable_bot_detection" <?php echo $enableBotDetection ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="enable_bot_detection">Bật phát hiện Bot</label>
                                                </div>
                                                <small class="form-text text-muted">Phát hiện và đánh dấu các lượt truy cập từ Bot</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="bot_score_threshold" class="col-sm-3 col-form-label">Ngưỡng điểm Bot</label>
                                            <div class="col-sm-9">
                                                <input type="range" class="custom-range" id="bot_score_threshold" name="bot_score_threshold" min="0" max="1" step="0.05" value="<?php echo $botScoreThreshold; ?>">
                                                <div class="d-flex justify-content-between">
                                                    <small>0 (Dễ dàng)</small>
                                                    <small id="bot_score_value"><?php echo $botScoreThreshold; ?></small>
                                                    <small>1 (Nghiêm ngặt)</small>
                                                </div>
                                                <small class="form-text text-muted">Ngưỡng điểm để xác định một lượt truy cập là Bot</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <div class="col-sm-3">Ẩn nút cho Bot</div>
                                            <div class="col-sm-9">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="hide_buttons_for_bots" name="hide_buttons_for_bots" <?php echo $hideButtonsForBots ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="hide_buttons_for_bots">Ẩn nút chuyển đổi cho Bot</label>
                                                </div>
                                                <small class="form-text text-muted">Ẩn các nút chuyển đổi (điện thoại, Zalo, Messenger, Maps) cho các lượt truy cập được xác định là Bot</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <div class="col-sm-9 offset-sm-3">
                                                <button type="submit" class="btn btn-primary">Lưu cài đặt phát hiện Bot</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification settings -->
                        <div class="tab-pane fade" id="notification" role="tabpanel" aria-labelledby="notification-tab">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <i class="fas fa-bell settings-icon"></i>
                                    <h5 class="card-title mb-0">Cài đặt thông báo</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="update_notification_settings">
                                        
                                        <div class="form-group row">
                                            <div class="col-sm-3">Thông báo qua email</div>
                                            <div class="col-sm-9">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="enable_email_notifications" name="enable_email_notifications" <?php echo $enableEmailNotifications ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="enable_email_notifications">Bật thông báo qua email</label>
                                                </div>
                                                <small class="form-text text-muted">Gửi thông báo qua email khi phát hiện anomaly</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="notification_email" class="col-sm-3 col-form-label">Email thông báo</label>
                                            <div class="col-sm-9">
                                                <input type="email" class="form-control" id="notification_email" name="notification_email" value="<?php echo htmlspecialchars($notificationEmail); ?>">
                                                <small class="form-text text-muted">Địa chỉ email nhận thông báo</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <div class="col-sm-3">Mức độ thông báo</div>
                                            <div class="col-sm-9">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="notify_on_high_severity" name="notify_on_high_severity" <?php echo $notifyOnHighSeverity ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="notify_on_high_severity">Thông báo anomaly mức độ cao</label>
                                                </div>
                                                <div class="custom-control custom-checkbox mt-2">
                                                    <input type="checkbox" class="custom-control-input" id="notify_on_medium_severity" name="notify_on_medium_severity" <?php echo $notifyOnMediumSeverity ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="notify_on_medium_severity">Thông báo anomaly mức độ trung bình</label>
                                                </div>
                                                <div class="custom-control custom-checkbox mt-2">
                                                    <input type="checkbox" class="custom-control-input" id="notify_on_low_severity" name="notify_on_low_severity" <?php echo $notifyOnLowSeverity ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="notify_on_low_severity">Thông báo anomaly mức độ thấp</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <div class="col-sm-9 offset-sm-3">
                                                <button type="submit" class="btn btn-primary">Lưu cài đặt thông báo</button>
                                            </div>
                                        </div>
                                    </form>
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
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update bot score threshold value display
        document.getElementById('bot_score_threshold').addEventListener('input', function() {
            document.getElementById('bot_score_value').textContent = this.value;
        });
    </script>
</body>
</html>
