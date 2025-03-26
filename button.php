<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

/**
 * Generate button HTML
 * 
 * @param array $options Button options
 * @return string Button HTML
 */
function generateButtonHtml($options = []) {
    // Default options
    $defaults = [
        'style' => 'fab', // 'fab', 'bar', or 'sticky-right'
        'phone' => '',
        'zalo' => '',
        'messenger' => '',
        'maps' => '',
        'show_labels' => true,
        'primary_color' => '#3961AA',
        'animation' => true
    ];
    
    // Merge options with defaults
    $options = array_merge($defaults, $options);
    
    // Validate options
    if (empty($options['phone']) && empty($options['zalo']) && empty($options['messenger']) && empty($options['maps'])) {
        return '<!-- Error: At least one contact method is required -->';
    }
    
    // Generate HTML based on style
    if ($options['style'] === 'fab') {
        return generateFabButtonHtml($options);
    } else if ($options['style'] === 'bar') {
        return generateBarButtonHtml($options);
    } else if ($options['style'] === 'sticky-right') {
        return generateStickyRightButtonsHtml($options);
    } else {
        // Default to FAB style if unknown style is provided
        return generateFabButtonHtml($options);
    }
}

/**
 * Generate FAB button HTML
 * 
 * @param array $options Button options
 * @return string Button HTML
 */
function generateFabButtonHtml($options) {
    $html = '<div class="fab-wrapper">' . PHP_EOL;
    $html .= '   <input id="fabCheckbox" type="checkbox" class="fab-checkbox">' . PHP_EOL;
    $html .= '   <label class="fab" for="fabCheckbox" style="background: ' . htmlspecialchars($options['primary_color']) . ';">' . PHP_EOL;
    $html .= '      <i class="icon-cps-fab-menu"></i>' . PHP_EOL;
    $html .= '      <!-- <i class="icon-cps-close"></i> -->' . PHP_EOL;
    $html .= '   </label>' . PHP_EOL;
    $html .= '   <div class="fab-wheel">' . PHP_EOL;
    
    // Maps button
    if (!empty($options['maps'])) {
        $html .= '      <a class="fab-action fab-action-1" href="' . htmlspecialchars($options['maps']) . '" rel="nofollow noopener" target="_blank">' . PHP_EOL;
        if ($options['show_labels']) {
            $html .= '         <span class="fab-title">Địa Chỉ</span>' . PHP_EOL;
        }
        $html .= '         <div class="fab-button fab-button-1"><i class="icon-cps-local"></i></div>' . PHP_EOL;
        $html .= '      </a>' . PHP_EOL;
    }
    
    // Phone button
    if (!empty($options['phone'])) {
        $html .= '      <a class="fab-action fab-action-2" href="tel:' . htmlspecialchars($options['phone']) . '" rel="nofollow">' . PHP_EOL;
        if ($options['show_labels']) {
            $html .= '         <span class="fab-title">Hotline</span>' . PHP_EOL;
        }
        $html .= '         <div class="fab-button fab-button-2"><i class="icon-cps-phone"></i></div>' . PHP_EOL;
        $html .= '      </a>' . PHP_EOL;
    }
    
    // Messenger button
    if (!empty($options['messenger'])) {
        $html .= '      <a class="fab-action fab-action-3" href="' . htmlspecialchars($options['messenger']) . '" rel="nofollow">' . PHP_EOL;
        if ($options['show_labels']) {
            $html .= '         <span class="fab-title">Chat FB ngay</span>' . PHP_EOL;
        }
        $html .= '         <div class="fab-button fab-button-3"><i class="icon-cps-chat"></i></div>' . PHP_EOL;
        $html .= '      </a>' . PHP_EOL;
    }
    
    // Zalo button
    if (!empty($options['zalo'])) {
        $html .= '      <a class="fab-action fab-action-4" href="' . htmlspecialchars($options['zalo']) . '" target="_blank" rel="nofollow noopener">' . PHP_EOL;
        if ($options['show_labels']) {
            $html .= '         <span class="fab-title">Chat trên Zalo</span>' . PHP_EOL;
        }
        $html .= '         <div class="fab-button fab-button-4"><i class="icon-cps-chat-zalo"></i></div>' . PHP_EOL;
        $html .= '      </a>' . PHP_EOL;
    }
    
    $html .= '   </div>' . PHP_EOL;
    $html .= '   <div class="suggestions-chat-box hidden" style="display: none;">' . PHP_EOL;
    $html .= '      <div class="box-content d-flex justify-content-around align-items-center">' . PHP_EOL;
    $html .= '         <i class="fa fa-times-circle" aria-hidden="true" id="btnClose" onclick="jQuery(\'.suggestions-chat-box\').hide()"></i>' . PHP_EOL;
    $html .= '         <p class="mb-0 font-14">Liên hệ ngay <i class="fa fa-hand-o-right" aria-hidden="true"></i></p>' . PHP_EOL;
    $html .= '      </div>' . PHP_EOL;
    $html .= '   </div>' . PHP_EOL;
    $html .= '   <div class="devvn_bg"></div>' . PHP_EOL;
    $html .= '</div>' . PHP_EOL;
    
    // Add animation class if enabled
    if (!$options['animation']) {
        $html .= '<style>.fab-checkbox:not(:checked)~.fab { animation: none; }</style>' . PHP_EOL;
    }
    
    return $html;
}

/**
 * Generate bar button HTML
 * 
 * @param array $options Button options
 * @return string Button HTML
 */
function generateBarButtonHtml($options) {
    $html = '<ul class="bbas-pc-contact-bar">' . PHP_EOL;
    
    // Facebook/Messenger button
    if (!empty($options['messenger'])) {
        $html .= '   <li class="facebook">' . PHP_EOL;
        $html .= '      <a href="' . htmlspecialchars($options['messenger']) . '" target="_blank" rel="nofollow noopener"></a>' . PHP_EOL;
        $html .= '   </li>' . PHP_EOL;
    }
    
    // Phone button
    if (!empty($options['phone'])) {
        $html .= '   <li class="phone">' . PHP_EOL;
        $html .= '      <a href="tel:' . htmlspecialchars($options['phone']) . '" rel="nofollow"></a>' . PHP_EOL;
        $html .= '   </li>' . PHP_EOL;
    }
    
    // Zalo button
    if (!empty($options['zalo'])) {
        $html .= '   <li class="zalo">' . PHP_EOL;
        $html .= '      <a href="' . htmlspecialchars($options['zalo']) . '" target="_blank" rel="nofollow noopener"></a>' . PHP_EOL;
        $html .= '   </li>' . PHP_EOL;
    }
    
    // Maps button
    if (!empty($options['maps'])) {
        $html .= '   <li class="maps">' . PHP_EOL;
        $html .= '      <a href="' . htmlspecialchars($options['maps']) . '" target="_blank" rel="nofollow noopener"></a>' . PHP_EOL;
        $html .= '   </li>' . PHP_EOL;
    }
    
    $html .= '</ul>' . PHP_EOL;
    
    return $html;
}

/**
 * Generate sticky right buttons HTML (Style 2)
 * 
 * @param array $options Button options
 * @return string Button HTML
 */
function generateStickyRightButtonsHtml($options) {
    // We only need phone and zalo for this style
    if (empty($options['phone']) && empty($options['zalo'])) {
        return '<!-- Error: Phone or Zalo is required for sticky-right style -->';
    }
    
    $html = '<div class="group-left-sidebar">' . PHP_EOL;
    $html .= '    <div class="sidebar-container">' . PHP_EOL;
    
    // Phone button
    if (!empty($options['phone'])) {
        $html .= '        <div class="sidebar-item sidebar-cskh-btn">' . PHP_EOL;
        $html .= '            <a href="tel:' . htmlspecialchars($options['phone']) . '">' . PHP_EOL;
        $html .= '                <div class="ring">' . PHP_EOL;
        $html .= '                    <div class="coccoc-alo-phone coccoc-alo-green coccoc-alo-show">' . PHP_EOL;
        $html .= '                        <div class="coccoc-alo-ph-circle-fill"></div>' . PHP_EOL;
        $html .= '                        <div class="coccoc-alo-ph-img-circle">' . PHP_EOL;
        $html .= '                            <img src="https://vuanem.com/image/icon/icons-phone-50.png" alt="Phone Icon" width="25px" height="25px">' . PHP_EOL;
        $html .= '                        </div>' . PHP_EOL;
        $html .= '                    </div>' . PHP_EOL;
        $html .= '                </div>' . PHP_EOL;
        $html .= '            </a>' . PHP_EOL;
        $html .= '        </div>' . PHP_EOL;
    }

    // Zalo button
    if (!empty($options['zalo'])) {
        $html .= '        <div class="sidebar-item" id="sidebar-zalo-btn">' . PHP_EOL;
        $html .= '            <a href="' . htmlspecialchars($options['zalo']) . '" target="_blank">' . PHP_EOL;
        $html .= '                <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">' . PHP_EOL;
        $html .= '                    <circle cx="22.5" cy="22.5" r="22.5" fill="#133EBF"></circle>' . PHP_EOL;
        $html .= '                    <rect x="8" y="10" width="28" height="28" fill="url(#pattern0_11239_19047)"></rect>' . PHP_EOL;
        $html .= '                    <defs>' . PHP_EOL;
        $html .= '                        <pattern id="pattern0_11239_19047" patternContentUnits="objectBoundingBox" width="1" height="1">' . PHP_EOL;
        $html .= '                            <use xlink:href="#image0_11239_19047" transform="scale(0.00166667)"></use>' . PHP_EOL;
        $html .= '                        </pattern>' . PHP_EOL;
        $html .= '                        <linearGradient id="paint0_linear_11239_19047" x1="22.5" y1="0" x2="22.5" y2="45" gradientUnits="userSpaceOnUse">' . PHP_EOL;
        $html .= '                            <stop stop-color="#133EBF"></stop>' . PHP_EOL;
        $html .= '                            <stop offset="1" stop-color="#133EBF"></stop>' . PHP_EOL;
        $html .= '                        </linearGradient>' . PHP_EOL;
        $html .= '                        <image id="image0_11239_19047" width="600" height="600" xlink:href="https://vuanem.com/images/zalo-logo.webp"></image>' . PHP_EOL;
        $html .= '                    </defs>' . PHP_EOL;
        $html .= '                </svg>' . PHP_EOL;
        $html .= '            </a>' . PHP_EOL;
        $html .= '        </div>' . PHP_EOL;
    }
    
    // Scroll to top button (optional)
    $html .= '        <div class="sidebar-item sidebar-cskh-btn" id="scrollTopBtn" style="display: none;">' . PHP_EOL;
    $html .= '            <div class="chevron-up">' . PHP_EOL;
    $html .= '                <img src="https://270349907.e.cdneverest.net/fast/filters:format(webp)/vuanem.com/image/chevron-up.png">' . PHP_EOL;
    $html .= '            </div>' . PHP_EOL;
    $html .= '        </div>' . PHP_EOL;
    
    $html .= '    </div>' . PHP_EOL;
    $html .= '</div>' . PHP_EOL;
    
    return $html;
}

/**
 * Generate tracking script
 * 
 * @param string $apiKey API key
 * @param array $options Script options
 * @return string Script HTML
 */
function generateTrackingScript($apiKey, $options = []) {
    // Default options
    $defaults = [
        'debug' => false,
        'buttonSelector' => '.fab-wrapper, .bbas-pc-contact-bar, .sticky-right-buttons',
        'apiUrl' => API_URL . '/track.php'
    ];
    
    // Merge options with defaults
    $options = array_merge($defaults, $options);
    
    $html = '<script>' . PHP_EOL;
    $html .= '(function() {' . PHP_EOL;
    $html .= '    // Load tracking script' . PHP_EOL;
    $html .= '    var script = document.createElement("script");' . PHP_EOL;
    $html .= '    script.src = "' . htmlspecialchars(API_URL) . '/assets/js/tracker.js";' . PHP_EOL;
    $html .= '    script.async = true;' . PHP_EOL;
    $html .= '    script.onload = function() {' . PHP_EOL;
    $html .= '        // Initialize tracker' . PHP_EOL;
    $html .= '        window.Tracker.init({' . PHP_EOL;
    $html .= '            apiKey: "' . htmlspecialchars($apiKey) . '",' . PHP_EOL;
    $html .= '            apiUrl: "' . htmlspecialchars($options['apiUrl']) . '",' . PHP_EOL;
    $html .= '            buttonSelector: "' . htmlspecialchars($options['buttonSelector']) . '",' . PHP_EOL;
    $html .= '            debug: ' . ($options['debug'] ? 'true' : 'false') . PHP_EOL;
    $html .= '        });' . PHP_EOL;
    $html .= '    };' . PHP_EOL;
    $html .= '    document.head.appendChild(script);' . PHP_EOL;
    $html .= '    ' . PHP_EOL;
    $html .= '    // Load CSS' . PHP_EOL;
    $html .= '    var link = document.createElement("link");' . PHP_EOL;
    $html .= '    link.rel = "stylesheet";' . PHP_EOL;
    $html .= '    link.href = "' . htmlspecialchars(API_URL) . '/assets/css/buttons.css";' . PHP_EOL;
    $html .= '    document.head.appendChild(link);' . PHP_EOL;
    $html .= '})();' . PHP_EOL;
    $html .= '</script>' . PHP_EOL;
    
    return $html;
}

/**
 * Generate complete tracking code for client websites
 * 
 * @param string $apiKey API key
 * @param array $buttonOptions Button options
 * @param array $scriptOptions Script options
 * @return string Complete tracking code
 */
function generateTrackingCode($apiKey, $buttonOptions = [], $scriptOptions = []) {
    $html = '<!-- IP Tracking and Bot Detection System -->' . PHP_EOL;
    
    // Check if we should show the buttons
    $showButtons = true;
    
    // Get site information from database
    if (defined('TRACKING_SYSTEM')) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT show_buttons FROM sites WHERE api_key = ?");
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $site = $result->fetch_assoc();
            $showButtons = (bool)$site['show_buttons'];
        }
    }
    
    // Only generate button HTML if show_buttons is true
    if ($showButtons) {
        $html .= generateButtonHtml($buttonOptions) . PHP_EOL;
    }
    
    $html .= generateTrackingScript($apiKey, $scriptOptions) . PHP_EOL;
    
    return $html;
}

// If this file is accessed directly, return 403 Forbidden
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access not allowed.');
}
