<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Get API key from query string or use a default one for testing
$apiKey = isset($_GET['api_key']) ? $_GET['api_key'] : '74006658a29161ccbd5eccd67994e1fed358837705e91c3a1e36dbcc7a2ad33d';

// Validate API key
$site = validateApiKey($apiKey);

// If API key is invalid, show error
if (!$site) {
    die('Invalid API key');
}

// Get debug mode from query string
$debug = isset($_GET['debug']) && $_GET['debug'] === '1';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Combined Tracking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            color: #3961AA;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .test-section {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .test-section h2 {
            margin-top: 0;
        }
        .code-block {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 3px;
            font-family: monospace;
            white-space: pre-wrap;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background: #3961AA;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .debug-info {
            background: #fffbf0;
            border: 1px solid #ffe0b2;
            padding: 15px;
            margin-top: 20px;
            border-radius: 3px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Combined Tracking Script</h1>
        
        <div class="test-section">
            <h2>Thông tin API Key</h2>
            <p>API Key: <strong><?php echo htmlspecialchars($apiKey); ?></strong></p>
            <p>Site ID: <strong><?php echo $site['id']; ?></strong></p>
            <p>Site Name: <strong><?php echo htmlspecialchars($site['name']); ?></strong></p>
            <p>Domain: <strong><?php echo htmlspecialchars($site['domain']); ?></strong></p>
        </div>
        
        <div class="test-section">
            <h2>Mã nhúng đang sử dụng</h2>
            <div class="code-block">&lt;script 
  src="https://chuyendoi.io.vn/assets/js/chuyendoi-track.js" 
  data-api-key="<?php echo htmlspecialchars($apiKey); ?>"
  data-debug="<?php echo $debug ? 'true' : 'false'; ?>"
  data-phone="<?php echo htmlspecialchars($site['phone'] ?? ''); ?>"
  data-zalo="<?php echo htmlspecialchars($site['zalo'] ?? ''); ?>"
  data-messenger="<?php echo htmlspecialchars($site['messenger'] ?? ''); ?>"
  data-maps="<?php echo htmlspecialchars($site['maps'] ?? ''); ?>"
  data-style="fab"
  data-show-labels="true"
  data-primary-color="#3961AA"
  data-animation="true"
&gt;&lt;/script&gt;</div>
        </div>
        
        <div class="test-section">
            <h2>Kiểm tra các sự kiện</h2>
            <p>Nhấp vào các nút bên dưới để kiểm tra việc theo dõi các sự kiện:</p>
            
            <a href="tel:<?php echo htmlspecialchars($site['phone'] ?? '0987654321'); ?>" class="button">Gọi điện</a>
            <a href="<?php echo htmlspecialchars($site['zalo'] ?? 'https://zalo.me/0987654321'); ?>" target="_blank" class="button">Zalo</a>
            <a href="<?php echo htmlspecialchars($site['messenger'] ?? 'https://m.me/facebook'); ?>" target="_blank" class="button">Messenger</a>
            <a href="<?php echo htmlspecialchars($site['maps'] ?? 'https://goo.gl/maps/vietnam'); ?>" target="_blank" class="button">Bản đồ</a>
            
            <button onclick="trackCustomEvent()" class="button">Sự kiện tùy chỉnh</button>
        </div>
        
        <?php if ($debug): ?>
        <div class="debug-info">
            <h2>Thông tin Debug</h2>
            <p>Mở Console trong Developer Tools để xem thông tin debug.</p>
            <div id="debug-output"></div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Include the tracking script -->
    <script 
      src="https://chuyendoi.io.vn/assets/js/chuyendoi-track.js" 
      data-api-key="<?php echo htmlspecialchars($apiKey); ?>"
      data-debug="<?php echo $debug ? 'true' : 'false'; ?>"
      data-phone="<?php echo htmlspecialchars($site['phone'] ?? ''); ?>"
      data-zalo="<?php echo htmlspecialchars($site['zalo'] ?? ''); ?>"
      data-messenger="<?php echo htmlspecialchars($site['messenger'] ?? ''); ?>"
      data-maps="<?php echo htmlspecialchars($site['maps'] ?? ''); ?>"
      data-style="fab"
      data-show-labels="true"
      data-primary-color="#3961AA"
      data-animation="true"
    ></script>
    
    <script>
        // Function to track custom event
        function trackCustomEvent() {
            if (window.ChuyenDoi) {
                window.ChuyenDoi.trackEvent('custom_event', {
                    category: 'test',
                    action: 'click',
                    label: 'custom_button'
                });
                
                alert('Sự kiện tùy chỉnh đã được gửi!');
            } else {
                alert('Lỗi: ChuyenDoi không được khởi tạo!');
            }
        }
        
        // Add debug information
        if (<?php echo $debug ? 'true' : 'false'; ?>) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    var debugOutput = document.getElementById('debug-output');
                    
                    if (window.ChuyenDoi) {
                        var visitId = window.ChuyenDoi.getVisitId ? window.ChuyenDoi.getVisitId() : 'N/A';
                        var botScore = window.ChuyenDoi.getBotScore ? window.ChuyenDoi.getBotScore() : 'N/A';
                        var isBot = window.ChuyenDoi.isBot ? window.ChuyenDoi.isBot() : 'N/A';
                        
                        debugOutput.innerHTML = '<p>Visit ID: <strong>' + visitId + '</strong></p>' +
                                               '<p>Bot Score: <strong>' + botScore + '</strong></p>' +
                                               '<p>Is Bot: <strong>' + isBot + '</strong></p>' +
                                               '<p class="success">Tracking script đã được khởi tạo thành công!</p>';
                    } else {
                        debugOutput.innerHTML = '<p class="error">Lỗi: ChuyenDoi không được khởi tạo!</p>';
                    }
                }, 2000); // Wait 2 seconds for tracking to initialize
            });
        }
    </script>
</body>
</html>
