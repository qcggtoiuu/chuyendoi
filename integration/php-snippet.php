<?php
/**
 * ChuyenDoi Tracking System - PHP Integration Snippet
 * 
 * This snippet allows you to integrate the ChuyenDoi tracking system into your PHP website.
 * It will track visitor information and display interactive buttons.
 * 
 * @param string $apiKey API key for the tracking system
 * @param array $options Button options
 * @return string HTML code to embed in your website
 */
function chuyendoi_tracking($apiKey, $options = []) {
    // Default options
    $defaults = [
        'style' => 'fab', // 'fab' or 'bar'
        'phone' => '',
        'zalo' => '',
        'messenger' => '',
        'maps' => '',
        'show_labels' => true,
        'primary_color' => '#3961AA',
        'animation' => true,
        'debug' => false
    ];
    
    // Merge options with defaults
    $options = array_merge($defaults, $options);
    
    // Build query string
    $queryParams = [
        'api_key' => $apiKey,
        'style' => $options['style'],
        'phone' => $options['phone'],
        'zalo' => $options['zalo'],
        'messenger' => $options['messenger'],
        'maps' => $options['maps'],
        'show_labels' => $options['show_labels'] ? '1' : '0',
        'primary_color' => $options['primary_color'],
        'animation' => $options['animation'] ? '1' : '0'
    ];
    
    $queryString = http_build_query($queryParams);
    
    // Generate tracking code with data attributes
    $html = '<!-- ChuyenDoi Tracking System -->' . PHP_EOL;
    $html .= '<script src="https://chuyendoi.io.vn/assets/js/chuyendoi-track.js"' . PHP_EOL;
    $html .= '        data-api-key="' . htmlspecialchars($apiKey) . '"' . PHP_EOL;
    
    // Add optional data attributes
    if (!empty($options['phone'])) {
        $html .= '        data-phone="' . htmlspecialchars($options['phone']) . '"' . PHP_EOL;
    }
    if (!empty($options['zalo'])) {
        $html .= '        data-zalo="' . htmlspecialchars($options['zalo']) . '"' . PHP_EOL;
    }
    if (!empty($options['messenger'])) {
        $html .= '        data-messenger="' . htmlspecialchars($options['messenger']) . '"' . PHP_EOL;
    }
    if (!empty($options['maps'])) {
        $html .= '        data-maps="' . htmlspecialchars($options['maps']) . '"' . PHP_EOL;
    }
    if ($options['style'] !== 'fab') {
        $html .= '        data-style="' . htmlspecialchars($options['style']) . '"' . PHP_EOL;
    }
    if (!$options['show_labels']) {
        $html .= '        data-show-labels="false"' . PHP_EOL;
    }
    if ($options['primary_color'] !== '#3961AA') {
        $html .= '        data-primary-color="' . htmlspecialchars($options['primary_color']) . '"' . PHP_EOL;
    }
    if (!$options['animation']) {
        $html .= '        data-animation="false"' . PHP_EOL;
    }
    if ($options['debug']) {
        $html .= '        data-debug="true"' . PHP_EOL;
    }
    
    $html .= '></script>' . PHP_EOL;
    
    return $html;
}

/**
 * Example usage:
 * 
 * <?php
 * // At the end of your page, before the closing </body> tag
 * echo chuyendoi_tracking('your-api-key', [
 *     'phone' => '0916152929',
 *     'zalo' => 'https://zalo.me/0916152929',
 *     'messenger' => 'https://m.me/dienmaytotvietnam',
 *     'maps' => 'https://goo.gl/maps/Z4pipWWc1GW2aY6p8'
 * ]);
 * ?>
 */
