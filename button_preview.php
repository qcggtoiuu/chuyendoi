<?php
// Import config & functions
require_once 'includes/config.php'; 
require_once 'includes/functions.php';

// Lấy site_id
$site_id = isset($_GET['site_id']) ? intval($_GET['site_id']) : 0;

// Lấy thông tin button
$button_html = '';
$button_css = '';
$button_position = 'right';

if ($site_id > 0) {
    try {
        $stmt = $conn->prepare("
            SELECT s.id, s.button_style, s.button_position, b.html_template, b.css_code 
            FROM sites s
            JOIN button_styles b ON s.button_style = b.id
            WHERE s.id = :site_id
        ");
        $stmt->bindParam(':site_id', $site_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $site = $stmt->fetch();
            
            $html = $site['html_template'];
            $css = $site['css_code'];
            $button_position = $site['button_position'];
            
            // Thay thế các biến trong template
            $html = str_replace('{{SITE_ID}}', $site_id, $html);
            $html = str_replace('{{TRACKING_URL}}', TRACKING_URL, $html);
            $html = str_replace('{{SESSION_ID}}', 'preview-' . uniqid(), $html);
            
            $button_html = $html;
            $button_css = $css;
        }
    } catch (PDOException $e) {
        $button_html = '<div class="alert alert-danger">Lỗi: Không thể tải thông tin nút. ' . $e->getMessage() . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem trước nút tương tác</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }
        .preview-message {
            text-align: center;
            padding: 20px;
        }
        <?php echo $button_css; ?>
        
        <?php if ($button_position == 'left'): ?>
        .fab-wrapper {
            left: 10px !important;
            right: auto !important;
        }
        <?php elseif ($button_position == 'bottom'): ?>
        .fab-wrapper {
            bottom: 10px !important;
            left: 50% !important;
            right: auto !important;
            transform: translateX(-50%) !important;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <div class="preview-message">
        <h3>Xem trước nút tương tác</h3>
        <p>Vị trí: <strong><?php echo ucfirst($button_position); ?></strong></p>
    </div>
    
    <?php echo $button_html; ?>
    
    <script>
        // Vô hiệu hóa các link trong bản xem trước
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    alert('Đây chỉ là bản xem trước. Link sẽ hoạt động trên website thực.');
                });
            });
        });
    </script>
</body>
</html>