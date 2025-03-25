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

// Check if user is admin
if ($currentUser['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Process actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $action = $_POST['action'];
        $targetUserId = (int)$_POST['user_id'];
        
        // Prevent actions on self
        if ($targetUserId === $userId) {
            $message = 'Không thể thực hiện hành động này trên tài khoản của bạn';
            $messageType = 'danger';
        } else {
            switch ($action) {
                case 'approve':
                    $stmt = $db->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
                    $stmt->bind_param("i", $targetUserId);
                    if ($stmt->execute()) {
                        $message = 'Đã phê duyệt tài khoản thành công';
                        $messageType = 'success';
                        
                        // Get user email for notification
                        $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
                        $stmt->bind_param("i", $targetUserId);
                        $stmt->execute();
                        $userEmail = $stmt->get_result()->fetch_assoc()['email'];
                        
                        // Send email notification (in a real system)
                        // mail($userEmail, 'Tài khoản đã được phê duyệt', 'Tài khoản của bạn đã được phê duyệt. Bạn có thể đăng nhập ngay bây giờ.');
                    } else {
                        $message = 'Lỗi khi phê duyệt tài khoản: ' . $db->error();
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'reject':
                    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND is_approved = 0");
                    $stmt->bind_param("i", $targetUserId);
                    if ($stmt->execute()) {
                        $message = 'Đã từ chối tài khoản thành công';
                        $messageType = 'success';
                    } else {
                        $message = 'Lỗi khi từ chối tài khoản: ' . $db->error();
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'promote':
                    $stmt = $db->prepare("UPDATE users SET role = 'manager' WHERE id = ? AND role = 'user'");
                    $stmt->bind_param("i", $targetUserId);
                    if ($stmt->execute()) {
                        $message = 'Đã nâng cấp tài khoản thành công';
                        $messageType = 'success';
                    } else {
                        $message = 'Lỗi khi nâng cấp tài khoản: ' . $db->error();
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'demote':
                    $stmt = $db->prepare("UPDATE users SET role = 'user' WHERE id = ? AND role = 'manager'");
                    $stmt->bind_param("i", $targetUserId);
                    if ($stmt->execute()) {
                        $message = 'Đã hạ cấp tài khoản thành công';
                        $messageType = 'success';
                    } else {
                        $message = 'Lỗi khi hạ cấp tài khoản: ' . $db->error();
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'disable':
                    $stmt = $db->prepare("UPDATE users SET is_approved = 0 WHERE id = ? AND id != ?");
                    $stmt->bind_param("ii", $targetUserId, $userId);
                    if ($stmt->execute()) {
                        $message = 'Đã vô hiệu hóa tài khoản thành công';
                        $messageType = 'success';
                    } else {
                        $message = 'Lỗi khi vô hiệu hóa tài khoản: ' . $db->error();
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete':
                    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND id != ?");
                    $stmt->bind_param("ii", $targetUserId, $userId);
                    if ($stmt->execute()) {
                        $message = 'Đã xóa tài khoản thành công';
                        $messageType = 'success';
                    } else {
                        $message = 'Lỗi khi xóa tài khoản: ' . $db->error();
                        $messageType = 'danger';
                    }
                    break;
                    
                default:
                    $message = 'Hành động không hợp lệ';
                    $messageType = 'danger';
                    break;
            }
        }
    }
}

// Get pending users
$stmt = $db->prepare("SELECT * FROM users WHERE is_approved = 0 ORDER BY created_at DESC");
$stmt->execute();
$pendingUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get active users
$stmt = $db->prepare("SELECT * FROM users WHERE is_approved = 1 ORDER BY role DESC, username ASC");
$stmt->execute();
$activeUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Page title
$pageTitle = 'Quản Lý Người Dùng';
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
        .badge-admin {
            background-color: #dc3545;
            color: white;
        }
        .badge-manager {
            background-color: #fd7e14;
            color: white;
        }
        .badge-user {
            background-color: #6c757d;
            color: white;
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
                        <a class="nav-link active" href="users.php">
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
                                <a class="nav-link" href="users.php">Quản Lý Người Dùng</a>
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
                    
                    <!-- Pending Users -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Tài Khoản Chờ Phê Duyệt</h5>
                            <span class="badge badge-warning"><?php echo count($pendingUsers); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (count($pendingUsers) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên đăng nhập</th>
                                            <th>Email</th>
                                            <th>Ngày đăng ký</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingUsers as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <form method="post" action="" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bạn có chắc chắn muốn phê duyệt tài khoản này?')">
                                                        <i class="fas fa-check"></i> Phê duyệt
                                                    </button>
                                                </form>
                                                <form method="post" action="" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn từ chối tài khoản này?')">
                                                        <i class="fas fa-times"></i> Từ chối
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-center text-muted my-4">Không có tài khoản nào đang chờ phê duyệt.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Active Users -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Tài Khoản Đã Kích Hoạt</h5>
                            <span class="badge badge-success"><?php echo count($activeUsers); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (count($activeUsers) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên đăng nhập</th>
                                            <th>Email</th>
                                            <th>Vai trò</th>
                                            <th>Đăng nhập cuối</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activeUsers as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge badge-admin">Admin</span>
                                                <?php elseif ($user['role'] === 'manager'): ?>
                                                <span class="badge badge-manager">Manager</span>
                                                <?php else: ?>
                                                <span class="badge badge-user">User</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Chưa đăng nhập'; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['id'] !== $userId): ?>
                                                    <?php if ($user['role'] === 'user'): ?>
                                                    <form method="post" action="" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="action" value="promote">
                                                        <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Bạn có chắc chắn muốn nâng cấp tài khoản này?')">
                                                            <i class="fas fa-arrow-up"></i> Nâng cấp
                                                        </button>
                                                    </form>
                                                    <?php elseif ($user['role'] === 'manager'): ?>
                                                    <form method="post" action="" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="action" value="demote">
                                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Bạn có chắc chắn muốn hạ cấp tài khoản này?')">
                                                            <i class="fas fa-arrow-down"></i> Hạ cấp
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($user['role'] !== 'admin'): ?>
                                                    <form method="post" action="" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="action" value="disable">
                                                        <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Bạn có chắc chắn muốn vô hiệu hóa tài khoản này?')">
                                                            <i class="fas fa-ban"></i> Vô hiệu hóa
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="post" action="" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này? Hành động này không thể hoàn tác.')">
                                                            <i class="fas fa-trash"></i> Xóa
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                <span class="text-muted">Tài khoản hiện tại</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-center text-muted my-4">Không có tài khoản nào đã kích hoạt.</p>
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
</body>
</html>
