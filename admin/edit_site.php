<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../button.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get database instance
$db = Database::getInstance();

// Get user information
$userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if site ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: sites.php');
    exit;
}

$siteId = (int)$_GET['id'];

// Get site information
$stmt = $db->prepare("SELECT * FROM sites WHERE id = ?");
$stmt->bind_param("i", $siteId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: sites.php');
    exit;
}

$site = $result->fetch_assoc();

// Initialize variables
$name = $site['name'];
$domain = $site['domain'];
$phone = $site['phone'];
$zalo = $site['zalo'];
$messenger = $site['messenger'];
$maps = $site['maps'];
$apiKey = $site['api_key'];
$success = false;
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $domain = isset($_POST['domain']) ? sanitizeInput($_POST['domain']) : '';
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $zalo = isset($_POST['zalo']) ? sanitizeInput($_POST['zalo']) : '';
    $messenger = isset($_POST['messenger']) ? sanitizeInput($_POST['messenger']) : '';
    $maps = isset($_POST['maps']) ? sanitizeInput($_POST['maps']) : '';
    
    // Validate form data
    if (empty($name)) {
        $error = 'Website name is required';
    } else if (empty($domain)) {
        $error = 'Domain is required';
    } else {
        // Check if domain already exists (excluding current site)
        $stmt = $db->prepare("SELECT id FROM sites WHERE domain = ? AND id != ?");
        $stmt->bind_param("si", $domain, $siteId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Domain already exists';
        } else {
            // Update site in database
            $stmt = $db->prepare("
                UPDATE sites 
                SET name = ?, domain = ?, phone = ?, zalo = ?, messenger = ?, maps = ?
                WHERE id = ?
            ");
            
            $stmt->bind_param("ssssssi", $name, $domain, $phone, $zalo, $messenger, $maps, $siteId);
            
            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = 'Error updating site: ' . $db->error();
            }
        }
    }
}

// Generate button options
$buttonOptions = [
    'style' => 'fab',
    'phone' => !empty($phone) ? $phone : '0916152929', // Default phone if empty
    'zalo' => !empty($zalo) ? $zalo : 'https://zalo.me/0916152929', // Default zalo if empty
    'messenger' => !empty($messenger) ? $messenger : 'https://m.me/dienmaytotvietnam', // Default messenger if empty
    'maps' => !empty($maps) ? $maps : 'https://goo.gl/maps/Z4pipWWc1GW2aY6p8', // Default maps if empty
    'show_labels' => true,
    'primary_color' => '#3961AA',
    'animation' => true
];

// Generate script options
$scriptOptions = [
    'debug' => false,
    'apiUrl' => API_URL . '/track.php'
];

// Generate tracking code
$trackingCode = generateTrackingCode($apiKey, $buttonOptions, $scriptOptions);

// Get embed code
$embedCode = htmlspecialchars($trackingCode);

// Get default or provided values for code snippets
$phoneValue = !empty($phone) ? $phone : '0916152929';
$zaloValue = !empty($zalo) ? $zalo : 'https://zalo.me/0916152929';
$messengerValue = !empty($messenger) ? $messenger : 'https://m.me/dienmaytotvietnam';
$mapsValue = !empty($maps) ? $maps : 'https://goo.gl/maps/Z4pipWWc1GW2aY6p8';

// Generate PHP code snippet
$phpSnippet = "<?php\n";
$phpSnippet .= "// Add this code at the end of your page, before the closing </body> tag\n";
$phpSnippet .= "echo chuyendoi_tracking('{$apiKey}', [\n";
$phpSnippet .= "    'phone' => '{$phoneValue}',\n";
$phpSnippet .= "    'zalo' => '{$zaloValue}',\n";
$phpSnippet .= "    'messenger' => '{$messengerValue}',\n";
$phpSnippet .= "    'maps' => '{$mapsValue}'\n";
$phpSnippet .= "]);\n";
$phpSnippet .= "?>";

// Generate JavaScript code snippet
$jsSnippet = "import ChuyenDoiTracker from '../components/ChuyenDoiTracker';\n\n";
$jsSnippet .= "// Add this component at the end of your page layout\n";
$jsSnippet .= "<ChuyenDoiTracker\n";
$jsSnippet .= "  apiKey=\"{$apiKey}\"\n";
$jsSnippet .= "  phone=\"{$phoneValue}\"\n";
$jsSnippet .= "  zalo=\"{$zaloValue}\"\n";
$jsSnippet .= "  messenger=\"{$messengerValue}\"\n";
$jsSnippet .= "  maps=\"{$mapsValue}\"\n";
$jsSnippet .= "/>";

// Page title
$pageTitle = 'Edit Website';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Hệ Thống Tracking IP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/buttons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: rgba(255, 255, 255, 1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .navbar-brand {
            font-weight: 700;
        }
        .dropdown-menu {
            right: 0;
            left: auto;
        }
        .btn-primary {
            background-color: #3961AA;
            border-color: #3961AA;
        }
        .btn-primary:hover {
            background-color: #2c4e8a;
            border-color: #2c4e8a;
        }
        .preview-container {
            position: relative;
            width: 100%;
            height: 300px;
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
            max-height: 200px;
            overflow-y: auto;
        }
        .nav-tabs .nav-link {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        .nav-tabs .nav-link.active {
            background-color: #f5f5f5;
            border-color: #ddd #ddd #f5f5f5;
        }
        .tab-content {
            border: 1px solid #ddd;
            border-top: none;
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            background-color: #f5f5f5;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 d-none d-md-block sidebar">
                <div class="text-center mb-4">
                    <h5 class="text-white">Tracking IP</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="sites.php">
                            <i class="fas fa-globe"></i> Websites
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="visits.php">
                            <i class="fas fa-eye"></i> Visits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clicks.php">
                            <i class="fas fa-mouse-pointer"></i> Clicks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="anomalies.php">
                            <i class="fas fa-exclamation-triangle"></i> Anomalies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fraud.php">
                            <i class="fas fa-ban"></i> Fraud Patterns
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <main role="main" class="col-md-10 ml-sm-auto px-4">
                <!-- Top navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="sites.php">Websites</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="add_site.php">Add Website</a>
                            </li>
                            <li class="nav-item active">
                                <a class="nav-link" href="#">Edit Website</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="fas fa-user"></i> Profile
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                
                <!-- Page content -->
                <div class="container-fluid">
                    <h1 class="h2 mb-4"><?php echo $pageTitle; ?>: <?php echo htmlspecialchars($name); ?></h1>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> Website has been updated successfully.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> <?php echo $error; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Website Information</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <div class="form-group">
                                            <label for="name">Website Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                            <small class="form-text text-muted">Enter a name for the website (e.g. My Company Website)</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="domain">Domain <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="domain" name="domain" value="<?php echo htmlspecialchars($domain); ?>" required>
                                            <small class="form-text text-muted">Enter the domain name without http:// or https:// (e.g. example.com)</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="api_key">API Key</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="api_key" value="<?php echo htmlspecialchars($apiKey); ?>" readonly>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary copy-btn" type="button" data-clipboard-target="#api_key">
                                                        <i class="fas fa-copy"></i> Copy
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">This is your unique API key for tracking</small>
                                        </div>
                                        
                                        <hr>
                                        <h5 class="mb-3">Contact Information</h5>
                                        <p class="text-muted">These will be used as default values for the interactive buttons.</p>
                                        
                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                                            <small class="form-text text-muted">Enter the phone number for the phone button (e.g. 0916152929)</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="zalo">Zalo Link</label>
                                            <input type="text" class="form-control" id="zalo" name="zalo" value="<?php echo htmlspecialchars($zalo); ?>">
                                            <small class="form-text text-muted">Enter the Zalo link (e.g. https://zalo.me/0916152929)</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="messenger">Messenger Link</label>
                                            <input type="text" class="form-control" id="messenger" name="messenger" value="<?php echo htmlspecialchars($messenger); ?>">
                                            <small class="form-text text-muted">Enter the Messenger link (e.g. https://m.me/dienmaytotvietnam)</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="maps">Maps Link</label>
                                            <input type="text" class="form-control" id="maps" name="maps" value="<?php echo htmlspecialchars($maps); ?>">
                                            <small class="form-text text-muted">Enter the Google Maps link (e.g. https://goo.gl/maps/Z4pipWWc1GW2aY6p8)</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Update Website</button>
                                            <a href="sites.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Integration Code</h5>
                                </div>
                                <div class="card-body">
                                    <p>Use the code below to integrate the tracking system into your website.</p>
                                    
                                    <div class="preview-container">
                                        <?php echo $trackingCode; ?>
                                    </div>
                                    
                                    <ul class="nav nav-tabs" id="codeTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="html-tab" data-toggle="tab" href="#html" role="tab" aria-controls="html" aria-selected="true">HTML</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="php-tab" data-toggle="tab" href="#php" role="tab" aria-controls="php" aria-selected="false">PHP</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="js-tab" data-toggle="tab" href="#js" role="tab" aria-controls="js" aria-selected="false">React/Next.js</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="codeTabsContent">
                                        <div class="tab-pane fade show active" id="html" role="tabpanel" aria-labelledby="html-tab">
                                            <div class="code-container">
                                                <pre><code><?php echo $embedCode; ?></code></pre>
                                                <button class="btn btn-sm btn-primary mt-2 copy-code-btn" data-target="html">
                                                    <i class="fas fa-copy"></i> Copy Code
                                                </button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="php" role="tabpanel" aria-labelledby="php-tab">
                                            <div class="code-container">
                                                <pre><code><?php echo htmlspecialchars($phpSnippet); ?></code></pre>
                                                <button class="btn btn-sm btn-primary mt-2 copy-code-btn" data-target="php">
                                                    <i class="fas fa-copy"></i> Copy Code
                                                </button>
                                            </div>
                                            <p class="mt-2 small">
                                                <i class="fas fa-info-circle"></i> Make sure to include the PHP integration file:
                                                <a href="../integration/php-snippet.php" target="_blank">php-snippet.php</a>
                                            </p>
                                        </div>
                                        <div class="tab-pane fade" id="js" role="tabpanel" aria-labelledby="js-tab">
                                            <div class="code-container">
                                                <pre><code><?php echo htmlspecialchars($jsSnippet); ?></code></pre>
                                                <button class="btn btn-sm btn-primary mt-2 copy-code-btn" data-target="js">
                                                    <i class="fas fa-copy"></i> Copy Code
                                                </button>
                                            </div>
                                            <p class="mt-2 small">
                                                <i class="fas fa-info-circle"></i> Make sure to include the React component:
                                                <a href="../integration/nextjs-component.js" target="_blank">ChuyenDoiTracker.js</a>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i> Add the code at the end of your page, before the closing <code>&lt;/body&gt;</code> tag.
                                    </div>
                                    
                                    <p>
                                        <a href="../button_preview.php?api_key=<?php echo urlencode($apiKey); ?>" target="_blank" class="btn btn-outline-primary btn-block">
                                            <i class="fas fa-eye"></i> Customize Button Appearance
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <footer class="mt-5 mb-3">
                    <div class="text-center">
                        <p class="text-muted">&copy; <?php echo date('Y'); ?> IP Tracking System</p>
                    </div>
                </footer>
            </main>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
    <script>
        // Initialize clipboard.js
        new ClipboardJS('.copy-btn');
        
        // Copy code from tabs
        document.querySelectorAll('.copy-code-btn').forEach(button => {
            button.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                const codeElement = document.querySelector(`#${target} pre code`);
                const textArea = document.createElement('textarea');
                textArea.value = codeElement.textContent;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                // Show tooltip
                this.setAttribute('data-original-title', 'Copied!');
                $(this).tooltip({
                    trigger: 'manual',
                    placement: 'top'
                }).tooltip('show');
                
                setTimeout(() => {
                    $(this).tooltip('hide');
                }, 1000);
            });
        });
        
        // Show tooltip when copying API key
        $('.copy-btn').on('click', function() {
            $(this).tooltip({
                title: 'Copied!',
                trigger: 'manual',
                placement: 'top'
            }).tooltip('show');
            
            setTimeout(() => {
                $(this).tooltip('hide');
            }, 1000);
        });
    </script>
</body>
</html>
