<?php
// Tắt báo cáo lỗi cho file production
error_reporting(0);

// Đặt header
header('Content-Type: image/gif');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Import các file cần thiết
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/detect.php';

// Tạo đối tượng phát hiện thiết bị
$detect = new Mobile_Detect();

// Thu thập thông tin
$data = array(
    'ip' => getClientIP(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
    'page' => isset($_GET['page']) ? $_GET['page'] : '',
    'site_id' => isset($_GET['site_id']) ? intval($_GET['site_id']) : 0,
    'screen_resolution' => isset($_GET['resolution']) ? $_GET['resolution'] : '',
    'language' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '',
    'session_id' => isset($_GET['session_id']) ? $_GET['session_id'] : '',
    'browser' => getBrowserInfo()['browser'],
    'browser_version' => getBrowserInfo()['version'],
    'os' => getOSInfo()['os'],
    'os_version' => getOSInfo()['version'],
    'network_type' => isset($_GET['network']) ? $_GET['network'] : 'unknown',
    'timestamp' => date('Y-m-d H:i:s')
);

// Xử lý tương tác đặc biệt nếu có
$interaction = isset($_GET['interaction']) ? $_GET['interaction'] : ''; 
$target = isset($_GET['target']) ? $_GET['target'] : '';

// Xác định thêm thông tin từ IP (vị trí địa lý và ISP)
getGeoInfo($data);

// Phát hiện bot
detectBot($data);

// Kiểm tra xem session này đã tồn tại chưa
$visit_id = checkExistingSession($data);

// Nếu không phải là ping và không phải session đã tồn tại, lưu visit mới
if (!(isset($_GET['ping']) && $_GET['ping'] == '1') && !$visit_id) {
    $visit_id = saveVisit($data);
}

// Lưu thông tin tương tác nếu có
if (!empty($interaction) && !empty($target) && $visit_id) {
    saveInteraction($visit_id, $data['session_id'], $data['ip'], $data['site_id'], $interaction, $target, $data['page']);
}

// Cập nhật thời gian online nếu là ping từ session hiện tại
if (isset($_GET['ping']) && $_GET['ping'] == '1' && !empty($data['session_id'])) {
    updateSessionDuration($data['session_id']);
}

// Cập nhật thống kê hàng ngày
updateDailyStats($data, $interaction);

// Trả về một pixel gif 1x1 trong suốt
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

/**
 * Kiểm tra xem session đã tồn tại chưa
 * @param array $data
 * @return int|null ID của visit hoặc null nếu chưa tồn tại
 */
function checkExistingSession($data) {
    global $conn;
    
    if (empty($data['session_id']) || empty($data['site_id'])) {
        return null;
    }
    
    try {
        $stmt = $conn->prepare("SELECT id FROM visits WHERE session_id = :session_id AND site_id = :site_id LIMIT 1");
        $stmt->bindParam(':session_id', $data['session_id']);
        $stmt->bindParam(':site_id', $data['site_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch()['id'];
        }
    } catch(PDOException $e) {
        logError("Error checking existing session: " . $e->getMessage());
    }
    
    return null;
}

/**
 * Lưu thông tin visit
 * @param array $data
 * @return int|null ID của visit hoặc null nếu thất bại
 */
function saveVisit($data) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO visits (ip, user_agent, referrer, page, site_id, screen_resolution, 
                                browser, browser_version, os, os_version, network_type, isp,
                                language, country, city, session_id, is_bot, bot_score, bot_reasons, timestamp) 
                               VALUES (:ip, :user_agent, :referrer, :page, :site_id, :screen_resolution, 
                                :browser, :browser_version, :os, :os_version, :network_type, :isp,
                                :language, :country, :city, :session_id, :is_bot, :bot_score, :bot_reasons, :timestamp)");
        
        $stmt->bindParam(':ip', $data['ip']);
        $stmt->bindParam(':user_agent', $data['user_agent']);
        $stmt->bindParam(':referrer', $data['referrer']);
        $stmt->bindParam(':page', $data['page']);
        $stmt->bindParam(':site_id', $data['site_id']);
        $stmt->bindParam(':screen_resolution', $data['screen_resolution']);
        $stmt->bindParam(':browser', $data['browser']);
        $stmt->bindParam(':browser_version', $data['browser_version']);
        $stmt->bindParam(':os', $data['os']);
        $stmt->bindParam(':os_version', $data['os_version']);
        $stmt->bindParam(':network_type', $data['network_type']);
        $stmt->bindParam(':isp', $data['isp']);
        $stmt->bindParam(':language', $data['language']);
        $stmt->bindParam(':country', $data['country']);
        $stmt->bindParam(':city', $data['city']);
        $stmt->bindParam(':session_id', $data['session_id']);
        $stmt->bindParam(':is_bot', $data['is_bot']);
        $stmt->bindParam(':bot_score', $data['bot_score']);
        $stmt->bindParam(':bot_reasons', $data['bot_reasons']);
        $stmt->bindParam(':timestamp', $data['timestamp']);
        
        if ($stmt->execute()) {
            return $conn->lastInsertId();
        }
    } catch(PDOException $e) {
        logError("Error saving visit: " . $e->getMessage());
    }
    
    return null;
}

/**
 * Lưu thông tin tương tác (tel, zalo, messenger, address)
 * @param int $visit_id
 * @param string $session_id
 * @param string $ip
 * @param int $site_id
 * @param string $interaction_type
 * @param string $target_value
 * @param string $page
 */
function saveInteraction($visit_id, $session_id, $ip, $site_id, $interaction_type, $target_value, $page) {
    global $conn;
    
    // Validate interaction type
    if (!in_array($interaction_type, ['tel', 'zalo', 'messenger', 'address'])) {
        return;
    }
    
    try {
        $timestamp = date('Y-m-d H:i:s');
        $is_valid = 1; // Mặc định là hợp lệ, có thể thay đổi nếu kiểm tra thêm
        
        // Kiểm tra xem có phải bot không trước khi lưu tương tác
        $stmt = $conn->prepare("SELECT is_bot FROM visits WHERE id = :visit_id LIMIT 1");
        $stmt->bindParam(':visit_id', $visit_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $visit = $stmt->fetch();
            if ($visit['is_bot'] == 1) {
                $is_valid = 0; // Đánh dấu là không hợp lệ nếu là bot
            }
        }
        
        // Lưu tương tác
        $stmt = $conn->prepare("INSERT INTO interactions (visit_id, session_id, ip, site_id, interaction_type, 
                               target_value, page, is_valid, timestamp) 
                               VALUES (:visit_id, :session_id, :ip, :site_id, :interaction_type, 
                               :target_value, :page, :is_valid, :timestamp)");
        
        $stmt->bindParam(':visit_id', $visit_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':site_id', $site_id);
        $stmt->bindParam(':interaction_type', $interaction_type);
        $stmt->bindParam(':target_value', $target_value);
        $stmt->bindParam(':page', $page);
        $stmt->bindParam(':is_valid', $is_valid);
        $stmt->bindParam(':timestamp', $timestamp);
        
        $stmt->execute();
    } catch(PDOException $e) {
        logError("Error saving interaction: " . $e->getMessage());
    }
}

/**
 * Cập nhật thời gian online của session
 * @param string $session_id
 */
function updateSessionDuration($session_id) {
    global $conn;
    
    if (empty($session_id)) return;
    
    try {
        // Cập nhật thời gian online (tăng thêm 10 giây mỗi lần ping)
        $stmt = $conn->prepare("UPDATE visits SET session_duration = session_duration + 10 WHERE session_id = :session_id");
        $stmt->bindParam(':session_id', $session_id);
        $stmt->execute();
    } catch(PDOException $e) {
        logError("Error updating session duration: " . $e->getMessage());
    }
}

/**
 * Cập nhật thống kê hàng ngày
 * @param array $data
 * @param string $interaction
 */
function updateDailyStats($data, $interaction = null) {
    global $conn;
    
    if (empty($data['site_id'])) return;
    
    $today = date('Y-m-d');
    
    try {
        // Kiểm tra xem đã có bản ghi cho ngày hôm nay chưa
        $stmt = $conn->prepare("SELECT id FROM daily_stats WHERE site_id = :site_id AND date = :date");
        $stmt->bindParam(':site_id', $data['site_id']);
        $stmt->bindParam(':date', $today);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            // Chưa có, tạo mới
            $stmt = $conn->prepare("INSERT INTO daily_stats (site_id, date, visits, unique_visitors, bot_visits) VALUES (:site_id, :date, 0, 0, 0)");
            $stmt->bindParam(':site_id', $data['site_id']);
            $stmt->bindParam(':date', $today);
            $stmt->execute();
        }
        
        // Cập nhật thống kê
        if (!(isset($_GET['ping']) && $_GET['ping'] == '1')) {
            // Tăng số lượt truy cập
            $sql = "UPDATE daily_stats SET visits = visits + 1";
            
            // Nếu là bot, tăng số lượng bot
            if (isset($data['is_bot']) && $data['is_bot'] == 1) {
                $sql .= ", bot_visits = bot_visits + 1";
            } else {
                // Nếu không phải bot và là IP mới, tăng số lượng visitor
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM visits WHERE ip = :ip AND site_id = :site_id AND DATE(timestamp) = :date");
                $stmt->bindParam(':ip', $data['ip']);
                $stmt->bindParam(':site_id', $data['site_id']);
                $stmt->bindParam(':date', $today);
                $stmt->execute();
                $count = $stmt->fetch()['count'];
                
                if ($count <= 1) {
                    $sql .= ", unique_visitors = unique_visitors + 1";
                }
            }
            
            $sql .= " WHERE site_id = :site_id AND date = :date";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':site_id', $data['site_id']);
            $stmt->bindParam(':date', $today);
            $stmt->execute();
        }
        
        // Cập nhật tương tác nếu có
        if ($interaction) {
            $column = $interaction . "_interactions";
            $sql = "UPDATE daily_stats SET $column = $column + 1 WHERE site_id = :site_id AND date = :date";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':site_id', $data['site_id']);
            $stmt->bindParam(':date', $today);
            $stmt->execute();
        }
    } catch(PDOException $e) {
        logError("Error updating daily stats: " . $e->getMessage());
    }
}