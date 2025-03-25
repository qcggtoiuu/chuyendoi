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

// Add/Edit fraud pattern
if (isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {
    $patternId = isset($_POST['pattern_id']) ? (int)$_POST['pattern_id'] : 0;
    $ipPattern = isset($_POST['ip_pattern']) ? sanitizeInput($_POST['ip_pattern']) : '';
    $ispPattern = isset($_POST['isp_pattern']) ? sanitizeInput($_POST['isp_pattern']) : '';
    $locationPattern = isset($_POST['location_pattern']) ? sanitizeInput($_POST['location_pattern']) : '';
    $devicePattern = isset($_POST['device_pattern']) ? sanitizeInput($_POST['device_pattern']) : '';
    $behaviorPattern = isset($_POST['behavior_pattern']) ? sanitizeInput($_POST['behavior_pattern']) : '';
    $similarityThreshold = isset($_POST['similarity_threshold']) ? (float)$_POST['similarity_threshold'] : 0.85;
    $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    if ($_POST['action'] === 'add') {
        // Insert new pattern
        $stmt = $db->prepare("
            INSERT INTO fraud_patterns (
                created_by, ip_pattern, isp_pattern, location_pattern, 
                device_pattern, behavior_pattern, similarity_threshold, 
                description, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isssssdsi",
            $userId, $ipPattern, $ispPattern, $locationPattern,
            $devicePattern, $behaviorPattern, $similarityThreshold,
            $description, $isActive
        );
        
        if ($stmt->execute()) {
            $message = 'Mẫu hình fraud đã được thêm thành công';
            $messageType = 'success';
        } else {
            $message = 'Lỗi khi thêm mẫu hình fraud: ' . $db->error();
            $messageType = 'danger';
        }
    } else {
        // Update existing pattern
        $stmt = $db->prepare("
            UPDATE fraud_patterns SET
                ip_pattern = ?,
                isp_pattern = ?,
                location_pattern = ?,
                device_pattern = ?,
                behavior_pattern = ?,
                similarity_threshold = ?,
                description = ?,
                is_active = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "sssssdsii",
            $ipPattern, $ispPattern, $locationPattern,
            $devicePattern, $behaviorPattern, $similarityThreshold,
            $description, $isActive, $patternId
        );
        
        if ($stmt->execute()) {
            $message = 'Mẫu hình fraud đã được cập nhật thành công';
            $messageType = 'success';
        } else {
            $message = 'Lỗi khi cập nhật mẫu hình fraud: ' . $db->error();
            $messageType = 'danger';
        }
    }
}

// Delete fraud pattern
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['pattern_id'])) {
    $patternId = (int)$_POST['pattern_id'];
    
    // Delete pattern
    $stmt = $db->prepare("DELETE FROM fraud_patterns WHERE id = ?");
    $stmt->bind_param("i", $patternId);
    
    if ($stmt->execute()) {
        $message = 'Mẫu hình fraud đã được xóa thành công';
        $messageType = 'success';
    } else {
        $message = 'Lỗi khi xóa mẫu hình fraud: ' . $db->error();
        $messageType = 'danger';
    }
}

// Toggle fraud pattern status
if (isset($_POST['action']) && $_POST['action'] === 'toggle' && isset($_POST['pattern_id'])) {
    $patternId = (int)$_POST['pattern_id'];
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Update pattern status
    $stmt = $db->prepare("UPDATE fraud_patterns SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $isActive, $patternId);
    
    if ($stmt->execute()) {
        $message = 'Trạng thái mẫu hình fraud đã được cập nhật thành công';
        $messageType = 'success';
    } else {
        $message = 'Lỗi khi cập nhật trạng thái mẫu hình fraud: ' . $db->error();
        $messageType = 'danger';
    }
}

// Get fraud patterns
$stmt = $db->prepare("
    SELECT fp.*, u.username as created_by_username,
           (SELECT COUNT(*) FROM button_hide_logs WHERE matching_pattern_id = fp.id) as hide_count
    FROM fraud_patterns fp
    JOIN users u ON fp.created_by = u.id
    ORDER BY fp.created_at DESC
");
$stmt->execute();
$fraudPatterns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get fraud pattern stats
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
    FROM fraud_patterns
");
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Page title
$pageTitle = 'Quản Lý Mẫu Hình Fraud';
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
        .badge-active {
            background-color: #28a745;
        }
        .badge-inactive {
            background-color: #6c757d;
        }
        .stats-card {
            border-left: 4px solid #3961AA;
        }
        .stats-card.active {
            border-left-color: #28a745;
        }
        .stats-card.inactive {
            border-left-color: #6c757d;
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
        .pattern-card {
            transition: all 0.3s ease;
        }
        .pattern-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .pattern-card.inactive {
            opacity: 0.7;
        }
        .pattern-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pattern-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0;
        }
        .pattern-meta {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .pattern-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
        }
        .pattern-stat {
            text-align: center;
        }
        .pattern-stat-value {
            font-size: 1.2rem;
            font-weight: 600;
        }
        .pattern-stat-label {
            font-size: 0.8rem;
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
                        <a class="nav-link" href="anomalies.php">
                            <i class="fas fa-exclamation-triangle"></i> Anomalies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="fraud.php">
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
                                <a class="nav-link" href="fraud.php">Quản Lý Mẫu Hình Fraud</a>
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
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPatternModal">
                            <i class="fas fa-plus"></i> Thêm Mẫu Hình Mới
                        </button>
                    </div>
                    
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
                            <div class="card stats-card">
                                <div class="card-body d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-ban stats-icon"></i>
                                    </div>
                                    <div>
                                        <div class="stats-count"><?php echo $stats['total']; ?></div>
                                        <div class="stats-label">Tổng số mẫu hình</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stats-card active">
                                <div class="card-body d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-check-circle stats-icon text-success"></i>
                                    </div>
                                    <div>
                                        <div class="stats-count"><?php echo $stats['active']; ?></div>
                                        <div class="stats-label">Mẫu hình đang hoạt động</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stats-card inactive">
                                <div class="card-body d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-times-circle stats-icon text-secondary"></i>
                                    </div>
                                    <div>
                                        <div class="stats-count"><?php echo $stats['inactive']; ?></div>
                                        <div class="stats-label">Mẫu hình không hoạt động</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fraud patterns list -->
                    <div class="row">
                        <?php if (count($fraudPatterns) > 0): ?>
                            <?php foreach ($fraudPatterns as $pattern): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card pattern-card <?php echo $pattern['is_active'] ? '' : 'inactive'; ?>">
                                    <div class="card-body">
                                        <div class="pattern-header">
                                            <h5 class="pattern-title">
                                                <?php echo !empty($pattern['description']) ? htmlspecialchars($pattern['description']) : 'Mẫu hình #' . $pattern['id']; ?>
                                            </h5>
                                            <span class="badge <?php echo $pattern['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                                <?php echo $pattern['is_active'] ? 'Đang hoạt động' : 'Không hoạt động'; ?>
                                            </span>
                                        </div>
                                        <div class="pattern-meta mt-2">
                                            <div>Tạo bởi: <?php echo htmlspecialchars($pattern['created_by_username']); ?></div>
                                            <div>Ngày tạo: <?php echo date('d/m/Y H:i', strtotime($pattern['created_at'])); ?></div>
                                        </div>
                                        <div class="mt-3">
                                            <?php if (!empty($pattern['ip_pattern'])): ?>
                                            <div><strong>IP:</strong> <?php echo htmlspecialchars($pattern['ip_pattern']); ?></div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($pattern['isp_pattern'])): ?>
                                            <div><strong>ISP:</strong> <?php echo htmlspecialchars($pattern['isp_pattern']); ?></div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($pattern['location_pattern'])): ?>
                                            <div><strong>Vị trí:</strong> <?php echo htmlspecialchars($pattern['location_pattern']); ?></div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($pattern['device_pattern'])): ?>
                                            <div><strong>Thiết bị:</strong> <?php echo htmlspecialchars($pattern['device_pattern']); ?></div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($pattern['behavior_pattern'])): ?>
                                            <div><strong>Hành vi:</strong> <?php echo htmlspecialchars($pattern['behavior_pattern']); ?></div>
                                            <?php endif; ?>
                                            
                                            <div><strong>Ngưỡng tương đồng:</strong> <?php echo $pattern['similarity_threshold'] * 100; ?>%</div>
                                        </div>
                                        <div class="pattern-stats">
                                            <div class="pattern-stat">
                                                <div class="pattern-stat-value"><?php echo $pattern['hide_count']; ?></div>
                                                <div class="pattern-stat-label">Lượt ẩn nút</div>
                                            </div>
                                        </div>
                                        <div class="mt-3 d-flex justify-content-between">
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editPatternModal<?php echo $pattern['id']; ?>">
                                                <i class="fas fa-edit"></i> Sửa
                                            </button>
                                            <form method="post" action="" class="d-inline">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="pattern_id" value="<?php echo $pattern['id']; ?>">
                                                <input type="hidden" name="is_active" value="<?php echo $pattern['is_active'] ? '0' : '1'; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $pattern['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                                    <i class="fas <?php echo $pattern['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                                    <?php echo $pattern['is_active'] ? 'Tạm dừng' : 'Kích hoạt'; ?>
                                                </button>
                                            </form>
                                            <form method="post" action="" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="pattern_id" value="<?php echo $pattern['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa mẫu hình này?')">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Edit Pattern Modal -->
                                <div class="modal fade" id="editPatternModal<?php echo $pattern['id']; ?>" tabindex="-1" aria-labelledby="editPatternModalLabel<?php echo $pattern['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editPatternModalLabel<?php echo $pattern['id']; ?>">Sửa Mẫu Hình Fraud</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="post" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="pattern_id" value="<?php echo $pattern['id']; ?>">
                                                    
                                                    <div class="form-group">
                                                        <label for="description<?php echo $pattern['id']; ?>">Mô tả</label>
                                                        <input type="text" class="form-control" id="description<?php echo $pattern['id']; ?>" name="description" value="<?php echo htmlspecialchars($pattern['description']); ?>">
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group col-md-6">
                                                            <label for="ip_pattern<?php echo $pattern['id']; ?>">Mẫu IP</label>
                                                            <input type="text" class="form-control" id="ip_pattern<?php echo $pattern['id']; ?>" name="ip_pattern" value="<?php echo htmlspecialchars($pattern['ip_pattern']); ?>">
                                                            <small class="form-text text-muted">Ví dụ: 192.168.1.</small>
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label for="isp_pattern<?php echo $pattern['id']; ?>">Mẫu ISP</label>
                                                            <input type="text" class="form-control" id="isp_pattern<?php echo $pattern['id']; ?>" name="isp_pattern" value="<?php echo htmlspecialchars($pattern['isp_pattern']); ?>">
                                                            <small class="form-text text-muted">Ví dụ: Viettel</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group col-md-6">
                                                            <label for="location_pattern<?php echo $pattern['id']; ?>">Mẫu vị trí</label>
                                                            <input type="text" class="form-control" id="location_pattern<?php echo $pattern['id']; ?>" name="location_pattern" value="<?php echo htmlspecialchars($pattern['location_pattern']); ?>">
                                                            <small class="form-text text-muted">Ví dụ: Hanoi, Vietnam</small>
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label for="device_pattern<?php echo $pattern['id']; ?>">Mẫu thiết bị</label>
                                                            <input type="text" class="form-control" id="device_pattern<?php echo $pattern['id']; ?>" name="device_pattern" value="<?php echo htmlspecialchars($pattern['device_pattern']); ?>">
                                                            <small class="form-text text-muted">Ví dụ: Chrome, Android</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group col-md-6">
                                                            <label for="behavior_pattern<?php echo $pattern['id']; ?>">Mẫu hành vi</label>
                                                            <input type="text" class="form-control" id="behavior_pattern<?php echo $pattern['id']; ?>" name="behavior_pattern" value="<?php echo htmlspecialchars($pattern['behavior_pattern']); ?>">
                                                            <small class="form-text text-muted">Ví dụ: rapid-clicks</small>
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label for="similarity_threshold<?php echo $pattern['id']; ?>">Ngưỡng tương đồng</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" id="similarity_threshold<?php echo $pattern['id']; ?>" name="similarity_threshold" value="<?php echo $pattern['similarity_threshold']; ?>" min="0" max="1" step="0.01">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </div>
                                                            <small class="form-text text-muted">Giá trị từ 0 đến 1 (0% đến 100%)</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="is_active<?php echo $pattern['id']; ?>" name="is_active" <?php echo $pattern['is_active'] ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label" for="is_active<?php echo $pattern['id']; ?>">Kích hoạt mẫu hình</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-ban fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">Chưa có mẫu hình fraud nào</h4>
                                <p class="text-muted">Bắt đầu bằng cách thêm mẫu hình fraud đầu tiên của bạn.</p>
                                <button type="button" class="btn btn-primary mt-3" data-toggle="modal" data-target="#addPatternModal">
                                    <i class="fas fa-plus"></i> Thêm Mẫu Hình Mới
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Add Pattern Modal -->
                <div class="modal fade" id="addPatternModal" tabindex="-1" aria-labelledby="addPatternModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-
