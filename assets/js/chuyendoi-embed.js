/**
 * ChuyenDoi Tracking System Embed Script
 * This script handles loading the tracking system, CSS, and generating buttons
 */
(function() {
    // Get script tag attributes
    const scriptTag = document.currentScript;
    const apiKey = scriptTag.getAttribute('data-api-key');
    const apiUrl = scriptTag.getAttribute('data-api-url') || 'https://chuyendoi.io.vn/api/track.php';
    const debug = scriptTag.getAttribute('data-debug') === 'true';
    
    // Contact information
    const phone = scriptTag.getAttribute('data-phone') || '';
    const zalo = scriptTag.getAttribute('data-zalo') || '';
    const messenger = scriptTag.getAttribute('data-messenger') || '';
    const maps = scriptTag.getAttribute('data-maps') || '';
    
    // Button options
    const style = scriptTag.getAttribute('data-style') || 'fab';
    const showLabels = scriptTag.getAttribute('data-show-labels') !== 'false';
    const primaryColor = scriptTag.getAttribute('data-primary-color') || '#3961AA';
    const animation = scriptTag.getAttribute('data-animation') !== 'false';
    
    // Load CSS
    const loadCSS = function() {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://chuyendoi.io.vn/assets/css/buttons.css';
        document.head.appendChild(link);
    };
    
    // Load tracker script
    const loadTracker = function() {
        const script = document.createElement('script');
        script.src = 'https://chuyendoi.io.vn/assets/js/tracker.js';
        script.async = true;
        script.onload = function() {
            // Initialize tracker
            window.Tracker.init({
                apiKey: apiKey,
                apiUrl: apiUrl,
                buttonSelector: '.fab-wrapper, .bbas-pc-contact-bar',
                debug: debug
            });
        };
        document.head.appendChild(script);
    };
    
    // Generate FAB button HTML
    const generateFabButtonHtml = function() {
        if (!phone && !zalo && !messenger && !maps) {
            return ''; // No contact methods provided
        }
        
        let html = '<div class="fab-wrapper">';
        html += '<input id="fabCheckbox" type="checkbox" class="fab-checkbox">';
        html += `<label class="fab" for="fabCheckbox" style="background: ${primaryColor};">`;
        html += '<i class="icon-cps-fab-menu"></i>';
        html += '</label>';
        html += '<div class="fab-wheel">';
        
        // Maps button
        if (maps) {
            html += `<a class="fab-action fab-action-1" href="${maps}" rel="nofollow noopener" target="_blank">`;
            if (showLabels) {
                html += '<span class="fab-title">Địa Chỉ</span>';
            }
            html += '<div class="fab-button fab-button-1"><i class="icon-cps-local"></i></div>';
            html += '</a>';
        }
        
        // Phone button
        if (phone) {
            html += `<a class="fab-action fab-action-2" href="tel:${phone}" rel="nofollow">`;
            if (showLabels) {
                html += '<span class="fab-title">Hotline</span>';
            }
            html += '<div class="fab-button fab-button-2"><i class="icon-cps-phone"></i></div>';
            html += '</a>';
        }
        
        // Messenger button
        if (messenger) {
            html += `<a class="fab-action fab-action-3" href="${messenger}" rel="nofollow">`;
            if (showLabels) {
                html += '<span class="fab-title">Chat FB ngay</span>';
            }
            html += '<div class="fab-button fab-button-3"><i class="icon-cps-chat"></i></div>';
            html += '</a>';
        }
        
        // Zalo button
        if (zalo) {
            html += `<a class="fab-action fab-action-4" href="${zalo}" target="_blank" rel="nofollow noopener">`;
            if (showLabels) {
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
        if (!animation) {
            html += '<style>.fab-checkbox:not(:checked)~.fab { animation: none; }</style>';
        }
        
        return html;
    };
    
    // Generate bar button HTML
    const generateBarButtonHtml = function() {
        if (!phone && !zalo && !messenger && !maps) {
            return ''; // No contact methods provided
        }
        
        let html = '<ul class="bbas-pc-contact-bar">';
        
        // Facebook/Messenger button
        if (messenger) {
            html += '<li class="facebook">';
            html += `<a href="${messenger}" target="_blank" rel="nofollow noopener"></a>`;
            html += '</li>';
        }
        
        // Phone button
        if (phone) {
            html += '<li class="phone">';
            html += `<a href="tel:${phone}" rel="nofollow"></a>`;
            html += '</li>';
        }
        
        // Zalo button
        if (zalo) {
            html += '<li class="zalo">';
            html += `<a href="${zalo}" target="_blank" rel="nofollow noopener"></a>`;
            html += '</li>';
        }
        
        // Maps button
        if (maps) {
            html += '<li class="maps">';
            html += `<a href="${maps}" target="_blank" rel="nofollow noopener"></a>`;
            html += '</li>';
        }
        
        html += '</ul>';
        
        return html;
    };
    
    // Check if buttons should be displayed
    const checkButtonsVisibility = function(callback) {
        // Make API request to check if buttons should be displayed
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'https://chuyendoi.io.vn/api/check_buttons.php?api_key=' + apiKey, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                let showButtons = true;
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        showButtons = response.show_buttons !== false;
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
                
                callback(showButtons);
            }
        };
        xhr.send();
    };
    
    // Initialize
    const init = function() {
        // Load CSS
        loadCSS();
        
        // Check if buttons should be displayed
        checkButtonsVisibility(function(showButtons) {
            // Generate buttons if enabled
            if (showButtons) {
                const buttonHtml = style === 'fab' ? generateFabButtonHtml() : generateBarButtonHtml();
                
                // Insert buttons into the page
                if (buttonHtml) {
                    const container = document.createElement('div');
                    container.innerHTML = buttonHtml;
                    document.body.appendChild(container);
                }
            }
            
            // Load tracker
            loadTracker();
        });
    };
    
    // Run initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
