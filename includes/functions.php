<?php
// Prevent direct access to this file
if (!defined('TRACKING_SYSTEM')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Generate a random API key
 * 
 * @return string Random API key
 */
function generateApiKey() {
    return bin2hex(random_bytes(32));
}

/**
 * Validate API key
 * 
 * @param string $apiKey API key to validate
 * @return array|false Site data if valid, false otherwise
 */
function validateApiKey($apiKey) {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM sites WHERE api_key = ?");
    $stmt->bind_param("s", $apiKey);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get client IP address
 * 
 * @return string Client IP address
 */
function getClientIp() {
    $ipAddress = '';
    
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipAddress = $_SERVER['HTTP_FORWARDED'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
    
    // If comma-separated list, get the first IP
    if (strpos($ipAddress, ',') !== false) {
        $ipAddresses = explode(',', $ipAddress);
        $ipAddress = trim($ipAddresses[0]);
    }
    
    return $ipAddress;
}

/**
 * Get browser information from user agent
 * 
 * @param string $userAgent User agent string
 * @return array Browser name and version
 */
function getBrowserInfo($userAgent) {
    $browser = [
        'name' => 'Unknown',
        'version' => 'Unknown'
    ];
    
    // Chrome
    if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
        $browser['name'] = 'Chrome';
        $browser['version'] = $matches[1];
    }
    // Safari (must check before Chrome)
    else if (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
        $browser['name'] = 'Safari';
        
        // Get Safari version
        if (preg_match('/Version\/([0-9.]+)/', $userAgent, $versionMatches)) {
            $browser['version'] = $versionMatches[1];
        }
    }
    // Firefox
    else if (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
        $browser['name'] = 'Firefox';
        $browser['version'] = $matches[1];
    }
    // Edge
    else if (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
        $browser['name'] = 'Edge';
        $browser['version'] = $matches[1];
    }
    // IE
    else if (preg_match('/MSIE ([0-9.]+)/', $userAgent, $matches)) {
        $browser['name'] = 'Internet Explorer';
        $browser['version'] = $matches[1];
    }
    // Opera
    else if (preg_match('/Opera\/([0-9.]+)/', $userAgent, $matches)) {
        $browser['name'] = 'Opera';
        $browser['version'] = $matches[1];
    }
    
    return $browser;
}

/**
 * Get operating system information from user agent
 * 
 * @param string $userAgent User agent string
 * @return array OS name and version
 */
function getOsInfo($userAgent) {
    $os = [
        'name' => 'Unknown',
        'version' => 'Unknown'
    ];
    
    // Windows
    if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
        $os['name'] = 'Windows';
        
        switch ($matches[1]) {
            case '10.0': $os['version'] = '10'; break;
            case '6.3': $os['version'] = '8.1'; break;
            case '6.2': $os['version'] = '8'; break;
            case '6.1': $os['version'] = '7'; break;
            case '6.0': $os['version'] = 'Vista'; break;
            case '5.2': $os['version'] = 'XP Pro x64'; break;
            case '5.1': $os['version'] = 'XP'; break;
            default: $os['version'] = $matches[1]; break;
        }
    }
    // Mac OS X
    else if (preg_match('/Mac OS X ([0-9_\.]+)/', $userAgent, $matches)) {
        $os['name'] = 'Mac OS X';
        $os['version'] = str_replace('_', '.', $matches[1]);
    }
    // iOS
    else if (preg_match('/iPhone OS ([0-9_]+)/', $userAgent, $matches)) {
        $os['name'] = 'iOS';
        $os['version'] = str_replace('_', '.', $matches[1]);
    }
    // Android
    else if (preg_match('/Android ([0-9\.]+)/', $userAgent, $matches)) {
        $os['name'] = 'Android';
        $os['version'] = $matches[1];
    }
    // Linux
    else if (preg_match('/Linux/', $userAgent)) {
        $os['name'] = 'Linux';
    }
    
    return $os;
}

/**
 * Get location information from IP address using ipinfo.io
 * 
 * @param string $ip IP address
 * @return array Location information (city, country, isp)
 */
function getLocationInfo($ip) {
    $location = [
        'city' => 'Unknown',
        'country' => 'Unknown',
        'isp' => 'Unknown'
    ];
    
    // Skip for localhost or private IPs
    if ($ip == '127.0.0.1' || $ip == 'localhost' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return $location;
    }
    
    $token = IPINFO_TOKEN;
    $url = "https://ipinfo.io/{$ip}/json";
    
    if (!empty($token)) {
        $url .= "?token={$token}";
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (isset($data['city'])) {
            $location['city'] = $data['city'];
        }
        
        if (isset($data['country'])) {
            $location['country'] = $data['country'];
        }
        
        if (isset($data['org'])) {
            $location['isp'] = $data['org'];
        }
    }
    
    return $location;
}

/**
 * Calculate Z-score for anomaly detection
 * 
 * @param float $value Value to check
 * @param float $mean Mean value
 * @param float $stdDev Standard deviation
 * @return float Z-score
 */
function calculateZScore($value, $mean, $stdDev) {
    if ($stdDev == 0) {
        return 0;
    }
    
    return ($value - $mean) / $stdDev;
}

/**
 * Check if a value is an anomaly based on Z-score
 * 
 * @param float $zScore Z-score
 * @param float $threshold Threshold (default: 3.0)
 * @return bool True if anomaly, false otherwise
 */
function isAnomaly($zScore, $threshold = 3.0) {
    return abs($zScore) > $threshold;
}

/**
 * Calculate bot score based on various factors
 * 
 * @param array $factors Array of factors with their weights
 * @return float Bot score (0-1)
 */
function calculateBotScore($factors) {
    $totalScore = 0;
    $totalWeight = 0;
    
    foreach ($factors as $factor => $data) {
        $score = $data['score'];
        $weight = $data['weight'];
        
        $totalScore += $score * $weight;
        $totalWeight += $weight;
    }
    
    if ($totalWeight == 0) {
        return 0;
    }
    
    return $totalScore / $totalWeight;
}

/**
 * Check if a visitor is a bot based on bot score
 * 
 * @param float $botScore Bot score
 * @return bool True if bot, false otherwise
 */
function isBot($botScore) {
    return $botScore >= BOT_SCORE_THRESHOLD;
}

/**
 * Format time in seconds to human-readable format
 * 
 * @param int $seconds Time in seconds
 * @return string Formatted time
 */
function formatTime($seconds) {
    if ($seconds < 60) {
        return $seconds . ' seconds';
    } else if ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        $result = $hours . ' hour' . ($hours > 1 ? 's' : '');
        
        if ($minutes > 0) {
            $result .= ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }
        
        return $result;
    }
}

/**
 * Sanitize input data
 * 
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
    } else {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Log message to file
 * 
 * @param string $message Message to log
 * @param string $type Log type (info, warning, error)
 * @return void
 */
function logMessage($message, $type = 'info') {
    $logFile = __DIR__ . '/../logs/' . $type . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Check if a string contains a specific pattern
 * 
 * @param string $haystack String to search in
 * @param string $needle Pattern to search for
 * @return bool True if pattern found, false otherwise
 */
function containsPattern($haystack, $needle) {
    return strpos($haystack, $needle) !== false;
}

/**
 * Get connection type (3G, WiFi, etc.)
 * 
 * @param array $headers HTTP headers
 * @return string Connection type
 */
function getConnectionType($headers) {
    // Check for Network Information API header
    if (isset($headers['HTTP_NETINFO_EFFECTIVE_TYPE'])) {
        return $headers['HTTP_NETINFO_EFFECTIVE_TYPE'];
    }
    
    // Check for mobile network headers
    $mobileNetworkHeaders = [
        'HTTP_X_NETWORK_TYPE',
        'HTTP_X_WAP_NETWORK',
        'HTTP_X_MOBILE_GATEWAY'
    ];
    
    foreach ($mobileNetworkHeaders as $header) {
        if (isset($headers[$header])) {
            return 'Mobile (' . $headers[$header] . ')';
        }
    }
    
    // Check for common mobile carriers in user agent
    $userAgent = isset($headers['HTTP_USER_AGENT']) ? $headers['HTTP_USER_AGENT'] : '';
    
    if (preg_match('/(3G|4G|5G|LTE|EDGE|GPRS|UMTS|HSPA|EVDO)/i', $userAgent, $matches)) {
        return 'Mobile (' . $matches[1] . ')';
    }
    
    // Default to unknown
    return 'Unknown';
}

/**
 * Check if a visitor matches a fraud pattern
 * 
 * @param array $visitorData Visitor data
 * @return array Match result (isMatch, patternId, similarityScore)
 */
function matchesFraudPattern($visitorData) {
    $db = Database::getInstance();
    $result = [
        'isMatch' => false,
        'patternId' => null,
        'similarityScore' => 0
    ];
    
    // Get active fraud patterns
    $stmt = $db->prepare("
        SELECT * FROM fraud_patterns 
        WHERE is_active = 1
    ");
    $stmt->execute();
    $patterns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($patterns as $pattern) {
        $matchScore = 0;
        $matchFactors = 0;
        
        // Check IP pattern
        if (!empty($pattern['ip_pattern']) && containsPattern($visitorData['ip_address'], $pattern['ip_pattern'])) {
            $matchScore += 1;
            $matchFactors++;
        }
        
        // Check ISP pattern
        if (!empty($pattern['isp_pattern']) && containsPattern($visitorData['isp'], $pattern['isp_pattern'])) {
            $matchScore += 1;
            $matchFactors++;
        }
        
        // Check location pattern
        if (!empty($pattern['location_pattern'])) {
            $locationString = $visitorData['city'] . ', ' . $visitorData['country'];
            if (containsPattern($locationString, $pattern['location_pattern'])) {
                $matchScore += 1;
                $matchFactors++;
            }
        }
        
        // Check device pattern
        if (!empty($pattern['device_pattern'])) {
            $deviceString = $visitorData['browser'] . ' ' . $visitorData['os'];
            if (containsPattern($deviceString, $pattern['device_pattern'])) {
                $matchScore += 1;
                $matchFactors++;
            }
        }
        
        // Check behavior pattern
        if (!empty($pattern['behavior_pattern'])) {
            // This would be more complex in a real implementation
            // For now, just check if bot_score is high
            if ($visitorData['bot_score'] > 0.5) {
                $matchScore += 1;
                $matchFactors++;
            }
        }
        
        // Calculate similarity score
        $similarityScore = ($matchFactors > 0) ? ($matchScore / $matchFactors) : 0;
        
        // Check if similarity score exceeds threshold
        if ($similarityScore >= $pattern['similarity_threshold']) {
            $result['isMatch'] = true;
            $result['patternId'] = $pattern['id'];
            $result['similarityScore'] = $similarityScore;
            break;
        }
    }
    
    return $result;
}

/**
 * Should hide buttons based on fraud detection
 * 
 * @param array $visitorData Visitor data
 * @return bool True if buttons should be hidden, false otherwise
 */
function shouldHideButtons($visitorData) {
    // Check if visitor is a bot
    if (isBot($visitorData['bot_score'])) {
        return true;
    }
    
    // Check if visitor matches a fraud pattern
    $fraudMatch = matchesFraudPattern($visitorData);
    if ($fraudMatch['isMatch']) {
        return true;
    }
    
    return false;
}

/**
 * Check if user is authenticated, if not redirect to login page with return URL
 * 
 * @return void
 */
function requireLogin() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Store current URL for redirect after login
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        // Store the URL in session
        $_SESSION['redirect_url'] = $currentUrl;
        
        // Redirect to login page
        header('Location: ' . getAdminUrl() . 'login.php');
        exit;
    }
}

/**
 * Get the admin URL based on the current script path
 * 
 * @return string Admin URL with trailing slash
 */
function getAdminUrl() {
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    $adminPos = strpos($scriptPath, '/admin/');
    
    if ($adminPos !== false) {
        return substr($scriptPath, 0, $adminPos) . '/admin/';
    }
    
    // Fallback to relative path
    return '/admin/';
}
