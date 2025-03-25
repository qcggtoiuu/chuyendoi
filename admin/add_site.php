</div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar py-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="interactions.php">
                            <i class="fas fa-phone-alt"></i> Tương tác
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="add_site.php">
                            <i class="fas fa-globe"></i> Quản lý Website
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buttons.php">
                            <i class="fas fa-comment-dots"></i> Tùy chỉnh nút
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bot_settings.php">
                            <i class="fas fa-robot"></i> Cài đặt chống Bot
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link" href="documentation.php">
                            <i class="fas fa-book"></i> Hướng dẫn
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="support.php">
                            <i class="fas fa-question-circle"></i> Hỗ trợ
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <div class="col-md-10 py-3">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h2>Quản lý Website</h2>
                        <p class="text-muted">Thêm và quản lý các website của bạn.</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSiteModal">
                            <i class="fas fa-plus"></i> Thêm website mới
                        </button>
                    </div>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <?php if (count($sites) == 0): ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Chưa có website nào</h4>
                                <p>Bạn chưa thêm website nào để theo dõi. Hãy thêm website đầu tiên của bạn.</p>
                                <hr>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSiteModal">
                                    <i class="fas fa-plus"></i> Thêm website
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sites as $site): ?>
                            <div class="col-md-6">
                                <div class="card site-card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($site['site_name']); ?></h5>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="edit_site.php?id=<?php echo $site['id']; ?>"><i class="fas fa-edit me-2"></i> Chỉnh sửa</a></li>
                                                <li><a class="dropdown-item" href="index.php?site_id=<?php echo $site['id']; ?>"><i class="fas fa-chart-line me-2"></i> Xem thống kê</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteSiteModal" data-site-id="<?php echo $site['id']; ?>" data-site-name="<?php echo htmlspecialchars($site['site_name']); ?>"><i class="fas fa-trash me-2"></i> Xóa</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p><strong><i class="fas fa-link me-2"></i> URL:</strong> <a href="<?php echo htmlspecialchars($site['site_url']); ?>" target="_blank"><?php echo htmlspecialchars($site['site_url']); ?></a></p>
                                        <p><strong><i class="fas fa-clock me-2"></i> Ngày tạo:</strong> <?php echo date('d/m/Y H:i', strtotime($site['created_at'])); ?></p>
                                        <p>
                                            <strong><i class="fas fa-shield-alt me-2"></i> Chống Bot:</strong>
                                            <?php if ($site['bot_protection']): ?>
                                                <span class="badge bg-success">Đang bật</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Đang tắt</span>
                                            <?php endif; ?>
                                        </p>
                                        <div class="site-buttons mt-3">
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#trackingCodeModal" data-site-id="<?php echo $site['id']; ?>" data-site-name="<?php echo htmlspecialchars($site['site_name']); ?>">
                                                <i class="fas fa-code me-1"></i> Mã tracking
                                            </button>
                                            <a href="index.php?site_id=<?php echo $site['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-chart-line me-1"></i> Xem thống kê
                                            </a>
                                            <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#previewButtonModal" data-site-id="<?php echo $site['id']; ?>">
                                                <i class="fas fa-eye me-1"></i> Xem nút
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal thêm website -->
    <div class="modal fade" id="addSiteModal" tabindex="-1" aria-labelledby="addSiteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSiteModalLabel"><i class="fas fa-plus-circle me-2"></i> Thêm website mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="site_name" class="form-label">Tên website</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" placeholder="Ví dụ: Shop của tôi" required>
                            <div class="form-text">Đặt tên để dễ nhận biết website của bạn.</div>
                        </div>
                        <div class="mb-3">
                            <label for="site_url" class="form-label">URL website</label>
                            <input type="url" class="form-control" id="site_url" name="site_url" placeholder="https://example.com" required>
                            <div class="form-text">Nhập URL đầy đủ bao gồm http:// hoặc https://</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Chọn kiểu nút tương tác</label>
                            <div class="row">
                                <?php foreach ($button_styles as $style): ?>
                                    <div class="col-md-3">
                                        <div class="button-preview" data-style-id="<?php echo $style['id']; ?>">
                                            <input type="radio" name="button_style" value="<?php echo $style['id']; ?>" id="style_<?php echo $style['id']; ?>" class="d-none" <?php echo ($style['id'] == 1) ? 'checked' : ''; ?>>
                                            <img src="<?php echo !empty($style['preview_image']) ? $style['preview_image'] : '../assets/images/button-style-' . $style['id'] . '.png'; ?>" alt="<?php echo htmlspecialchars($style['name']); ?>" class="img-fluid">
                                            <div class="mt-1"><?php echo htmlspecialchars($style['name']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vị trí nút</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="button_position" id="position_right" value="right" checked>
                                <label class="form-check-label" for="position_right">
                                    Góc phải
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="button_position" id="position_left" value="left">
                                <label class="form-check-label" for="position_left">
                                    Góc trái
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="button_position" id="position_bottom" value="bottom">
                                <label class="form-check-label" for="position_bottom">
                                    Dưới cùng
                                </label>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="bot_protection" name="bot_protection" checked>
                            <label class="form-check-label" for="bot_protection">Bật chế độ chống Bot/Automation</label>
                            <div class="form-text">Khi phát hiện Bot, hệ thống sẽ không hiển thị nút tương tác để tránh click ảo.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Thêm website</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal mã tracking -->
    <div class="modal fade" id="trackingCodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-code me-2"></i> Mã Tracking cho <span id="siteName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Copy đoạn mã sau và dán vào phần cuối thẻ <code>&lt;body&gt;</code> trên website của bạn:</p>
                    <div class="mb-3">
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0"><code id="trackingCode"></code></pre>
                        </div>
                        <div class="d-flex justify-content-end mt-2">
                            <button class="btn btn-sm btn-primary copy-btn" onclick="copyToClipboard('trackingCode')">
                                <i class="fas fa-copy me-1"></i> Copy mã
                            </button>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> <strong>Lưu ý:</strong> Sau khi thêm mã, có thể mất đến 5 phút để hệ thống bắt đầu thu thập dữ liệu. Nếu sử dụng các nền tảng như Wordpress, hãy thêm mã này vào phần footer của theme.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal xóa website -->
    <div class="modal fade" id="deleteSiteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash me-2"></i> Xóa website</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa website <strong id="deleteSiteName"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i> Lưu ý: Hành động này không thể hoàn tác và sẽ xóa tất cả dữ liệu tracking liên quan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger"><i class="fas fa-trash me-1"></i> Xóa</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal xem trước nút -->
    <div class="modal fade" id="previewButtonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i> Xem trước nút tương tác</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="buttonPreviewContainer" class="p-3">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i> Đây là bản xem trước của nút tương tác.
                        </div>
                        <div id="buttonPreview" style="height: 300px; position: relative; border: 1px solid #ddd; border-radius: 5px; background-color: #f8f9fa;">
                            <!-- Nút sẽ được hiển thị ở đây -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý chọn kiểu nút
        document.querySelectorAll('.button-preview').forEach(function(button) {
            button.addEventListener('click', function() {
                // Xóa active class từ tất cả
                document.querySelectorAll('.button-preview').forEach(function(btn) {
                    btn.classList.remove('active');
                });
                
                // Thêm active class và check radio
                this.classList.add('active');
                const styleId = this.getAttribute('data-style-id');
                document.getElementById('style_' + styleId).checked = true;
            });
        });
        
        // Set active cho nút được chọn mặc định
        document.addEventListener('DOMContentLoaded', function() {
            const checkedStyle = document.querySelector('input[name="button_style"]:checked');
            if (checkedStyle) {
                const styleId = checkedStyle.value;
                document.querySelector('.button-preview[data-style-id="' + styleId + '"]').classList.add('active');
            }
        });
        
        // Xử lý modal mã tracking
        document.getElementById('trackingCodeModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const siteId = button.getAttribute('data-site-id');
            const siteName = button.getAttribute('data-site-name');
            
            document.getElementById('siteName').textContent = siteName;
            
            // Tạo mã tracking
            const trackingCode = `<!-- ChuyenDoi.io.vn Tracking Code -->
<script>
    (function() {
        var script = document.createElement('script');
        script.src = '<?php echo SITE_URL; ?>/assets/js/chuyendoi-track.js';
        script.async = true;
        script.setAttribute('data-site-id', '${siteId}');
        document.head.appendChild(script);
        
        var styleScript = document.createElement('script');
        styleScript.src = '<?php echo BUTTON_URL; ?>?site_id=${siteId}';
        styleScript.async = true;
        document.head.appendChild(styleScript);
    })();
<\/script>`;
            
            document.getElementById('trackingCode').textContent = trackingCode;
        });
        
        // Xử lý modal xóa website
        document.getElementById('deleteSiteModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const siteId = button.getAttribute('data-site-id');
            const siteName = button.getAttribute('data-site-name');
            
            document.getElementById('deleteSiteName').textContent = siteName;
            document.getElementById('confirmDelete').href = 'delete_site.php?id=' + siteId;
        });
        
        // Xử lý modal xem trước nút
        document.getElementById('previewButtonModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const siteId = button.getAttribute('data-site-id');
            
            // Tải iframe với nút
            const previewContainer = document.getElementById('buttonPreview');
            previewContainer.innerHTML = `<iframe src="<?php echo SITE_URL; ?>/button_preview.php?site_id=${siteId}" frameborder="0" style="width: 100%; height: 100%;"></iframe>`;
        });
        
        // Copy to clipboard
        function copyToClipboard(elementId) {
            const el = document.getElementById(elementId);
            const text = el.textContent;
            
            navigator.clipboard.writeText(text).then(function() {
                const copyBtn = document.querySelector('.copy-btn');
                copyBtn.innerHTML = '<i class="fas fa-check me-1"></i> Đã copy';
                copyBtn.classList.replace('btn-primary', 'btn-success');
                
                setTimeout(function() {
                    copyBtn.innerHTML = '<i class="fas fa-copy me-1"></i> Copy mã';
                    copyBtn.classList.replace('btn-success', 'btn-primary');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                alert('Không thể copy mã. Vui lòng thử lại.');
            });
        }
    </script>
</body>
</html><?php
session_start();

// Import config & functions
require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Lấy danh sách kiểu nút
try {
    $stmt = $conn->prepare("SELECT id, name, description, preview_image FROM button_styles");
    $stmt->execute();
    $button_styles = $stmt->fetchAll();
} catch (PDOException $e) {
    logError("Error fetching button styles: " . $e->getMessage());
    $button_styles = [];
}

// Xử lý thêm website
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = isset($_POST['site_name']) ? trim($_POST['site_name']) : '';
    $site_url = isset($_POST['site_url']) ? trim($_POST['site_url']) : '';
    $button_style = isset($_POST['button_style']) ? intval($_POST['button_style']) : 1;
    $button_position = isset($_POST['button_position']) ? $_POST['button_position'] : 'right';
    $bot_protection = isset($_POST['bot_protection']) ? 1 : 0;
    
    // Validate input
    if (empty($site_name)) {
        $error = 'Vui lòng nhập tên website.';
    } elseif (empty($site_url)) {
        $error = 'Vui lòng nhập URL website.';
    } elseif (!isValidUrl($site_url)) {
        $error = 'URL không hợp lệ. Vui lòng nhập URL đầy đủ (bao gồm http:// hoặc https://).';
    } else {
        try {
            // Kiểm tra URL đã tồn tại chưa
            $stmt = $conn->prepare("SELECT id FROM sites WHERE site_url = :site_url AND user_id = :user_id");
            $stmt->bindParam(':site_url', $site_url);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $error = 'Website với URL này đã tồn tại.';
            } else {
                // Tạo API key
                $api_key = generateApiKey();
                
                // Thêm website vào database
                $stmt = $conn->prepare("INSERT INTO sites (site_name, site_url, api_key, user_id, button_style, button_position, bot_protection) 
                                       VALUES (:site_name, :site_url, :api_key, :user_id, :button_style, :button_position, :bot_protection)");
                $stmt->bindParam(':site_name', $site_name);
                $stmt->bindParam(':site_url', $site_url);
                $stmt->bindParam(':api_key', $api_key);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':button_style', $button_style);
                $stmt->bindParam(':button_position', $button_position);
                $stmt->bindParam(':bot_protection', $bot_protection);
                
                if ($stmt->execute()) {
                    $site_id = $conn->lastInsertId();
                    $success = 'Website đã được thêm thành công!';
                } else {
                    $error = 'Không thể thêm website. Vui lòng thử lại.';
                }
            }
        } catch (PDOException $e) {
            logError("Error adding site: " . $e->getMessage());
            $error = 'Đã xảy ra lỗi. Vui lòng thử lại sau.';
        }
    }
}

// Lấy danh sách website của người dùng
try {
    $stmt = $conn->prepare("SELECT * FROM sites WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $sites = $stmt->fetchAll();
} catch (PDOException $e) {
    logError("Error fetching sites: " . $e->getMessage());
    $sites = [];
}

// Lấy thông tin người dùng
try {
    $stmt = $conn->prepare("SELECT username, email, is_admin FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch();
} catch(PDOException $e) {
    logError("Error fetching user info: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Website - ChuyenDoi.io.vn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: rgba(255, 255, 255, 0.95);
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .site-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        .site-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .site-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 15px;
        }
        .site-buttons {
            margin-top: 10px;
        }
        .site-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid rgba(0, 0, 0, 0.125);
        }
        .button-preview {
            display: inline-block;
            margin: 10px;
            text-align: center;
            cursor: pointer;
        }
        .button-preview img {
            max-width: 100px;
            border-radius: 8px;
            border: 2px solid transparent;
            transition: all 0.2s;
        }
        .button-preview.active img {
            border-color: #0d6efd;
        }
        .copy-btn {
            cursor: pointer;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-danger {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">ChuyenDoi.io.vn</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($user['is_admin']): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php"><i class="fas fa-cog"></i> Quản trị</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-id-card"></i> Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Cài đặt</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>