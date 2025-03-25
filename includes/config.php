<?php
// Thông tin kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'username'); // Thay đổi theo thông tin hosting thực
define('DB_PASS', 'password'); // Thay đổi theo thông tin hosting thực
define('DB_NAME', 'chuyendoi_tracking');

// URL của hệ thống
define('SITE_URL', 'https://chuyendoi.io.vn');
define('TRACKING_URL', 'https://chuyendoi.io.vn/track.php');
define('BUTTON_URL', 'https://chuyendoi.io.vn/button.php');

// Cấu hình phát hiện bot
define('BOT_THRESHOLD', 30); // Ngưỡng điểm để xác định là bot
define('STRICT_BOT_PROTECTION', true); // Bật/tắt chế độ bảo vệ nghiêm ngặt

// API Keys cho các dịch vụ bên thứ 3 (nếu dùng)
define('IPINFO_API_KEY', ''); // API key cho ipinfo.io (nếu sử dụng)
define('IPAPI_KEY', ''); // API key cho ip-api.com (nếu sử dụng)

// Cài đặt chung
define('SESSION_LIFETIME', 1800); // Thời gian tồn tại session (30 phút)
define('DEBUG_MODE', false); // Bật/tắt chế độ debug

// Kết nối database
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    if (DEBUG_MODE) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please try again later.");
    }
}

// Hàm xử lý lỗi và ghi log
function logError($message, $level = 'ERROR') {
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] [$level] $message" . PHP_EOL;
    
    // Đảm bảo thư mục logs tồn tại
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    // Ghi log
    error_log($log_message, 3, $log_file);
    
    // Nếu đang ở chế độ debug, hiển thị lỗi
    if (DEBUG_MODE && $level == 'ERROR') {
        echo "<div style='color:red;'>Error: $message</div>";
    }
}

// Thiết lập múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');