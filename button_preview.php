<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/button.php';

// Check if API key is provided
$apiKey = isset($_GET['api_key']) ? $_GET['api_key'] : '';
$style = isset($_GET['style']) ? $_GET['style'] : 'fab';
$phone = isset($_GET['phone']) ? $_GET['phone'] : '';
$zalo = isset($_GET['zalo']) ? $_GET['zalo'] : '';
$messenger = isset($_GET['messenger']) ? $_GET['messenger'] : '';
$maps = isset($_GET['maps']) ? $_GET['maps'] : '';
$showLabels = isset($_GET['show_labels']) ? ($_GET['show_labels'] === '1') : true;
$primaryColor = isset($_GET['primary_color']) ? $_GET['primary_color'] : '#3961AA';
$animation = isset($_GET['animation']) ? ($_GET['animation'] === '1') : true;

// Validate API key if provided
$site = null;
if (!empty($apiKey)) {
    $site = validateApiKey($apiKey);
}

// Generate button options
$buttonOptions = [
    'style' => $style,
    'phone' => $phone,
    'zalo' => $zalo,
    'messenger' => $messenger,
    'maps' => $maps,
    'show_labels' => $showLabels,
    'primary_color' => $primaryColor,
    'animation' => $animation
];

// Generate script options
$scriptOptions = [
    'debug' => true,
    'apiUrl' => API_URL . '/track.php'
];

// Generate tracking code
$trackingCode = '';
if ($site) {
    $trackingCode = generateTrackingCode($apiKey, $buttonOptions, $scriptOptions);
} else {
    $trackingCode = generateButtonHtml($buttonOptions);
}

// Get embed code
$embedCode = htmlspecialchars($trackingCode);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Button Preview</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/buttons.css">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .preview-container {
            position: relative;
            width: 100%;
            height: 500px;
            border: 1px solid #ddd;
            background-color: #fff;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .code-container {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .code-container pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .form-group label {
            font-weight: 600;
        }
        .color-preview {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 1px solid #ddd;
            vertical-align: middle;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Button Preview</h1>
        
        <div class="row">
            <div class="col-md-8">
                <div class="preview-container">
                    <?php echo $trackingCode; ?>
                </div>
                
                <div class="code-container">
                    <h4>Embed Code</h4>
                    <pre><code><?php echo $embedCode; ?></code></pre>
                    <button class="btn btn-sm btn-primary mt-2" onclick="copyToClipboard()">Copy Code</button>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Button Options</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="">
                            <?php if ($site): ?>
                            <div class="form-group">
                                <label for="api_key">API Key</label>
                                <input type="text" class="form-control" id="api_key" name="api_key" value="<?php echo htmlspecialchars($apiKey); ?>" readonly>
                                <small class="form-text text-muted">Site: <?php echo htmlspecialchars($site['name']); ?></small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="style">Button Style</label>
                                <select class="form-control" id="style" name="style">
                                    <option value="fab" <?php echo $style === 'fab' ? 'selected' : ''; ?>>Floating Action Button</option>
                                    <option value="bar" <?php echo $style === 'bar' ? 'selected' : ''; ?>>Contact Bar</option>
                                    <option value="sticky-right" <?php echo $style === 'sticky-right' ? 'selected' : ''; ?>>Sticky Right (Zalo & Call)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="zalo">Zalo Link</label>
                                <input type="text" class="form-control" id="zalo" name="zalo" value="<?php echo htmlspecialchars($zalo); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="messenger">Messenger Link</label>
                                <input type="text" class="form-control" id="messenger" name="messenger" value="<?php echo htmlspecialchars($messenger); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="maps">Maps Link</label>
                                <input type="text" class="form-control" id="maps" name="maps" value="<?php echo htmlspecialchars($maps); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="primary_color">Primary Color</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($primaryColor); ?>">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <span class="color-preview" style="background-color: <?php echo htmlspecialchars($primaryColor); ?>"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="show_labels" name="show_labels" value="1" <?php echo $showLabels ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="show_labels">Show Labels</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="animation" name="animation" value="1" <?php echo $animation ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="animation">Enable Animation</label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Preview</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard() {
            var codeElement = document.querySelector('.code-container code');
            var textArea = document.createElement('textarea');
            textArea.value = codeElement.textContent;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            alert('Code copied to clipboard!');
        }
        
        document.getElementById('primary_color').addEventListener('input', function() {
            document.querySelector('.color-preview').style.backgroundColor = this.value;
        });
    </script>
</body>
</html>
