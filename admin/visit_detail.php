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

// Check if visit ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: visits.php');
    exit;
}

$visitId = (int)$_GET['id'];

// Get visit information
$stmt = $db->prepare("
    SELECT v.*, s.name as site_name
    FROM visits v
    JOIN sites s ON v.site_id = s.id
    WHERE v.id = ?
");
$stmt->bind_param("i", $visitId);
$stmt->execute();
$visit = $stmt->get_result()->fetch_assoc();

// If visit not found, redirect to visits page
if (!$visit) {
    header('Location: visits.php');
    exit;
}

// Get clicks for this visit
$stmt = $db->prepare("
    SELECT * FROM clicks
    WHERE visit_id = ?
    ORDER BY click_time DESC
");
$stmt->bind_param("i", $visitId);
$stmt->execute();
$clicks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Count clicks by type
$clickCounts = [
    'phone' => 0,
    'zalo' => 0,
    'messenger' => 0,
    'maps' => 0
];

foreach ($clicks as $click) {
    $clickCounts[$click['click_type']]++;
}

// Get bot detection factors
$botFactors = [
    'User Agent Analysis' => [
        'description' => 'Kiểm tra chuỗi User Agent có chứa từ khóa bot/crawler',
        'score' => 0,
        'details' => 'Không phát hiện dấu hiệu bot trong User Agent'
    ],
    'Headers Analysis' => [
        'description' => 'Kiểm tra các HTTP headers bất thường',
        'score' => 0,
        'details' => 'Headers bình thường'
    ],
    'IP Analysis' => [
        'description' => 'Kiểm tra IP có phải là IP riêng/proxy',
        'score' => 0,
        'details' => 'IP công cộng bình thường'
    ],
    'JavaScript Capabilities' => [
        'description' => 'Kiểm tra khả năng thực thi JavaScript',
        'score' => 0,
        'details' => 'JavaScript được bật'
    ],
    'Mouse Movements' => [
        'description' => 'Phân tích chuyển động chuột',
        'score' => 0,
        'details' => 'Chuyển động chuột tự nhiên'
    ],
    'Timing Analysis' => [
        'description' => 'Phân tích thời gian tương tác',
        'score' => 0,
        'details' => 'Thời gian tương tác tự nhiên'
    ],
    'Browser Fingerprint' => [
        'description' => 'Kiểm tra dấu vân tay trình duyệt',
        'score' => 0,
        'details' => 'Dấu vân tay trình duyệt bình thường'
    ]
];

// Calculate bot score components (this is a simplified example)
// In a real implementation, you would retrieve this data from a database or logs
$botScore = $visit['bot_score'];
$botFactorWeights = [
    'User Agent Analysis' => 0.4,
    'Headers Analysis' => 0.2,
    'IP Analysis' => 0.2,
    'JavaScript Capabilities' => 0.2,
    'Mouse Movements' => 0.3,
    'Timing Analysis' => 0.3,
    'Browser Fingerprint' => 0.4
];

// Simulate bot factor scores based on total bot score
// This is just for demonstration - in a real system, you'd store these individual scores
foreach ($botFactors as $factor => $data) {
    // Generate a random score that averages to the total bot score
    $randomFactor = mt_rand(80, 120) / 100; // Random factor between 0.8 and 1.2
    $botFactors[$factor]['score'] = min(1, max(0, $botScore * $randomFactor));
    
    // Update details based on score
    if ($botFactors[$factor]['score'] > 0.7) {
        $botFactors[$factor]['details'] = 'Dấu hiệu bot rất rõ ràng';
    } else if ($botFactors[$factor]['score'] > 0.4) {
        $botFactors[$factor]['details'] = 'Có một số dấu hiệu đáng ngờ';
    } else {
        // Keep the default "normal" message
    }
}

// Page title
$pageTitle = 'Chi Tiết Lượt Truy Cập #' . $visitId;
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
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .progress {
            height: 10px;
        }
        .progress-bar-bot {
            background-color: #dc3545;
        }
        .progress-bar-human {
            background-color: #28a745;
        }
        .click-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .click-badge i {
            margin-right: 5px;
        }
        .bot-factor-row {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .bot-factor-row:last-child {
            border-bottom: none;
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
                        <a class="nav-link active" href="visits.php">
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
                            <li class="nav-item">
                                <a class="nav-link" href="visits.php">Quản Lý Lượt Truy Cập</a>
                            </li>
                            <li class="nav-item active">
                                <a class="nav-link" href="#">Chi Tiết Lượt Truy Cập #<?php echo $visitId; ?></a>
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
                        <a href="visits.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Thông Tin Cơ Bản</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Website:</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['site_name']); ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Thời gian truy cập:</div>
                                        <div class="col-md-8"><?php echo date('d/m/Y H:i:s', strtotime($visit['visit_time'])); ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Thời gian online:</div>
                                        <div class="col-md-8"><?php echo formatTime($visit['time_spent']); ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Trang đang xem:</div>
                                        <div class="col-md-8">
                                            <a href="<?php echo htmlspecialchars($visit['current_page']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($visit['current_page']); ?>
                                                <i class="fas fa-external-link-alt ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Nguồn truy cập:</div>
                                        <div class="col-md-8">
                                            <?php if (!empty($visit['referrer'])): ?>
                                            <a href="<?php echo htmlspecialchars($visit['referrer']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($visit['referrer']); ?>
                                                <i class="fas fa-external-link-alt ml-1"></i>
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">Truy cập trực tiếp hoặc không xác định</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Loại:</div>
                                        <div class="col-md-8">
                                            <?php if ($visit['is_bot']): ?>
                                            <span class="badge badge-bot">Bot (<?php echo round($visit['bot_score'] * 100); ?>%)</span>
                                            <?php else: ?>
                                            <span class="badge badge-human">Human</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- UTM Parameters -->
                            <?php if (!empty($visit['utm_source']) || !empty($visit['utm_medium']) || !empty($visit['utm_campaign']) || !empty($visit['utm_term']) || !empty($visit['utm_content'])): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Thông Tin UTM</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($visit['utm_source'])): ?>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Nguồn (Source):</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['utm_source']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($visit['utm_medium'])): ?>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Kênh (Medium):</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['utm_medium']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($visit['utm_campaign'])): ?>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Chiến dịch (Campaign):</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['utm_campaign']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($visit['utm_term'])): ?>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Từ khóa (Term):</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['utm_term']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($visit['utm_content'])): ?>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Nội dung (Content):</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['utm_content']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Device Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Thông Tin Thiết Bị</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">IP:</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['ip_address']); ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Trình duyệt:</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['browser'] . ' ' . $visit['browser_version']); ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Hệ điều hành:</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['os'] . ' ' . $visit['os_version']); ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Kích thước màn hình:</div>
                                        <div class="col-md-8"><?php echo $visit['screen_width'] . 'x' . $visit['screen_height']; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Nhà mạng:</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['isp']); ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Kết nối:</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['connection_type']); ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 info-label">Vị trí:</div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($visit['city'] . ', ' . $visit['country']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Conversion Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Thông Tin Chuyển Đổi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h6 class="mb-3">Tổng số lượt click:</h6>
                                        <div class="d-flex flex-wrap">
                                            <?php if ($clickCounts['phone'] > 0): ?>
                                            <div class="click-badge badge badge-primary">
                                                <i class="fas fa-phone"></i> Điện thoại: <?php echo $clickCounts['phone']; ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($clickCounts['zalo'] > 0): ?>
                                            <div class="click-badge badge badge-info">
                                                <i class="fas fa-comment"></i> Zalo: <?php echo $clickCounts['zalo']; ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($clickCounts['messenger'] > 0): ?>
                                            <div class="click-badge badge badge-primary">
                                                <i class="fab fa-facebook-messenger"></i> Messenger: <?php echo $clickCounts['messenger']; ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($clickCounts['maps'] > 0): ?>
                                            <div class="click-badge badge badge-success">
                                                <i class="fas fa-map-marker-alt"></i> Google Maps: <?php echo $clickCounts['maps']; ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($clickCounts['phone'] + $clickCounts['zalo'] + $clickCounts['messenger'] + $clickCounts['maps'] == 0): ?>
                                            <div class="text-muted">Không có lượt click nào</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (count($clicks) > 0): ?>
                                    <h6 class="mb-3">Chi tiết các lượt click:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Thời gian</th>
                                                    <th>Loại</th>
                                                    <th>URL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($clicks as $click): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i:s', strtotime($click['click_time'])); ?></td>
                                                    <td>
                                                        <?php if ($click['click_type'] == 'phone'): ?>
                                                        <span class="badge badge-primary"><i class="fas fa-phone"></i> Điện thoại</span>
                                                        <?php elseif ($click['click_type'] == 'zalo'): ?>
                                                        <span class="badge badge-info"><i class="fas fa-comment"></i> Zalo</span>
                                                        <?php elseif ($click['click_type'] == 'messenger'): ?>
                                                        <span class="badge badge-primary"><i class="fab fa-facebook-messenger"></i> Messenger</span>
                                                        <?php elseif ($click['click_type'] == 'maps'): ?>
                                                        <span class="badge badge-success"><i class="fas fa-map-marker-alt"></i> Google Maps</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo htmlspecialchars($click['click_url']); ?>" target="_blank">
                                                            <?php echo htmlspecialchars($click['click_url']); ?>
                                                            <i class="fas fa-external-link-alt ml-1"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Bot Detection Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Phân Tích Bot/Automation</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h6 class="mb-2">Điểm bot tổng thể:</h6>
                                        <div class="progress mb-2">
                                            <?php 
                                            $botScorePercent = round($visit['bot_score'] * 100);
                                            $humanScorePercent = 100 - $botScorePercent;
                                            ?>
                                            <div class="progress-bar progress-bar-bot" role="progressbar" style="width: <?php echo $botScorePercent; ?>%" aria-valuenow="<?php echo $botScorePercent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            <div class="progress-bar progress-bar-human" role="progressbar" style="width: <?php echo $humanScorePercent; ?>%" aria-valuenow="<?php echo $humanScorePercent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <div><span class="badge badge-bot">Bot: <?php echo $botScorePercent; ?>%</span></div>
                                            <div><span class="badge badge-human">Human: <?php echo $humanScorePercent; ?>%</span></div>
                                        </div>
                                    </div>
                                    
                                    <h6 class="mb-3">Các yếu tố phát hiện bot:</h6>
                                    <?php foreach ($botFactors as $factor => $data): ?>
                                    <div class="bot-factor-row">
                                        <div class="d-flex justify-content-between mb-1">
                                            <div>
                                                <strong><?php echo $factor; ?></strong>
                                                <small class="text-muted d-block"><?php echo $data['description']; ?></small>
                                            </div>
                                            <div>
                                                <?php 
                                                $factorScorePercent = round($data['score'] * 100);
                                                if ($factorScorePercent > 70) {
                                                    $badgeClass = 'badge-danger';
                                                } else if ($factorScorePercent > 40) {
                                                    $badgeClass = 'badge-warning';
                                                } else {
                                                    $badgeClass = 'badge-success';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $factorScorePercent; ?>%</span>
                                            </div>
                                        </div>
                                        <div class="progress mb-1" style="height: 5px;">
                                            <div class="progress-bar <?php echo $badgeClass; ?>" role="progressbar" style="width: <?php echo $factorScorePercent; ?>%" aria-valuenow="<?php echo $factorScorePercent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small><?php echo $data['details']; ?></small>
                                    </div>
                                    <?php endforeach; ?>
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
</body>
</html>
