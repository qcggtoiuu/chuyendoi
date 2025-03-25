<?php
// Import các file cần thiết
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/detect.php';

// Kiểm tra tham số
$site_id = isset($_GET['site_id']) ? intval($_GET['site_id']) : 0;
$callback = isset($_GET['callback']) ? $_GET['callback'] : '';

// Kiểm tra IP, session và phát hiện bot
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : '';
$ip = getClientIP();
$botMode = false;

// Nếu có session_id, kiểm tra xem có phải bot không
if (!empty($session_id)) {
    try {
        $stmt = $conn->prepare("SELECT is_bot, bot_score FROM visits WHERE session_id = :session_id ORDER BY id DESC LIMIT 1");
        $stmt->bindParam(':session_id', $session_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $visit = $stmt->fetch();
            if ($visit['is_bot'] == 1 || $visit['bot_score'] >= BOT_THRESHOLD) {
                $botMode = true;
            }
        }
    } catch (PDOException $e) {
        logError("Error checking bot status: " . $e->getMessage());
    }
}

// Lấy thông tin website
try {
    $stmt = $conn->prepare("SELECT s.id, s.site_name, s.site_url, s.bot_protection, s.button_style, s.button_position, 
                          b.html_template, b.css_code 
                          FROM sites s
                          JOIN button_styles b ON s.button_style = b.id
                          WHERE s.id = :site_id");
    $stmt->bindParam(':site_id', $site_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $site = $stmt->fetch();
    } else {
        // Site không tồn tại
        header('Content-Type: application/javascript');
        echo 'console.error("ChuyenDoi.io.vn: Site ID không hợp lệ");';
        exit;
    }
} catch (PDOException $e) {
    logError("Error fetching site info: " . $e->getMessage());
    header('Content-Type: application/javascript');
    echo 'console.error("ChuyenDoi.io.vn: Lỗi hệ thống");';
    exit;
}

// Tạo mã HTML và CSS từ template
$html = $site['html_template'];
$css = $site['css_code'];

// Thay thế các biến trong template
$html = str_replace('{{SITE_ID}}', $site_id, $html);
$html = str_replace('{{TRACKING_URL}}', TRACKING_URL, $html);
$html = str_replace('{{SESSION_ID}}', $session_id, $html);

// Xử lý ẩn nút nếu là bot và bot_protection được bật
$isButtonHidden = ($botMode && $site['bot_protection'] == 1) ? true : false;

// Set content type
header('Content-Type: application/javascript');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Access-Control-Allow-Origin: *');

// Tạo mã JavaScript để nhúng vào trang
?>
(function() {
    // Kiểm tra xem đã load chưa
    if (document.getElementById('chuyendoi-buttons-<?php echo $site_id; ?>')) {
        return;
    }
    
    // Tạo container
    var container = document.createElement('div');
    container.id = 'chuyendoi-container-<?php echo $site_id; ?>';
    
    <?php if ($isButtonHidden): ?>
    // Ẩn nút nếu là bot
    container.style.display = 'none';
    <?php endif; ?>
    
    // Tạo CSS
    var style = document.createElement('style');
    style.type = 'text/css';
    style.innerHTML = `<?php echo $css; ?>`;
    
    // Thiết lập HTML
    container.innerHTML = `<?php echo $html; ?>`;
    
    // Thêm vào trang
    document.head.appendChild(style);
    document.body.appendChild(container);
    
    // Tracking code
    var trackInteraction = function(type, target) {
        var img = document.createElement('img');
        img.src = '<?php echo TRACKING_URL; ?>?site_id=<?php echo $site_id; ?>&session_id=<?php echo $session_id; ?>&interaction=' + type + '&target=' + encodeURIComponent(target);
        img.style.display = 'none';
        img.width = 1;
        img.height = 1;
        document.body.appendChild(img);
    };
    
    // Thêm sự kiện click cho các liên kết
    var setupEvents = function() {
        // Tel links
        var telLinks = document.querySelectorAll('#chuyendoi-container-<?php echo $site_id; ?> a[href^="tel:"]');
        for (var i = 0; i < telLinks.length; i++) {
            telLinks[i].addEventListener('click', function(e) {
                var phone = this.getAttribute('href').replace('tel:', '');
                trackInteraction('tel', phone);
            });
        }
        
        // Zalo links
        var zaloLinks = document.querySelectorAll('#chuyendoi-container-<?php echo $site_id; ?> a[href*="zalo.me"]');
        for (var i = 0; i < zaloLinks.length; i++) {
            zaloLinks[i].addEventListener('click', function(e) {
                var zaloId = this.getAttribute('href').split('/').pop();
                trackInteraction('zalo', zaloId);
            });
        }
        
        // Messenger links
        var messengerLinks = document.querySelectorAll('#chuyendoi-container-<?php echo $site_id; ?> a[href*="m.me"]');
        for (var i = 0; i < messengerLinks.length; i++) {
            messengerLinks[i].addEventListener('click', function(e) {
                var messengerId = this.getAttribute('href').split('/').pop();
                trackInteraction('messenger', messengerId);
            });
        }
        
        // Address links
        var addressLinks = document.querySelectorAll('#chuyendoi-container-<?php echo $site_id; ?> a[href*="maps"]');
        for (var i = 0; i < addressLinks.length; i++) {
            addressLinks[i].addEventListener('click', function(e) {
                var address = this.getAttribute('href');
                trackInteraction('address', address);
            });
        }
    };
    
    // Thiết lập sự kiện sau khi DOM đã load
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setupEvents();
    } else {
        document.addEventListener('DOMContentLoaded', setupEvents);
    }
    
    <?php if (!empty($callback)): ?>
    // Gọi callback nếu được cung cấp
    if (typeof <?php echo $callback; ?> === 'function') {
        <?php echo $callback; ?>();
    }
    <?php endif; ?>
    
    // Bot detection script
    var botCheck = function() {
        var hasMouseMoved = false;
        var hasScrolled = false;
        
        document.addEventListener('mousemove', function() {
            hasMouseMoved = true;
        });
        
        document.addEventListener('scroll', function() {
            hasScrolled = true;
        });
        
        // Kiểm tra sau 5 giây
        setTimeout(function() {
            var botCheckImg = document.createElement('img');
            var botFlags = [];
            
            if (!hasMouseMoved) botFlags.push('no_mouse');
            if (!hasScrolled) botFlags.push('no_scroll');
            
            if (botFlags.length > 0) {
                botCheckImg.src = '<?php echo TRACKING_URL; ?>?site_id=<?php echo $site_id; ?>&session_id=<?php echo $session_id; ?>&bot_check=' + botFlags.join(',');
                botCheckImg.style.display = 'none';
                document.body.appendChild(botCheckImg);
            }
        }, 5000);
    };
    
    botCheck();
})();