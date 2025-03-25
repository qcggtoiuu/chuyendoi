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
$clickType = isset($_GET['click_type']) ? $_GET['click_type'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-7 days'));
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$query = "
    SELECT c.*, v.ip_address, v.browser, v.os, v.city, v.country, v.is_bot, v.bot_score, s.name as site_name
    FROM clicks c
    JOIN visits v ON c.visit_id = v.id
    JOIN sites s ON v.site_id = s.id
    WHERE 1=1
";
$countQuery = "
    SELECT COUNT(*) as total
    FROM clicks c
    JOIN visits v ON c.visit_id = v.id
    JOIN sites s ON v.site_id = s.id
    WHERE 1=1
";
$params = [];
$types = "";

// Add filters
if ($siteId > 0) {
    $query .= " AND v.site_id = ?";
    $countQuery .= " AND v.site_id = ?";
    $params[] = $siteId;
    $types .= "i";
}

if (!empty($clickType)) {
    $query .= " AND c.click_type = ?";
    $countQuery .= " AND c.click_type = ?";
    $params[] = $clickType;
    $types .= "s";
}

if (!empty($dateFrom)) {
    $query .= " AND DATE(c.click_time) >= ?";
    $countQuery .= " AND DATE(c.click_time) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

if (!empty($dateTo)) {
    $query .= " AND DATE(c.click_time) <= ?";
    $countQuery .= " AND DATE(c.click_time) <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

// Add order and limit
$query .= " ORDER BY c.click_time DESC LIMIT ?, ?";
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

// Get clicks
$stmt = $db->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$clicks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get sites for filter
$stmt = $db->prepare("SELECT id, name FROM sites ORDER BY name");
$stmt->execute();
$sites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Page title
$pageTitle = 'Quản Lý Lượt Click';
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
        .badge-bot {
            background-color: #dc3545;
        }
        .badge-human {
            background-color: #28a745;
        }
        .badge-phone {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-zalo {
            background-color: #0068ff;
        }
        .badge-messenger {
            background-color: #0084ff;
        }
        .badge-maps {
            background-color: #4285F4;
        }
        .filter-card {
            background-color: #f8f9fa;
            border: none;
        }
        .pagination {
            margin-bottom: 0;
        }
        .click-url {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
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
                        <a class="nav-link active" href="clicks.php">
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
                                <a class="nav-link" href="clicks.php">Quản Lý Lượt Click</a>
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
                    
                    <!-- Filters -->
                    <div class="card filter-card mb-4">
                        <div class="card-body">
                            <form method="get" action="" class="row">
                                <div class="col-md-3 mb-3">
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
                                <div class="col-md-3 mb-3">
                                    <label for="click_type">Loại click</label>
                                    <select class="form-control" id="click_type" name="click_type">
                                        <option value="" <?php echo empty($clickType) ? 'selected' : ''; ?>>Tất cả</option>
                                        <option value="phone" <?php echo $clickType === 'phone' ? 'selected' : ''; ?>>Điện thoại</option>
                                        <option value="zalo" <?php echo $clickType === 'zalo' ? 'selected' : ''; ?>>Zalo</option>
                                        <option value="messenger" <?php echo $clickType === 'messenger' ? 'selected' : ''; ?>>Messenger</option>
                                        <option value="maps" <?php echo $clickType === 'maps' ? 'selected' : ''; ?>>Maps</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
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
                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Lọc
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Clicks list -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Danh sách lượt click</h5>
                                <span class="badge badge-primary"><?php echo $totalCount; ?> lượt click</span>
                            </div>
                            
                            <?php if (count($clicks) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Website</th>
                                            <th>IP</th>
                                            <th>Loại</th>
                                            <th>URL</th>
                                            <th>Thời gian</th>
                                            <th>Loại người dùng</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clicks as $click): ?>
                                        <tr>
                                            <td><?php echo $click['id']; ?></td>
                                            <td><?php echo htmlspecialchars($click['site_name']); ?></td>
                                            <td><?php echo htmlspecialchars($click['ip_address']); ?></td>
                                            <td>
                                                <?php 
                                                switch ($click['click_type']) {
                                                    case 'phone':
                                                        echo '<span class="badge badge-phone">Điện thoại</span>';
                                                        break;
                                                    case 'zalo':
                                                        echo '<span class="badge badge-zalo">Zalo</span>';
                                                        break;
                                                    case 'messenger':
                                                        echo '<span class="badge badge-messenger">Messenger</span>';
                                                        break;
                                                    case 'maps':
                                                        echo '<span class="badge badge-maps">Maps</span>';
                                                        break;
                                                    default:
                                                        echo htmlspecialchars($click['click_type']);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo htmlspecialchars($click['click_url']); ?>" target="_blank" class="click-url" title="<?php echo htmlspecialchars($click['click_url']); ?>">
                                                    <?php echo htmlspecialchars($click['click_url']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($click['click_time'])); ?></td>
                                            <td>
                                                <?php if ($click['is_bot']): ?>
                                                <span class="badge badge-bot">Bot (<?php echo round($click['bot_score'] * 100); ?>%)</span>
                                                <?php else: ?>
                                                <span class="badge badge-human">Human</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="visit_detail.php?id=<?php echo $click['visit_id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-info-circle"></i> Chi tiết
                                                </a>
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
                                    Hiển thị <?php echo count($clicks); ?> / <?php echo $totalCount; ?> lượt click
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&site_id=<?php echo $siteId; ?>&click_type=<?php echo $clickType; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $startPage = max(1, $page - 2);
                                        $endPage = min($totalPages, $page + 2);
                                        
                                        if ($startPage > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="?page=1&site_id=' . $siteId . '&click_type=' . $clickType . '&date_from=' . $dateFrom . '&date_to=' . $dateTo . '">1</a></li>';
                                            if ($startPage > 2) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                        }
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++) {
                                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '&site_id=' . $siteId . '&click_type=' . $clickType . '&date_from=' . $dateFrom . '&date_to=' . $dateTo . '">' . $i . '</a></li>';
                                        }
                                        
                                        if ($endPage < $totalPages) {
                                            if ($endPage < $totalPages - 1) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '&site_id=' . $siteId . '&click_type=' . $clickType . '&date_from=' . $dateFrom . '&date_to=' . $dateTo . '">' . $totalPages . '</a></li>';
                                        }
                                        ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&site_id=<?php echo $siteId; ?>&click_type=<?php echo $clickType; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                            <?php endif; ?>
                            
                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-mouse-pointer fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">Không có lượt click nào</h4>
                                <p class="text-muted">Thử thay đổi bộ lọc hoặc chờ dữ liệu được thu thập.</p>
                            </div>
                            <?php endif; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize date range picker
            $('#date_range').daterangepicker({
                startDate: moment('<?php echo $dateFrom; ?>'),
                endDate: moment('<?php echo $dateTo; ?>'),
                ranges: {
                   'Today': [moment(), moment()],
                   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                   'This Month': [moment().startOf('month'), moment().endOf('month')],
                   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    format: 'YYYY-MM-DD'
                }
            }, function(start, end) {
                $('#date_from').val(start.format('YYYY-MM-DD'));
                $('#date_to').val(end.format('YYYY-MM-DD'));
            });
        });
    </script>
</body>
</html>
