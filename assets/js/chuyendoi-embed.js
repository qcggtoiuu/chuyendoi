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
    
    // Contact information from attributes (for backward compatibility)
    const attrPhone = scriptTag.getAttribute('data-phone') || '';
    const attrZalo = scriptTag.getAttribute('data-zalo') || '';
    const attrMessenger = scriptTag.getAttribute('data-messenger') || '';
    const attrMaps = scriptTag.getAttribute('data-maps') || '';
    
    // Button options
    const style = scriptTag.getAttribute('data-style') || 'fab';
    const showLabels = scriptTag.getAttribute('data-show-labels') !== 'false';
    const primaryColor = scriptTag.getAttribute('data-primary-color') || '#3961AA';
    const animation = scriptTag.getAttribute('data-animation') !== 'false';
    
    // Contact information (will be updated from API)
    let phone = attrPhone;
    let zalo = attrZalo;
    let messenger = attrMessenger;
    let maps = attrMaps;
    
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
                    buttonSelector: '.fab-wrapper, .bbas-pc-contact-bar, .group-left-sidebar',
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
    
    // Generate sticky right buttons HTML (Style 2)
    const generateStickyRightButtonsHtml = function() {
        // We only need phone and zalo for this style
        if (!phone && !zalo) {
            return ''; // No required contact methods provided
        }
        
        let html = '<div class="group-left-sidebar">';
        html += '    <div class="sidebar-container">';
        
        // Phone button
        if (phone) {
            html += '        <div class="sidebar-item sidebar-cskh-btn">';
            html += `            <a href="tel:${phone}">`;
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
        if (zalo) {
            html += '        <div class="sidebar-item" id="sidebar-zalo-btn">';
            html += `            <a href="${zalo}" target="_blank">`;
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
    };
    
    // Check if buttons should be displayed and get contact information
    const checkButtonsVisibility = function(callback) {
        // Make API request to check if buttons should be displayed and get contact information
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'https://chuyendoi.io.vn/api/check_buttons.php?api_key=' + apiKey, true);
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
                            phone = response.contact_info.phone || attrPhone;
                            zalo = response.contact_info.zalo || attrZalo;
                            messenger = response.contact_info.messenger || attrMessenger;
                            maps = response.contact_info.maps || attrMaps;
                            
                            if (debug) {
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
    };
    
    // Initialize
    const init = function() {
        // Load CSS
        loadCSS();
        
        // Check if buttons should be displayed
        checkButtonsVisibility(function(showButtons, buttonStyle) {
            // Generate buttons if enabled
            if (showButtons) {
                let buttonHtml = '';
                
                // Use button style from API if available, otherwise use the attribute
                const finalStyle = buttonStyle || style;
                
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
