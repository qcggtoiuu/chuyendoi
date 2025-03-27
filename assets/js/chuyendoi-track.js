/**
 * ChuyenDoi Tracking System
 * Combined tracking and button functionality
 */
(function() {
    'use strict';
    
    // Configuration
    var config = {
        apiUrl: '/api/track.php', // Relative URL for the tracking system
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
        buttonSelector: '.fab-wrapper, .bbas-pc-contact-bar, .group-left-sidebar', // Selector for interactive buttons
        debug: false, // Set to true to enable debug logging
        
        // Button options
        style: 'fab',
        phone: '',
        zalo: '',
        messenger: '',
        maps: '',
        showLabels: true,
        primaryColor: '#3961AA',
        animation: true
    };
    
    // Initialize tracking and buttons
    function init(options) {
        // Get script tag attributes if not provided in options
        if (!options) {
            options = {};
            const scriptTag = document.currentScript;
            
            if (scriptTag) {
                options.apiKey = scriptTag.getAttribute('data-api-key');
                options.apiUrl = scriptTag.getAttribute('data-api-url');
                options.debug = scriptTag.getAttribute('data-debug') === 'true';
                
                // Button options
                options.phone = scriptTag.getAttribute('data-phone');
                options.zalo = scriptTag.getAttribute('data-zalo');
                options.messenger = scriptTag.getAttribute('data-messenger');
                options.maps = scriptTag.getAttribute('data-maps');
                options.style = scriptTag.getAttribute('data-style');
                options.showLabels = scriptTag.getAttribute('data-show-labels') !== 'false';
                options.primaryColor = scriptTag.getAttribute('data-primary-color');
                options.animation = scriptTag.getAttribute('data-animation') !== 'false';
            }
        }
        
        // Merge options with default config
        if (options) {
            for (var key in options) {
                if (options.hasOwnProperty(key) && options[key] !== undefined && options[key] !== null) {
                    config[key] = options[key];
                }
            }
        }
        
        // Check if API key is provided
        if (!config.apiKey) {
            log('Error: API key is required');
            return;
        }
        
        // Load CSS
        loadCSS();
        
        // Check if buttons should be displayed
        checkButtonsVisibility(function(showButtons, buttonStyle) {
            // Generate buttons if enabled
            if (showButtons) {
                let buttonHtml = '';
                
                // Use button style from API if available, otherwise use the attribute
                const finalStyle = buttonStyle || config.style;
                
                // Generate HTML based on style
                if (finalStyle === 'fab') {
                    buttonHtml = generateFabButtonHtml();
                } else if (finalStyle === 'bar') {
                    buttonHtml = generateBarButtonHtml();
                } else if (finalStyle === 'sticky-right') {
                    buttonHtml = generateStickyRightButtonsHtml();
                }
                
                // Insert buttons into the page
                if (buttonHtml) {
                    const container = document.createElement('div');
                    container.innerHTML = buttonHtml;
                    document.body.appendChild(container);
                }
            }
            
            // Start tracking
            trackPageView();
            
            // Set up event listeners
            setupEventListeners();
            
            // Set up periodic bot checks
            setTimeout(performBotCheck, 5000); // First check after 5 seconds
            
            // Set up time tracking
            setInterval(updateTimeSpent, 30000); // Update every 30 seconds
        });
        
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
    
    // Load CSS
    function loadCSS() {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/assets/css/buttons.css';
        document.head.appendChild(link);
    }
    
    // Check if buttons should be displayed and get contact information
    function checkButtonsVisibility(callback) {
        // Make API request to check if buttons should be displayed and get contact information
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '/api/check_buttons.php?api_key=' + config.apiKey, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                let showButtons = true;
                let buttonStyle = null;
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        showButtons = response.show_buttons !== false;
                        buttonStyle = response.button_style || null;
                        
                        // Update contact information from API if available
                        if (response.contact_info) {
                            // Use API values if they exist, otherwise keep the attribute values
                            config.phone = response.contact_info.phone || config.phone;
                            config.zalo = response.contact_info.zalo || config.zalo;
                            config.messenger = response.contact_info.messenger || config.messenger;
                            config.maps = response.contact_info.maps || config.maps;
                            
                            if (config.debug) {
                                console.log('Contact info from API:', response.contact_info);
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
                
                callback(showButtons, buttonStyle);
            }
        };
        xhr.send();
    }
    
    // Generate FAB button HTML
    function generateFabButtonHtml() {
        if (!config.phone && !config.zalo && !config.messenger && !config.maps) {
            return ''; // No contact methods provided
        }
        
        let html = '<div class="fab-wrapper">';
        html += '<input id="fabCheckbox" type="checkbox" class="fab-checkbox">';
        html += `<label class="fab" for="fabCheckbox" style="background: ${config.primaryColor};">`;
        html += '<i class="icon-cps-fab-menu"></i>';
        html += '</label>';
        html += '<div class="fab-wheel">';
        
        // Maps button
        if (config.maps) {
            html += `<a class="fab-action fab-action-1" href="${config.maps}" rel="nofollow noopener" target="_blank">`;
            if (config.showLabels) {
                html += '<span class="fab-title">Địa Chỉ</span>';
            }
            html += '<div class="fab-button fab-button-1"><i class="icon-cps-local"></i></div>';
            html += '</a>';
        }
        
        // Phone button
        if (config.phone) {
            html += `<a class="fab-action fab-action-2" href="tel:${config.phone}" rel="nofollow">`;
            if (config.showLabels) {
                html += '<span class="fab-title">Hotline</span>';
            }
            html += '<div class="fab-button fab-button-2"><i class="icon-cps-phone"></i></div>';
            html += '</a>';
        }
        
        // Messenger button
        if (config.messenger) {
            html += `<a class="fab-action fab-action-3" href="${config.messenger}" rel="nofollow">`;
            if (config.showLabels) {
                html += '<span class="fab-title">Chat FB ngay</span>';
            }
            html += '<div class="fab-button fab-button-3"><i class="icon-cps-chat"></i></div>';
            html += '</a>';
        }
        
        // Zalo button
        if (config.zalo) {
            html += `<a class="fab-action fab-action-4" href="${config.zalo}" target="_blank" rel="nofollow noopener">`;
            if (config.showLabels) {
                html += '<span class="fab-title">Chat trên Zalo</span>';
            }
            html += '<div class="fab-button fab-button-4"><i class="icon-cps-chat-zalo"></i></div>';
            html += '</a>';
        }
        
        html += '</div>';
        html += '<div class="suggestions-chat-box hidden" style="display: none;">';
        html += '<div class="box-content d-flex justify-content-around align-items-center">';
        html += '<i class="fa fa-times-circle" aria-hidden="true" id="btnClose" onclick="jQuery(\'.suggestions-chat-box\').hide()"></i>';
        html += '<p class="mb-0 font-14">Liên hệ ngay <i class="fa fa-hand-o-right" aria-hidden="true"></i></p>';
        html += '</div>';
        html += '</div>';
        html += '<div class="devvn_bg"></div>';
        html += '</div>';
        
        // Add animation class if disabled
        if (!config.animation) {
            html += '<style>.fab-checkbox:not(:checked)~.fab { animation: none; }</style>';
        }
        
        return html;
    }
    
    // Generate bar button HTML
    function generateBarButtonHtml() {
        if (!config.phone && !config.zalo && !config.messenger && !config.maps) {
            return ''; // No contact methods provided
        }
        
        let html = '<ul class="bbas-pc-contact-bar">';
        
        // Facebook/Messenger button
        if (config.messenger) {
            html += '<li class="facebook">';
            html += `<a href="${config.messenger}" target="_blank" rel="nofollow noopener"></a>`;
            html += '</li>';
        }
        
        // Phone button
        if (config.phone) {
            html += '<li class="phone">';
            html += `<a href="tel:${config.phone}" rel="nofollow"></a>`;
            html += '</li>';
        }
        
        // Zalo button
        if (config.zalo) {
            html += '<li class="zalo">';
            html += `<a href="${config.zalo}" target="_blank" rel="nofollow noopener"></a>`;
            html += '</li>';
        }
        
        // Maps button
        if (config.maps) {
            html += '<li class="maps">';
            html += `<a href="${config.maps}" target="_blank" rel="nofollow noopener"></a>`;
            html += '</li>';
        }
        
        html += '</ul>';
        
        return html;
    }
    
    // Generate sticky right buttons HTML (Style 2)
    function generateStickyRightButtonsHtml() {
        // We only need phone and zalo for this style
        if (!config.phone && !config.zalo) {
            return ''; // No required contact methods provided
        }
        
        let html = '<div class="group-left-sidebar">';
        html += '    <div class="sidebar-container">';
        
        // Phone button
        if (config.phone) {
            html += '        <div class="sidebar-item sidebar-cskh-btn">';
            html += `            <a href="tel:${config.phone}">`;
            html += '                <div class="ring">';
            html += '                    <div class="coccoc-alo-phone coccoc-alo-green coccoc-alo-show">';
            html += '                        <div class="coccoc-alo-ph-circle-fill"></div>';
            html += '                        <div class="coccoc-alo-ph-img-circle">';
            html += '                            <img src="https://vuanem.com/image/icon/icons-phone-50.png" alt="Phone Icon" width="25px" height="25px">';
            html += '                        </div>';
            html += '                    </div>';
            html += '                </div>';
            html += '            </a>';
            html += '        </div>';
        }

        // Zalo button
        if (config.zalo) {
            html += '        <div class="sidebar-item" id="sidebar-zalo-btn">';
            html += `            <a href="${config.zalo}" target="_blank">`;
            html += '                <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">';
            html += '                    <circle cx="22.5" cy="22.5" r="22.5" fill="#133EBF"></circle>';
            html += '                    <rect x="8" y="10" width="28" height="28" fill="url(#pattern0_11239_19047)"></rect>';
            html += '                    <defs>';
            html += '                        <pattern id="pattern0_11239_19047" patternContentUnits="objectBoundingBox" width="1" height="1">';
            html += '                            <use xlink:href="#image0_11239_19047" transform="scale(0.00166667)"></use>';
            html += '                        </pattern>';
            html += '                        <linearGradient id="paint0_linear_11239_19047" x1="22.5" y1="0" x2="22.5" y2="45" gradientUnits="userSpaceOnUse">';
            html += '                            <stop stop-color="#133EBF"></stop>';
            html += '                            <stop offset="1" stop-color="#133EBF"></stop>';
            html += '                        </linearGradient>';
            html += '                        <image id="image0_11239_19047" width="600" height="600" xlink:href="https://vuanem.com/images/zalo-logo.webp"></image>';
            html += '                    </defs>';
            html += '                </svg>';
            html += '            </a>';
            html += '        </div>';
        }
        
        // Scroll to top button (optional)
        html += '        <div class="sidebar-item sidebar-cskh-btn" id="scrollTopBtn" style="display: none;">';
        html += '            <div class="chevron-up">';
        html += '                <img src="https://270349907.e.cdneverest.net/fast/filters:format(webp)/vuanem.com/image/chevron-up.png">';
        html += '            </div>';
        html += '        </div>';
        
        html += '    </div>';
        html += '</div>';
        
        return html;
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
            console.log('[ChuyenDoi] ' + message);
        }
    }
    
    // Initialize on load
    function onLoad() {
        // Get script tag attributes
        const scriptTag = document.currentScript || (function() {
            const scripts = document.getElementsByTagName('script');
            return scripts[scripts.length - 1];
        })();
        
        if (scriptTag) {
            const options = {
                apiKey: scriptTag.getAttribute('data-api-key'),
                apiUrl: scriptTag.getAttribute('data-api-url'),
                debug: scriptTag.getAttribute('data-debug') === 'true',
                
                // Button options
                phone: scriptTag.getAttribute('data-phone'),
                zalo: scriptTag.getAttribute('data-zalo'),
                messenger: scriptTag.getAttribute('data-messenger'),
                maps: scriptTag.getAttribute('data-maps'),
                style: scriptTag.getAttribute('data-style'),
                showLabels: scriptTag.getAttribute('data-show-labels') !== 'false',
                primaryColor: scriptTag.getAttribute('data-primary-color'),
                animation: scriptTag.getAttribute('data-animation') !== 'false'
            };
            
            init(options);
        }
    }
    
    // Expose global API
    window.ChuyenDoi = {
        init: init,
        trackClick: trackClick,
        trackEvent: trackEvent,
        isBot: function() { return config.isBot; },
        shouldHideButtons: function() { return config.hideButtons; }
    };
    
    // Initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onLoad);
    } else {
        onLoad();
    }
})();
