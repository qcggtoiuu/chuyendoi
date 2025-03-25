/**
 * Chuyendoi.io.vn - Tracking Script
 * Version: 1.0.0
 * Auto-generated for each site
 */
(function() {
    // Cấu hình
    var config = {
        siteId: '{{SITE_ID}}',
        trackingUrl: '{{TRACKING_URL}}',
        buttonUrl: '{{BUTTON_URL}}',
        pingInterval: 10000, // 10 giây ping một lần
        buttonEnabled: {{BUTTON_ENABLED}},
        debug: false
    };
    
    // Tạo session ID duy nhất
    function generateSessionId() {
        return 'xxxx-xxxx-xxxx-xxxx'.replace(/[x]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        }) + '-' + new Date().getTime();
    }
    
    // Lấy sessionId từ localStorage hoặc tạo mới
    var sessionId = localStorage.getItem('chuyendoi_session_id');
    if (!sessionId) {
        sessionId = generateSessionId();
        localStorage.setItem('chuyendoi_session_id', sessionId);
    }
    
    // Khởi tạo biến và thông tin cần thiết
    var screenResolution = window.screen.width + 'x' + window.screen.height;
    var currentPage = encodeURIComponent(window.location.href);
    var referrer = encodeURIComponent(document.referrer);
    var networkType = getNetworkType();
    
    // Debug log
    function debugLog(message) {
        if (config.debug) {
            console.log('[ChuyenDoi.io.vn] ' + message);
        }
    }
    
    // Xác định loại kết nối mạng
    function getNetworkType() {
        var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (connection) {
            if (connection.type) {
                return connection.type;
            } else if (connection.effectiveType) {
                return connection.effectiveType;
            }
        }
        
        // Fallback dựa vào User-Agent
        if (/\bWIFI\b/i.test(navigator.userAgent)) {
            return 'wifi';
        } else if (/\bLTE\b/i.test(navigator.userAgent) || /\b4G\b/i.test(navigator.userAgent)) {
            return '4g';
        } else if (/\b3G\b/i.test(navigator.userAgent)) {
            return '3g';
        } else if (/\b2G\b/i.test(navigator.userAgent) || /\bGPRS\b/i.test(navigator.userAgent)) {
            return '2g';
        }
        
        return 'unknown';
    }
    
    // Tạo và gửi tracking request
    function sendTrackingRequest(params) {
        var img = document.createElement('img');
        var url = config.trackingUrl + '?site_id=' + config.siteId + 
                 '&session_id=' + sessionId +
                 '&resolution=' + screenResolution +
                 '&network=' + networkType;
        
        // Thêm các tham số khác
        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                url += '&' + key + '=' + params[key];
            }
        }
        
        img.src = url;
        img.style.display = 'none';
        img.width = 1;
        img.height = 1;
        img.onload = function() {
            debugLog('Tracking request sent: ' + url);
            // Xóa element sau khi load
            if (img.parentNode) {
                img.parentNode.removeChild(img);
            }
        };
        
        document.body.appendChild(img);
    }
    
    // Gửi tracking data ban đầu khi trang load
    function sendInitialTracking() {
        sendTrackingRequest({
            'page': currentPage,
            'referrer': referrer
        });
        debugLog('Initial tracking sent');
    }
    
    // Ping server để cập nhật thời gian online
    function startPingTimer() {
        setInterval(function() {
            sendTrackingRequest({
                'ping': 1
            });
            debugLog('Ping sent');
        }, config.pingInterval);
    }
    
    // Theo dõi các click vào số điện thoại, Zalo, Messenger
    function setupInteractionTracking() {
        document.addEventListener('click', function(e) {
            var target = e.target;
            
            // Tìm thẻ <a> gần nhất (có thể là chính nó hoặc phần tử cha)
            while (target && target.tagName !== 'A') {
                target = target.parentNode;
                if (!target || target === document) return;
            }
            
            var href = target.getAttribute('href');
            if (!href) return;
            
            var interactionType = null;
            var interactionValue = '';
            
            // Kiểm tra loại tương tác
            if (href.indexOf('tel:') === 0) {
                interactionType = 'tel';
                interactionValue = href.substring(4);
                debugLog('Phone interaction: ' + interactionValue);
            } else if (href.indexOf('https://zalo.me/') === 0 || href.indexOf('zalo.me/') === 0) {
                interactionType = 'zalo';
                interactionValue = href.split('/').pop();
                debugLog('Zalo interaction: ' + interactionValue);
            } else if (href.indexOf('https://m.me/') === 0 || href.indexOf('m.me/') === 0) {
                interactionType = 'messenger';
                interactionValue = href.split('/').pop();
                debugLog('Messenger interaction: ' + interactionValue);
            } else if (href.indexOf('goo.gl/maps') >= 0 || href.indexOf('maps.google') >= 0 || href.indexOf('google.com/maps') >= 0) {
                interactionType = 'address';
                interactionValue = href;
                debugLog('Address interaction: ' + interactionValue);
            }
            
            // Nếu là tương tác cần theo dõi, gửi thông tin
            if (interactionType) {
                sendTrackingRequest({
                    'page': currentPage,
                    'interaction': interactionType,
                    'target': encodeURIComponent(interactionValue)
                });
            }
        }, true);
    }
    
    // Load nút tương tác
    function loadInteractionButtons() {
        if (!config.buttonEnabled) return;
        
        var script = document.createElement('script');
        script.src = config.buttonUrl + '?site_id=' + config.siteId + 
                    '&session_id=' + sessionId;
        script.async = true;
        document.head.appendChild(script);
        debugLog('Button script loaded');
    }
    
    // Theo dõi thay đổi URL trong ứng dụng SPA
    function setupSPATracking() {
        if (typeof history.pushState === 'function') {
            var originalPushState = history.pushState;
            history.pushState = function() {
                originalPushState.apply(this, arguments);
                
                // Đợi một chút để đảm bảo URL đã cập nhật
                setTimeout(function() {
                    var newPage = encodeURIComponent(window.location.href);
                    sendTrackingRequest({
                        'page': newPage
                    });
                    debugLog('SPA navigation tracked: ' + newPage);
                }, 100);
            };
            
            window.addEventListener('popstate', function() {
                // Tương tự khi người dùng nhấn nút Back/Forward của trình duyệt
                setTimeout(function() {
                    var newPage = encodeURIComponent(window.location.href);
                    sendTrackingRequest({
                        'page': newPage
                    });
                    debugLog('Popstate tracked: ' + newPage);
                }, 100);
            });
        }
    }
    
    // Thêm thuộc tính để phát hiện bot
    function setupBotDetection() {
        try {
            // Kiểm tra khả năng lưu trữ localStorage
            localStorage.setItem('chuyendoi_bot_test', 'not_bot');
            
            // Kiểm tra tương tác chuột
            var mouseDetected = false;
            document.addEventListener('mousemove', function() {
                mouseDetected = true;
            });
            
            // Kiểm tra scroll
            var scrollDetected = false;
            document.addEventListener('scroll', function() {
                scrollDetected = true;
            });
            
            // Sau 5 giây kiểm tra và cập nhật nếu không có tương tác
            setTimeout(function() {
                var botChecks = [];
                
                if (!mouseDetected) botChecks.push('no_mouse');
                if (!scrollDetected) botChecks.push('no_scroll');
                
                if (botChecks.length > 0) {
                    sendTrackingRequest({
                        'bot_check': botChecks.join(',')
                    });
                    debugLog('Bot detection flags: ' + botChecks.join(','));
                }
            }, 5000);
        } catch (e) {
            // Nếu có lỗi khi thực hiện các thao tác JavaScript thông thường, có thể là bot
            sendTrackingRequest({
                'bot_check': 'js_error'
            });
            debugLog('Bot detection error: ' + e.message);
        }
    }
    
    // Khởi tạo khi DOM đã sẵn sàng
    function init() {
        sendInitialTracking();
        startPingTimer();
        setupInteractionTracking();
        loadInteractionButtons();
        setupSPATracking();
        setupBotDetection();
        debugLog('Tracking initialized');
    }
    
    // Chờ DOM sẵn sàng
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }
})();