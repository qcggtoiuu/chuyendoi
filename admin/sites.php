<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session
session_start();

// Check if user is logged in and redirect if not
requireLogin();

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

// Delete site
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['site_id'])) {
    $siteId = (int)$_POST['site_id'];
    
    // Check if site exists
    $stmt = $db->prepare("SELECT * FROM sites WHERE id = ?");
    $stmt->bind_param("i", $siteId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Delete site
        $stmt = $db->prepare("DELETE FROM sites WHERE id = ?");
        $stmt->bind_param("i", $siteId);
        
        if ($stmt->execute()) {
            $message = 'Website đã được xóa thành công';
            $messageType = 'success';
        } else {
            $message = 'Lỗi khi xóa website: ' . $db->error();
            $messageType = 'danger';
        }
    } else {
        $message = 'Website không tồn tại';
        $messageType = 'danger';
    }
}

// Get sites
$stmt = $db->prepare("
    SELECT s.*, 
           (SELECT COUNT(*) FROM visits WHERE site_id = s.id) as visit_count,
           (SELECT COUNT(*) FROM visits v JOIN clicks c ON v.id = c.visit_id WHERE v.site_id = s.id) as click_count
    FROM sites s
    ORDER BY s.created_at DESC
");
$stmt->execute();
$sites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Page title
$pageTitle = 'Quản Lý Websites';
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
        .api-key {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 5px;
            border-radius: 3px;
            font-size: 0.9rem;
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
                        <a class="nav-link active" href="sites.php">
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
                                <a class="nav-link" href="sites.php">Quản Lý Websites</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="add_site.php">Thêm Website Mới</a>
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
                        <a href="add_site.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm Website Mới
                        </a>
                    </div>
                    
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Sites list -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (count($sites) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên</th>
                                            <th>Domain</th>
                                            <th>API Key</th>
                                            <th>Visits</th>
                                            <th>Clicks</th>
                                            <th>Ngày tạo</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sites as $site): ?>
                                        <tr>
                                            <td><?php echo $site['id']; ?></td>
                                            <td><?php echo htmlspecialchars($site['name']); ?></td>
                                            <td>
                                                <a href="https://<?php echo htmlspecialchars($site['domain']); ?>" target="_blank">
                                                    <?php echo htmlspecialchars($site['domain']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="api-key"><?php echo htmlspecialchars($site['api_key']); ?></span>
                                                <button class="btn btn-sm btn-outline-secondary copy-btn" data-clipboard-text="<?php echo htmlspecialchars($site['api_key']); ?>">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                            <td><?php echo $site['visit_count']; ?></td>
                                            <td><?php echo $site['click_count']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($site['created_at'])); ?></td>
                                            <td>
                                                <a href="edit_site.php?id=<?php echo $site['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> Sửa
                                                </a>
                                                <form method="post" action="" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa website này? Tất cả dữ liệu liên quan sẽ bị xóa.')">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-globe fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">Chưa có website nào</h4>
                                <p class="text-muted">Bắt đầu bằng cách thêm website đầu tiên của bạn.</p>
                                <a href="add_site.php" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus"></i> Thêm Website Mới
                                </a>
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
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
    <script>
        // Initialize clipboard.js
        new ClipboardJS('.copy-btn');
        
        // Show tooltip when copying
        $('.copy-btn').on('click', function() {
            $(this).tooltip({
                title: 'Copied!',
                trigger: 'manual',
                placement: 'top'
            }).tooltip('show');
            
            setTimeout(() => {
                $(this).tooltip('hide');
            }, 1000);
        });
    </script>
</body>
</html>
