<?php
/**
 * File chứa các hàm tiện ích cho hệ thống
 */

/**
 * Lấy IP thực của người dùng
 * @return string
 */
function getClientIP() {
    $ip = '';
    
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        // Nếu có nhiều IP, lấy IP đầu tiên
        if (strpos($ip, ',') !== false) {
            $ip = explode(',', $ip)[0];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    // Nếu vẫn không xác định được IP hoặc IP là localhost
    if (empty($ip) || $ip == '127.0.0.1' || $ip == '::1') {
        // Sử dụng ipify.org để lấy IP thực
        $externalIP = getExternalIP();
        if (!empty($externalIP)) {
            $ip = $externalIP;
        }
    }
    
    return filter_var(trim($ip), FILTER_VALIDATE_IP) ? $ip : '';
}

/**
 * Lấy IP bên ngoài sử dụng ipify.org
 * @return string
 */
function getExternalIP() {
    try {
        $response = @file_get_contents('https://api64.ipify.org?format=json');
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['ip'])) {
                return $data['ip'];
            }
        }
    } catch (Exception $e) {
        logError("Error getting external IP: " . $e->getMessage());
    }
    
    return '';
}

/**
 * Lấy thông tin trình duyệt
 * @return array
 */
function getBrowserInfo() {
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $browser = 'Unknown';
    $version = '';
    
    if (empty($userAgent)) {
        return ['browser' => $browser, 'version' => $version];
    }
    
    $browsers = [
        'Firefox' => 'Firefox',
        'MSIE' => 'Internet Explorer',
        'Trident' => 'Internet Explorer',
        'Edge' => 'Edge',
        'Edg' => 'Edge',
        'Chrome' => 'Chrome',
        'CriOS' => 'Chrome',
        'Safari' => 'Safari',
        'Opera' => 'Opera',
        'OPR' => 'Opera',
        'YaBrowser' => 'Yandex',
        'Maxthon' => 'Maxthon',
        'Konqueror' => 'Konqueror',
        'UCBrowser' => 'UC Browser',
        'SamsungBrowser' => 'Samsung Browser'
    ];
    
    foreach ($browsers as $key => $value) {
        if (strpos($userAgent, $key) !== false) {
            $browser = $value;
            
            // Lấy phiên bản
            $pattern = '/(?:' . $key . '|Version)[\/ ]+([0-9.]+)/';
            if ($key == 'Trident') {
                $pattern = '/rv:([0-9.]+)/';
            }
            
            if (preg_match($pattern, $userAgent, $matches)) {
                $version = $matches[1];
            }
            
            break;
        }
    }
    
    return ['browser' => $browser, 'version' => $version];
}

/**
 * Lấy thông tin hệ điều hành
 * @return array
 */
function getOSInfo() {
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $os = 'Unknown';
    $version = '';
    
    if (empty($userAgent)) {
        return ['os' => $os, 'version' => $version];
    }
    
    $platforms = [
        '/windows nt 11/i' => ['Windows', '11'],
        '/windows nt 10/i' => ['Windows', '10'],
        '/windows nt 6.3/i' => ['Windows', '8.1'],
        '/windows nt 6.2/i' => ['Windows', '8'],
        '/windows nt 6.1/i' => ['Windows', '7'],
        '/windows nt 6.0/i' => ['Windows', 'Vista'],
        '/windows nt 5.2/i' => ['Windows', 'Server 2003/XP x64'],
        '/windows nt 5.1/i' => ['Windows', 'XP'],
        '/windows xp/i' => ['Windows', 'XP'],
        '/mac os x ([0-9_]+)/i' => ['Mac OS X', '$1'],
        '/mac os x/i' => ['Mac OS X', ''],
        '/macintosh|mac os/i' => ['Mac OS', ''],
        '/android ([0-9.]+)/i' => ['Android', '$1'],
        '/android/i' => ['Android', ''],
        '/iphone os ([0-9_]+)/i' => ['iOS', '$1'],
        '/iphone/i' => ['iOS', ''],
        '/ipad os ([0-9_]+)/i' => ['iOS', '$1'],
        '/ipad/i' => ['iOS', ''],
        '/linux/i' => ['Linux', ''],
        '/ubuntu/i' => ['Ubuntu', ''],
        '/debian/i' => ['Debian', ''],
        '/CrOS/i' => ['Chrome OS', '']
    ];
    
    foreach ($platforms as $regex => $value) {
        if (preg_match($regex, $userAgent, $matches)) {
            $os = $value[0];
            $version = isset($matches[1]) ? str_replace('_', '.', $matches[1]) : $value[1];
            break;
        }
    }
    
    return ['os' => $os, 'version' => $version];
}

/**
 * Xác định thông tin địa lý từ IP
 * @param array &$data
 */
function getGeoInfo(&$data) {
    if (empty($data['ip'])) return;
    
    // Thử lấy thông tin từ ip-api.com (không cần API key)
    try {
        $url = "http://ip-api.com/json/" . $data['ip'] . "?fields=status,country,countryCode,city,isp";
        $response = @file_get_contents($url);
        
        if ($response) {
            $geoData = json_decode($response, true);
            if ($geoData && $geoData['status'] == 'success') {
                $data['country'] = $geoData['countryCode'];
                $data['city'] = $geoData['city'];
                $data['isp'] = $geoData['isp'];
                return;
            }
        }
    } catch (Exception $e) {
        logError("GeoIP error (ip-api): " . $e->getMessage());
    }
    
    // Backup: Thử với ipinfo.io nếu có API key
    if (defined('IPINFO_API_KEY') && !empty(IPINFO_API_KEY)) {
        try {
            $url = "https://ipinfo.io/" . $data['ip'] . "?token=" . IPINFO_API_KEY;
            $response = @file_get_contents($url);
            
            if ($response) {
                $geoData = json_decode($response, true);
                if ($geoData) {
                    $data['country'] = isset($geoData['country']) ? $geoData['country'] : '';
                    $data['city'] = isset($geoData['city']) ? $geoData['city'] : '';
                    $data['isp'] = isset($geoData['org']) ? $geoData['org'] : '';
                }
            }
        } catch (Exception $e) {
            logError("GeoIP error (ipinfo): " . $e->getMessage());
        }
    }
}

/**
 * Phát hiện bot
 * @param array &$data
 */
function detectBot(&$data) {
    global $conn;
    
    $botScore = 0;
    $isBot = 0;
    $botReasons = [];
    
    // 1. Kiểm tra User Agent
    $botSignatures = [
        'bot', 'crawl', 'spider', 'slurp', 'bingbot', 'googlebot', 'yandex', 'baidu', 
        'selenium', 'puppeteer', 'phantomjs', 'headless', 'python-requests', 'curl', 
        'wget', 'zgrab', 'metasploit'
    ];
    
    foreach ($botSignatures as $signature) {
        if (stripos($data['user_agent'], $signature) !== false) {
            $botScore += 20;
            $botReasons[] = "User-Agent chứa '$signature'";
            break;
        }
    }
    
    // 2. Kiểm tra headers
    if (empty($_SERVER['HTTP_ACCEPT']) || empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $botScore += 15;
        $botReasons[] = "Thiếu header Accept hoặc Accept-Language";
    }
    
    // 3. Kiểm tra screen resolution
    if (empty($data['screen_resolution']) || $data['screen_resolution'] == '1x1') {
        $botScore += 10;
        $botReasons[] = "Độ phân giải màn hình không hợp lệ";
    }
    
    // 4. Kiểm tra truy cập quá nhanh (session_id nhưng không có referrer)
    if (!empty($data['session_id']) && empty($data['referrer'])) {
        $botScore += 10;
        $botReasons[] = "Không có referrer với session đã tồn tại";
    }
    
    // 5. Kiểm tra danh sách pattern từ database
    try {
        $stmt = $conn->prepare("SELECT pattern, score FROM bot_patterns WHERE pattern_type = 'user_agent' AND is_active = 1");
        $stmt->execute();
        $patterns = $stmt->fetchAll();
        
        foreach ($patterns as $pattern) {
            if (stripos($data['user_agent'], $pattern['pattern']) !== false) {
                $botScore += $pattern['score'];
                $botReasons[] = "User-Agent khớp pattern DB: '" . $pattern['pattern'] . "'";
                break;
            }
        }
        
        // Kiểm tra IP blacklist
        $stmt = $conn->prepare("SELECT pattern, score FROM bot_patterns WHERE pattern_type = 'ip' AND is_active = 1");
        $stmt->execute();
        $ipPatterns = $stmt->fetchAll();
        
        foreach ($ipPatterns as $pattern) {
            if ($data['ip'] == $pattern['pattern'] || (strpos($pattern['pattern'], '*') !== false && 
                fnmatch($pattern['pattern'], $data['ip']))) {
                $botScore += $pattern['score'];
                $botReasons[] = "IP trong danh sách blacklist";
                break;
            }
        }
    } catch (PDOException $e) {
        logError("Error checking bot patterns: " . $e->getMessage());
    }
    
    // 6. Nếu bot_check từ JavaScript gửi lên
    if (isset($_GET['bot_check'])) {
        if ($_GET['bot_check'] == 'no_mouse' || $_GET['bot_check'] == 'js_error') {
            $botScore += 15;
            $botReasons[] = "JavaScript check: " . $_GET['bot_check'];
        }
    }
    
    // Điểm cao = khả năng là bot cao
    if ($botScore >= BOT_THRESHOLD) {
        $isBot = 1;
    }
    
    $data['is_bot'] = $isBot;
    $data['bot_score'] = $botScore;
    $data['bot_reasons'] = implode("; ", $botReasons);
}

/**
 * Tạo an toàn một mã API key
 * @return string
 */
function generateApiKey() {
    return bin2hex(random_bytes(32));
}

/**
 * Mã hóa mật khẩu
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Kiểm tra mật khẩu
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Tạo token cho xác thực
 * @param int $userId
 * @return string
 */
function generateAuthToken($userId) {
    $token = bin2hex(random_bytes(32));
    
    // Lưu token vào session
    $_SESSION['auth_token'] = $token;
    $_SESSION['user_id'] = $userId;
    
    return $token;
}

/**
 * Kiểm tra quyền truy cập
 * @param int $requiredLevel
 * @return bool
 */
function checkAccess($requiredLevel = 0) {
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            // Nếu yêu cầu admin và người dùng là admin
            if ($requiredLevel > 0 && $user['is_admin'] == 1) {
                return true;
            }
            
            // Nếu không yêu cầu admin
            if ($requiredLevel == 0) {
                return true;
            }
        }
    } catch (PDOException $e) {
        logError("Error checking access: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Kiểm tra site ID thuộc về user hiện tại
 * @param int $siteId
 * @return bool
 */
function checkSiteOwnership($siteId) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT id FROM sites WHERE id = :site_id AND user_id = :user_id");
        $stmt->bindParam(':site_id', $siteId);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        return ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
        logError("Error checking site ownership: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Tạo nút tương tác theo site_id
 * @param int $siteId
 * @return array
 */
function generateInteractionButton($siteId) {
    global $conn;
    
    try {
        // Lấy thông tin site và kiểu nút
        $stmt = $conn->prepare("
            SELECT s.id, s.button_style, s.button_position, b.html_template, b.css_code 
            FROM sites s
            JOIN button_styles b ON s.button_style = b.id
            WHERE s.id = :site_id
        ");
        $stmt->bindParam(':site_id', $siteId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $site = $stmt->fetch();
            
            $html = $site['html_template'];
            $css = $site['css_code'];
            
            // Thay thế các biến trong template
            $html = str_replace('{{SITE_ID}}', $siteId, $html);
            $html = str_replace('{{TRACKING_URL}}', TRACKING_URL, $html);
            
            // Thêm id cho việc xác định bot (sẽ được ẩn nếu là bot)
            $html = str_replace('<div class="fab-wrapper">', '<div class="fab-wrapper" id="chuyendoi-buttons-' . $siteId . '">', $html);
            
            return [
                'html' => $html,
                'css' => $css,
                'position' => $site['button_position']
            ];
        }
    } catch (PDOException $e) {
        logError("Error generating interaction button: " . $e->getMessage());
    }
    
    // Mặc định nếu có lỗi
    return [
        'html' => '',
        'css' => '',
        'position' => 'right'
    ];
}

/**
 * Kiểm tra hợp lệ của URL
 * @param string $url
 * @return bool
 */
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Format số lượng theo dạng có dấu phân cách
 * @param int $number
 * @return string
 */
function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

/**
 * Format thời gian online
 * @param int $seconds
 * @return string
 */
function formatOnlineTime($seconds) {
    if ($seconds < 60) {
        return $seconds . ' giây';
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        return $minutes . ' phút ' . $seconds . ' giây';
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return $hours . ' giờ ' . $minutes . ' phút';
    }
}

/**
 * Rút gọn chuỗi (URL, text...)
 * @param string $string
 * @param int $length
 * @param string $append
 * @return string
 */
function truncateString($string, $length = 50, $append = '...') {
    if (strlen($string) > $length) {
        $string = substr($string, 0, $length) . $append;
    }
    return $string;
}

/**
 * Xử lý và làm sạch input từ người dùng
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Tạo lịch sử API call
 * @param int $userId
 * @param int $siteId
 * @param string $endpoint
 * @param int $statusCode
 */
function logApiCall($userId, $siteId, $endpoint, $statusCode = 200) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO api_logs (user_id, site_id, endpoint, ip, status_code) VALUES (:user_id, :site_id, :endpoint, :ip, :status_code)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':site_id', $siteId);
        $stmt->bindParam(':endpoint', $endpoint);
        
        $ip = getClientIP();
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':status_code', $statusCode);
        
        $stmt->execute();
    } catch (PDOException $e) {
        logError("Error logging API call: " . $e->getMessage());
    }
}

/**
 * Kiểm tra giới hạn API của người dùng
 * @param int $userId
 * @return bool True nếu vẫn còn trong giới hạn
 */
function checkApiLimit($userId) {
    global $conn;
    
    try {
        // Lấy giới hạn API của người dùng
        $stmt = $conn->prepare("SELECT api_limit FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            $limit = $user['api_limit'];
            
            // Đếm số lượng API call trong ngày
            $today = date('Y-m-d');
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM api_logs 
                WHERE user_id = :user_id 
                AND DATE(created_at) = :today
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':today', $today);
            $stmt->execute();
            
            $count = $stmt->fetch()['count'];
            
            // Còn trong giới hạn
            return ($count < $limit);
        }
    } catch (PDOException $e) {
        logError("Error checking API limit: " . $e->getMessage());
    }
    
    return false;
}
