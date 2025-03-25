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
$isBot = isset($_GET['is_bot']) ? (int)$_GET['is_bot'] : -1; // -1 = all, 0 = humans, 1 = bots
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-7 days'));
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$query = "
    SELECT v.*, s.name as site_name
    FROM visits v
    JOIN sites s ON v.site_id = s.id
    WHERE 1=1
";
$countQuery = "
    SELECT COUNT(*) as total
    FROM visits v
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

if ($isBot >= 0) {
    $query .= " AND v.is_bot = ?";
    $countQuery .= " AND v.is_bot = ?";
    $params[] = $isBot;
    $types .= "i";
}

if (!empty($dateFrom)) {
    $query .= " AND DATE(v.visit_time) >= ?";
    $countQuery .= " AND DATE(v.visit_time) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

if (!empty($dateTo)) {
    $query .= " AND DATE(v.visit_time) <= ?";
    $countQuery .= " AND DATE(v.visit_time) <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

// Add order and limit
$query .= " ORDER BY v.visit_time DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types .= "ii";

// Get total count
$stmt = $db->prepare($countQuery);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalCount = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalCount / $perPage);

// Get visits
$stmt = $db->prepare($query);
if (!empty($types)) {
    // Remove the last two parameters (offset and perPage) for the count query
    $countParams = array_slice($params, 0, -2);
    
    // Add them back for the main query
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$visits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get sites for filter
$stmt = $db->prepare("SELECT id, name FROM sites ORDER BY name");
$stmt->execute();
$sites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Page title
$pageTitle = 'Quản Lý Lượt Truy Cập';
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
        .filter-card {
            background-color: #f8f9fa;
            border: none;
        }
        .pagination {
            margin-bottom: 0;
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
                            <li class="nav-item active">
                                <a class="nav-link" href="visits.php">Quản Lý Lượt Truy Cập</a>
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
                                    <label for="is_bot">Loại</label>
                                    <select class="form-control" id="is_bot" name="is_bot">
                                        <option value="-1" <?php echo $isBot == -1 ? 'selected' : ''; ?>>Tất cả</option>
                                        <option value="0" <?php echo $isBot === 0 ? 'selected' : ''; ?>>Người dùng</option>
                                        <option value="1" <?php echo $isBot === 1 ? 'selected' : ''; ?>>Bot</option>
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
                    
                    <!-- Visits list -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Danh sách lượt truy cập</h5>
                                <span class="badge badge-primary"><?php echo $totalCount; ?> lượt truy cập</span>
                            </div>
                            
                            <?php if (count($visits) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Website</th>
                                            <th>IP</th>
                                            <th>Trình duyệt</th>
                                            <th>Hệ điều hành</th>
                                            <th>Vị trí</th>
                                            <th>Thời gian</th>
                                            <th>Loại</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($visits as $visit): ?>
                                        <tr>
                                            <td><?php echo $visit['id']; ?></td>
                                            <td><?php echo htmlspecialchars($visit['site_name']); ?></td>
                                            <td><?php echo htmlspecialchars($visit['ip_address']); ?></td>
                                            <td><?php echo htmlspecialchars($visit['browser'] . ' ' . $visit['browser_version']); ?></td>
                                            <td><?php echo htmlspecialchars($visit['os'] . ' ' . $visit['os_version']); ?></td>
                                            <td><?php echo htmlspecialchars($visit['city'] . ', ' . $visit['country']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($visit['visit_time'])); ?></td>
                                            <td>
                                                <?php if ($visit['is_bot']): ?>
                                                <span class="badge badge-bot">Bot (<?php echo round($visit['bot_score'] * 100); ?>%)</span>
                                                <?php else: ?>
                                                <span class="badge badge-human">Human</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="visit_detail.php?id=<?php echo $visit['id']; ?>" class="btn btn-sm btn-info">
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
                                    Hiển thị <?php echo count($visits); ?> / <?php echo $totalCount; ?> lượt truy cập
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&site_id=<?php echo $siteId; ?>&is_bot=<?php echo $isBot; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $startPage = max(1, $page - 2);
                                        $endPage = min($totalPages, $page + 2);
                                        
                                        if ($startPage > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="?page=1&site_id=' . $siteId . '&is_bot=' . $isBot . '&date_from=' . $dateFrom . '&date_to=' . $dateTo . '">1</a></li>';
                                            if ($startPage > 2) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                        }
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++) {
                                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '&site_id=' . $siteId . '&is_bot=' . $isBot . '&date_from=' . $dateFrom . '&date_to=' . $dateTo . '">' . $i . '</a></li>';
                                        }
                                        
                                        if ($endPage < $totalPages) {
                                            if ($endPage < $totalPages - 1) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '&site_id=' . $siteId . '&is_bot=' . $isBot . '&date_from=' . $dateFrom . '&date_to=' . $dateTo . '">' . $totalPages . '</a></li>';
                                        }
                                        ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&site_id=<?php echo $siteId; ?>&is_bot=<?php echo $isBot; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">
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
                                <i class="fas fa-eye-slash fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">Không có lượt truy cập nào</h4>
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
