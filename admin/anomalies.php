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

// Resolve anomaly
if (isset($_POST['action']) && $_POST['action'] === 'resolve' && isset($_POST['anomaly_id'])) {
    $anomalyId = (int)$_POST['anomaly_id'];
    $resolutionNotes = isset($_POST['resolution_notes']) ? sanitizeInput($_POST['resolution_notes']) : '';
    
    // Update anomaly
    $stmt = $db->prepare("UPDATE anomalies SET is_resolved = 1, resolution_notes = ? WHERE id = ?");
    $stmt->bind_param("si", $resolutionNotes, $anomalyId);
    
    if ($stmt->execute()) {
        $message = 'Anomaly đã được đánh dấu là đã giải quyết';
        $messageType = 'success';
    } else {
        $message = 'Lỗi khi cập nhật anomaly: ' . $db->error();
        $messageType = 'danger';
    }
}

// Process filters
$siteId = isset($_GET['site_id']) ? (int)$_GET['site_id'] : 0;
$anomalyType = isset($_GET['anomaly_type']) ? $_GET['anomaly_type'] : '';
$severity = isset($_GET['severity']) ? $_GET['severity'] : '';
$isResolved = isset($_GET['is_resolved']) ? (int)$_GET['is_resolved'] : -1; // -1 = all, 0 = unresolved, 1 = resolved
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$query = "
    SELECT a.*, s.name as site_name
    FROM anomalies a
    JOIN sites s ON a.site_id = s.id
    WHERE 1=1
";
$countQuery = "
    SELECT COUNT(*) as total
    FROM anomalies a
    JOIN sites s ON a.site_id = s.id
    WHERE 1=1
";
$params = [];
$types = "";

// Add filters
if ($siteId > 0) {
    $query .= " AND a.site_id = ?";
    $countQuery .= " AND a.site_id = ?";
    $params[] = $siteId;
    $types .= "i";
}

if (!empty($anomalyType)) {
    $query .= " AND a.anomaly_type = ?";
    $countQuery .= " AND a.anomaly_type = ?";
    $params[] = $anomalyType;
    $types .= "s";
}

if (!empty($severity)) {
    $query .= " AND a.severity = ?";
    $countQuery .= " AND a.severity = ?";
    $params[] = $severity;
    $types .= "s";
}

if ($isResolved >= 0) {
    $query .= " AND a.is_resolved = ?";
    $countQuery .= " AND a.is_resolved = ?";
    $params[] = $isResolved;
    $types .= "i";
}

if (!empty($dateFrom)) {
    $query .= " AND DATE(a.detected_at) >= ?";
    $countQuery .= " AND DATE(a.detected_at) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

if (!empty($dateTo)) {
    $query .= " AND DATE(a.detected_at) <= ?";
    $countQuery .= " AND DATE(a.detected_at) <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

// Add order and limit
$query .= " ORDER BY a.detected_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types .= "ii";

// Get total count
$stmt = $db->prepare($countQuery);
if (!empty($types)) {
    // Remove the last two parameters (offset and perPage) for the count query
    $countParams = array_slice($params, 0, -2);
    $countTypes = substr($types, 0, -2);
    
    if (!empty($countTypes)) {
        $stmt->bind_param($countTypes, ...$countParams);
    }
}
$stmt->execute();
$totalCount = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalCount / $perPage);

// Get anomalies
$stmt = $db->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$anomalies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get sites for filter
$stmt = $db->prepare("SELECT id, name FROM sites ORDER BY name");
$stmt->execute();
$sites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get anomaly stats
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_resolved = 0 THEN 1 ELSE 0 END) as unresolved,
        SUM(CASE WHEN is_resolved = 1 THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high_severity,
        SUM(CASE WHEN severity = 'medium' THEN 1 ELSE 0 END) as medium_severity,
        SUM(CASE WHEN severity = 'low' THEN 1 ELSE 0 END) as low_severity
    FROM anomalies
");
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Page title
$pageTitle = 'Quản Lý Anomalies';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Hệ Thống Tracking IP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
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
        .badge-high {
            background-color: #dc3545;
        }
        .badge-medium {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-low {
            background-color: #17a2b8;
        }
        .badge-resolved {
            background-color: #28a745;
        }
        .badge-pending {
            background-color: #6c757d;
        }
        .filter-card {
            background-color: #f8f9fa;
            border: none;
        }
        .pagination {
            margin-bottom: 0;
        }
        .stats-card {
            border-left: 4px solid #3961AA;
        }
        .stats-card.unresolved {
            border-left-color: #dc3545;
        }
        .stats-card.resolved {
            border-left-color: #28a745;
        }
        .stats-card.high {
            border-left-color: #dc3545;
        }
        .stats-card.medium {
            border-left-color: #ffc107;
        }
        .stats-card.low {
            border-left-color: #17a2b8;
        }
        .stats-icon {
            font-size: 2rem;
            color: #3961AA;
        }
        .stats-count {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .stats-label {
            font-size: 0.9rem;
            color: #6c757d;
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
                        <a class="nav-link active" href="anomalies.php">
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
                                <a class="nav-link" href="anomalies.php">Quản Lý Anomalies</a>
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
                    
                    <!-- Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stats-card unresolved">
                                <div class="card-body d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-exclamation-triangle stats-icon text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="stats-count"><?php echo $stats['unresolved']; ?></div>
                                        <div class="stats-label">Anomalies chưa giải quyết</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stats-card high">
                                <div class="card-body d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-radiation stats-icon text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="stats-count"><?php echo $stats['high_severity']; ?></div>
                                        <div class="stats-label">Mức độ nghiêm trọng cao</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stats-card resolved">
                                <div class="card-body d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-check-circle stats-icon text-success"></i>
                                    </div>
                                    <div>
                                        <div class="stats-count"><?php echo $stats['resolved']; ?></div>
                                        <div class="stats-label">Anomalies đã giải quyết</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card filter-card mb-4">
                        <div class="card-body">
                            <form method="get" action="" class="row">
                                <div class="col-md-2 mb-3">
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
                                <div class="col-md-2 mb-3">
                                    <label for="anomaly_type">Loại anomaly</label>
                                    <select class="form-control" id="anomaly_type" name="anomaly_type">
                                        <option value="" <?php echo empty($anomalyType) ? 'selected' : ''; ?>>Tất cả</option>
                                        <option value="high_cr" <?php echo $anomalyType === 'high_cr' ? 'selected' : ''; ?>>Tỷ lệ chuyển đổi cao</option>
                                        <option value="pattern" <?php echo $anomalyType === 'pattern' ? 'selected' : ''; ?>>Mẫu hình bất thường</option>
                                        <option value="timing" <?php echo $anomalyType === 'timing' ? 'selected' : ''; ?>>Thời gian bất thường</option>
                                        <option value="cluster" <?php echo $anomalyType === 'cluster' ? 'selected' : ''; ?>>Cụm bất thường</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="severity">Mức độ nghiêm trọng</label>
                                    <select class="form-control" id="severity" name="severity">
                                        <option value="" <?php echo empty($severity) ? 'selected' : ''; ?>>Tất cả</option>
                                        <option value="high" <?php echo $severity === 'high' ? 'selected' : ''; ?>>Cao</option>
                                        <option value="medium" <?php echo $severity === 'medium' ? 'selected' : ''; ?>>Trung bình</option>
                                        <option value="low" <?php echo $severity === 'low' ? 'selected' : ''; ?>>Thấp</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="is_resolved">Trạng thái</label>
                                    <select class="form-control" id="is_resolved" name="is_resolved">
                                        <option value="-1" <?php echo $isResolved == -1 ? 'selected' : ''; ?>>Tất cả</option>
                                        <option value="0" <?php echo $isResolved === 0 ? 'selected' : ''; ?>>Chưa giải quyết</option>
                                        <option value="1" <?php echo $isResolved === 1 ? 'selected' : ''; ?>>Đã giải quyết</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="date_range">Thời gian</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="date_range" name="date_range" value="<?php echo $dateFrom . ' - ' . $dateTo; ?>">
                                        <input type="hidden" id="date_from" name="date_from" value="<?php echo $dateFrom; ?>">
                                        <input type="hidden" id="date_to" name="date_to" value="<?php echo $dateTo; ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Lọc
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Anomalies list -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Danh sách anomalies</h5>
                                <span class="badge badge-primary"><?php echo $totalCount; ?> anomalies</span>
                            </div>
                            
                            <?php if (count($anomalies) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Website</th>
                                            <th>Loại</th>
                                            <th>Mức độ</th>
                                            <th>Phân khúc</th>
                                            <th>Giá trị</th>
                                            <th>Phát hiện</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($anomalies as $anomaly): ?>
                                        <tr>
                                            <td><?php echo $anomaly['id']; ?></td>
                                            <td><?php echo htmlspecialchars($anomaly['site_name']); ?></td>
                                            <td>
                                                <?php 
                                                switch ($anomaly['anomaly_type']) {
                                                    case 'high_cr':
                                                        echo 'Tỷ lệ chuyển đổi cao';
                                                        break;
                                                    case 'pattern':
                                                        echo 'Mẫu hình bất thường';
                                                        break;
                                                    case 'timing':
                                                        echo 'Thời gian bất thường';
                                                        break;
                                                    case 'cluster':
                                                        echo 'Cụm bất thường';
                                                        break;
                                                    default:
                                                        echo htmlspecialchars($anomaly['anomaly_type']);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                switch ($anomaly['severity']) {
                                                    case 'high':
                                                        echo '<span class="badge badge-high">Cao</span>';
                                                        break;
                                                    case 'medium':
                                                        echo '<span class="badge badge-medium">Trung bình</span>';
                                                        break;
                                                    case 'low':
                                                        echo '<span class="badge badge-low">Thấp</span>';
                                                        break;
                                                    default:
                                                        echo htmlspecialchars($anomaly['severity']);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                switch ($anomaly['segment_type']) {
                                                    case 'overall':
                                                        echo 'Tổng thể';
                                                        break;
                                                    case 'device':
                                                        echo 'Thiết bị: ' . htmlspecialchars($anomaly['segment_value']);
                                                        break;
                                                    case 'browser':
                                                        echo 'Trình duyệt: ' . htmlspecialchars($anomaly['segment_value']);
                                                        break;
                                                    case 'os':
                                                        echo 'Hệ điều hành: ' . htmlspecialchars($anomaly['segment_value']);
                                                        break;
                                                    case 'location':
                                                        echo 'Vị trí: ' . htmlspecialchars($anomaly['segment_value']);
                                                        break;
                                                    case 'isp':
                                                        echo 'ISP: ' . htmlspecialchars($anomaly['segment_value']);
                                                        break;
                                                    default:
                                                        echo htmlspecialchars($anomaly['segment_type'] . ': ' . $anomaly['segment_value']);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                echo 'Dự kiến: ' . round($anomaly['expected_value'], 2) . '<br>';
                                                echo 'Thực tế: ' . round($anomaly['actual_value'], 2) . '<br>';
                                                echo 'Chênh lệch: ' . round($anomaly['deviation_percent'], 2) . '%';
                                                ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($anomaly['detected_at'])); ?></td>
                                            <td>
                                                <?php if ($anomaly['is_resolved']): ?>
                                                <span class="badge badge-resolved">Đã giải quyết</span>
                                                <?php else: ?>
                                                <span class="badge badge-pending">Chưa giải quyết</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="anomaly_detail.php?id=<?php echo $anomaly['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-info-circle"></i> Chi tiết
                                                </a>
                                                <?php if (!$anomaly['is_resolved']): ?>
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#resolveModal<?php echo $anomaly['id']; ?>">
                                                    <i class="fas fa-check"></i> Giải quyết
                                                </button>
                                                
                                                <!-- Resolve Modal -->
                                                <div class="modal fade" id="resolveModal<?php echo $anomaly['id']; ?>" tabindex="-1" aria-labelledby="resolveModalLabel<?php echo $anomaly['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="resolveModalLabel<?php echo $anomaly['id']; ?>">Giải quyết Anomaly #<?php echo $anomaly['id']; ?></h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form method="post" action="">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="action" value="resolve">
                                                                    <input type="hidden" name="anomaly_id" value="<?php echo $anomaly['id']; ?>">
                                                                    
                                                                    <div class="form-group">
                                                                        <label for="resolution_notes<?php echo $anomaly['id']; ?>">Ghi chú giải quyết</label>
                                                                        <textarea class="form-control" id="resolution_notes<?php echo $anomaly['id']; ?>" name="resolution_notes" rows="3" required></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                                    <button type="submit" class="btn btn-success">Đánh dấu đã giải quyết</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    Hiển thị <?php echo count($anomalies); ?> / <?php echo $totalCount; ?> anomalies
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo
