/**
 * IP Tracking and Bot Detection System
 * 
 * This script collects visitor information and detects bot/automation activity.
 * It also controls the visibility of interactive buttons based on bot detection.
 */

(function() {
    'use strict';
    
    // Configuration
    var config = {
        apiUrl: 'https://chuyendoi.io.vn/api/track.php', // Domain for the tracking system
        apiKey: '', // Will be set during initialization
        visitId: null,
        botScore: 0,
        isBot: false,
        hideButtons: false,
        mouseMovements: [],
        mouseMovementSampleRate: 5, // Sample every 5th movement
        mouseMovementCount: 0,
        interactionTimings: [],
        lastActivityTime: Date.now(),
        startTime: Date.now(),
        buttonSelector: '.fab-wrapper', // Selector for interactive buttons
        debug: false // Set to true to enable debug logging
    };
    
    // Initialize tracking
    function init(options) {
        // Merge options with default config
        if (options) {
            for (var key in options) {
                if (options.hasOwnProperty(key)) {
                    config[key] = options[key];
                }
            }
        }
        
        // Check if API key is provided
        if (!config.apiKey) {
            log('Error: API key is required');
            return;
        }
        
        // Start tracking
        trackPageView();
        
        // Set up event listeners
        setupEventListeners();
        
        // Set up periodic bot checks
        setTimeout(performBotCheck, 5000); // First check after 5 seconds
        
        // Set up time tracking
        setInterval(updateTimeSpent, 30000); // Update every 30 seconds
        
        // Return public API
        return {
            getVisitId: function() { return config.visitId; },
            getBotScore: function() { return config.botScore; },
            isBot: function() { return config.isBot; },
            shouldHideButtons: function() { return config.hideButtons; },
            trackEvent: trackEvent,
            trackClick: trackClick
        };
    }
    
    // Track page view
    function trackPageView() {
        var data = {
            api_key: config.apiKey,
            action: 'pageview',
            current_page: window.location.href,
            screen_width: window.screen.width,
            screen_height: window.screen.height,
            js_enabled: true,
            connection_type: getConnectionType(),
            user_agent: navigator.userAgent,
            referrer: document.referrer || ''
        };
        
        // Get UTM parameters from URL
        var urlParams = new URLSearchParams(window.location.search);
        data.utm_source = urlParams.get('utm_source') || null;
        data.utm_medium = urlParams.get('utm_medium') || null;
        data.utm_campaign = urlParams.get('utm_campaign') || null;
        data.utm_term = urlParams.get('utm_term') || null;
        data.utm_content = urlParams.get('utm_content') || null;
        
        // Get IP address using ipify.org
        fetch('https://api.ipify.org?format=json')
            .then(function(response) { return response.json(); })
            .then(function(ipData) {
                data.ip = ipData.ip;
                sendToApi(data, handlePageViewResponse);
            })
            .catch(function(error) {
                // Proceed without IP if ipify.org is not available
                log('Error getting IP: ' + error);
                sendToApi(data, handlePageViewResponse);
            });
    }
    
    // Handle page view response
    function handlePageViewResponse(response) {
        if (response && response.success) {
            config.visitId = response.visit_id;
            config.botScore = response.bot_score;
            config.isBot = response.is_bot;
            config.hideButtons = response.hide_buttons;
            
            // Update button visibility
            updateButtonVisibility();
            
            log('Page view tracked. Visit ID: ' + config.visitId);
        } else {
            log('Error tracking page view');
        }
    }
    
    // Track click on specific links
    function trackClick(element) {
        if (!config.visitId) {
            log('Visit ID not available, cannot track click');
            return;
        }
        
        var href = element.href || '';
        var clickType = '';
        
        // Determine click type
        if (href.startsWith('tel:')) {
            clickType = 'phone';
        } else if (href.includes('zalo.me')) {
            clickType = 'zalo';
        } else if (href.includes('m.me')) {
            clickType = 'messenger';
        } else if (href.includes('google.com/maps') || href.includes('goo.gl/maps')) {
            clickType = 'maps';
        } else {
            // Not a tracked link type
            return;
        }
        
        var data = {
            api_key: config.apiKey,
            action: 'click',
            visit_id: config.visitId,
            click_type: clickType,
            click_url: href
        };
        
        sendToApi(data);
        log('Click tracked: ' + clickType + ' - ' + href);
    }
    
    // Track custom event
    function trackEvent(eventName, eventData) {
        if (!config.visitId) {
            log('Visit ID not available, cannot track event');
            return;
        }
        
        var data = {
            api_key: config.apiKey,
            action: 'event',
            visit_id: config.visitId,
            event_name: eventName,
            event_data: eventData
        };
        
        sendToApi(data);
        log('Event tracked: ' + eventName);
    }
    
    // Update time spent on page
    function updateTimeSpent() {
        if (!config.visitId) {
            return;
        }
        
        var timeSpent = Math.floor((Date.now() - config.startTime) / 1000);
        
        var data = {
            api_key: config.apiKey,
            action: 'timespent',
            visit_id: config.visitId,
            time_spent: timeSpent
        };
        
        sendToApi(data);
        log('Time spent updated: ' + timeSpent + ' seconds');
    }
    
    // Perform bot check
    function performBotCheck() {
        if (!config.visitId) {
            return;
        }
        
        // Collect browser fingerprint
        var fingerprint = collectBrowserFingerprint();
        
        var data = {
            api_key: config.apiKey,
            action: 'botcheck',
            visit_id: config.visitId,
            mouse_movements: config.mouseMovements,
            timing_data: config.interactionTimings,
            fingerprint: fingerprint
        };
        
        sendToApi(data, handleBotCheckResponse);
        
        // Reset collected data
        config.mouseMovements = [];
        config.interactionTimings = [];
        
        // Schedule next check
        setTimeout(performBotCheck, 60000); // Check every minute
    }
    
    // Handle bot check response
    function handleBotCheckResponse(response) {
        if (response && response.success) {
            config.botScore = response.bot_score;
            config.isBot = response.is_bot;
            config.hideButtons = response.hide_buttons;
            
            // Update button visibility
            updateButtonVisibility();
            
            log('Bot check completed. Bot score: ' + config.botScore);
        }
    }
    
    // Set up event listeners
    function setupEventListeners() {
        // Track mouse movements
        document.addEventListener('mousemove', function(event) {
            // Sample mouse movements to reduce data volume
            config.mouseMovementCount++;
            if (config.mouseMovementCount % config.mouseMovementSampleRate !== 0) {
                return;
            }
            
            config.mouseMovements.push([event.clientX, event.clientY]);
            
            // Keep only the last 100 movements
            if (config.mouseMovements.length > 100) {
                config.mouseMovements.shift();
            }
        });
        
        // Track user interactions
        document.addEventListener('click', function(event) {
            config.interactionTimings.push(Date.now());
            config.lastActivityTime = Date.now();
            
            // Track clicks on specific links
            var target = event.target;
            while (target && target.tagName !== 'A') {
                target = target.parentElement;
            }
            
            if (target && target.href) {
                trackClick(target);
            }
        });
        
        // Track keyboard activity
        document.addEventListener('keydown', function() {
            config.interactionTimings.push(Date.now());
            config.lastActivityTime = Date.now();
        });
        
        // Track scroll activity
        document.addEventListener('scroll', function() {
            config.lastActivityTime = Date.now();
        });
        
        // Track visibility changes
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                config.startTime = Date.now() - ((Date.now() - config.startTime) - (Date.now() - config.lastActivityTime));
            } else {
                config.lastActivityTime = Date.now();
            }
        });
        
        // Track page unload
        window.addEventListener('beforeunload', function() {
            updateTimeSpent();
        });
    }
    
    // Update button visibility based on bot detection
    function updateButtonVisibility() {
        if (config.hideButtons) {
            var buttons = document.querySelectorAll(config.buttonSelector);
            for (var i = 0; i < buttons.length; i++) {
                buttons[i].style.display = 'none';
            }
            log('Buttons hidden due to bot detection');
        }
    }
    
    // Collect browser fingerprint
    function collectBrowserFingerprint() {
        var fingerprint = {
            webdriver: checkWebDriver(),
            navigator: collectNavigatorInfo(),
            canvas: checkCanvasFingerprint(),
            webgl: checkWebGLFingerprint()
        };
        
        return fingerprint;
    }
    
    // Check for WebDriver
    function checkWebDriver() {
        return navigator.webdriver === true;
    }
    
    // Collect navigator information
    function collectNavigatorInfo() {
        return {
            user_agent: navigator.userAgent,
            app_version: navigator.appVersion,
            platform: navigator.platform,
            plugins_length: navigator.plugins ? navigator.plugins.length : 0,
            languages_length: navigator.languages ? navigator.languages.length : 0
        };
    }
    
    // Check canvas fingerprint
    function checkCanvasFingerprint() {
        try {
            var canvas = document.createElement('canvas');
            canvas.width = 200;
            canvas.height = 50;
            
            var ctx = canvas.getContext('2d');
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillStyle = '#f60';
            ctx.fillRect(125, 1, 62, 20);
            ctx.fillStyle = '#069';
            ctx.fillText('IP Tracking', 2, 15);
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.fillText('IP Tracking', 4, 17);
            
            var dataURL = canvas.toDataURL();
            
            // Check if canvas is blank or has unexpected output
            if (dataURL === 'data:,') {
                return 'anomaly';
            }
            
            return 'normal';
        } catch (e) {
            return 'error';
        }
    }
    
    // Check WebGL fingerprint
    function checkWebGLFingerprint() {
        try {
            var canvas = document.createElement('canvas');
            var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            
            if (!gl) {
                return 'not_supported';
            }
            
            var debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            if (!debugInfo) {
                return 'no_debug_info';
            }
            
            var vendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
            var renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
            
            if (!vendor || !renderer) {
                return 'anomaly';
            }
            
            return 'normal';
        } catch (e) {
            return 'error';
        }
    }
    
    // Get connection type
    function getConnectionType() {
        var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        
        if (connection) {
            return connection.effectiveType || connection.type || 'unknown';
        }
        
        return 'unknown';
    }
    
    // Send data to API
    function sendToApi(data, callback) {
        fetch(config.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(function(response) { return response.json(); })
        .then(function(responseData) {
            if (callback) {
                callback(responseData);
            }
        })
        .catch(function(error) {
            log('API error: ' + error);
        });
    }
    
    // Log message (if debug is enabled)
    function log(message) {
        if (config.debug) {
            console.log('[Tracker] ' + message);
        }
    }
    
    // Expose global API
    window.Tracker = {
        init: init,
        trackClick: trackClick,
        trackEvent: trackEvent,
        isBot: function() { return config.isBot; },
        shouldHideButtons: function() { return config.hideButtons; }
    };
})();
