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

// Check if user is logged in and redirect if not
requireLogin();

// Get database instance
$db = Database::getInstance();

// Get user information
$userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Initialize variables
$name = '';
$domain = '';
$phone = '';
$zalo = '';
$messenger = '';
$maps = '';
$success = false;
$error = '';

// Initialize variables for embedding code
$newSiteApiKey = '';
$newSitePhone = '';
$newSiteZalo = '';
$newSiteMessenger = '';
$newSiteMaps = '';
$showEmbedCode = false;
$embedCode = '';
$phpSnippet = '';
$jsSnippet = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $domain = isset($_POST['domain']) ? sanitizeInput($_POST['domain']) : '';
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $zalo = isset($_POST['zalo']) ? sanitizeInput($_POST['zalo']) : '';
    $messenger = isset($_POST['messenger']) ? sanitizeInput($_POST['messenger']) : '';
    $maps = isset($_POST['maps']) ? sanitizeInput($_POST['maps']) : '';
    $buttonStyle = isset($_POST['button_style']) ? sanitizeInput($_POST['button_style']) : 'fab';
    $showButtons = isset($_POST['show_buttons']) ? 1 : 0;
    
    // Validate form data
    if (empty($name)) {
        $error = 'Website name is required';
    } else if (empty($domain)) {
        $error = 'Domain is required';
    } else {
        // Check if domain already exists
        $stmt = $db->prepare("SELECT id FROM sites WHERE domain = ?");
        $stmt->bind_param("s", $domain);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Domain already exists';
        } else {
            // Generate API key
            $apiKey = generateApiKey();
            
            // Insert site into database
            $stmt = $db->prepare("
                INSERT INTO sites (name, domain, api_key, phone, zalo, messenger, maps, button_style, show_buttons)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param("ssssssssi", $name, $domain, $apiKey, $phone, $zalo, $messenger, $maps, $buttonStyle, $showButtons);
            
            if ($stmt->execute()) {
                $siteId = $db->getConnection()->insert_id;
                $success = true;
                
                // Save the API key and contact info for embedding code
                $newSiteApiKey = $apiKey;
                $newSitePhone = $phone;
                $newSiteZalo = $zalo;
                $newSiteMessenger = $messenger;
                $newSiteMaps = $maps;
                $showEmbedCode = true;
                
                // Reset form for next submission
                $name = '';
                $domain = '';
                $phone = '';
                $zalo = '';
                $messenger = '';
                $maps = '';
            } else {
                $error = 'Error adding site: ' . $db->error();
            }
        }
    }
}

// Generate embedding code if a site was just added
if ($showEmbedCode) {
    // Generate button options without default values if empty
    $buttonOptions = [
        'style' => $buttonStyle,
        'phone' => !empty($newSitePhone) ? $newSitePhone : '',
        'zalo' => !empty($newSiteZalo) ? $newSiteZalo : '',
        'messenger' => !empty($newSiteMessenger) ? $newSiteMessenger : '',
        'maps' => !empty($newSiteMaps) ? $newSiteMaps : '',
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
    $trackingCode = generateTrackingCode($newSiteApiKey, $buttonOptions, $scriptOptions);
    
    // Get embed code
    $embedCode = htmlspecialchars($trackingCode);
    
    // Get provided values for code snippets (no defaults)
    $phoneValue = !empty($newSitePhone) ? $newSitePhone : '';
    $zaloValue = !empty($newSiteZalo) ? $newSiteZalo : '';
    $messengerValue = !empty($newSiteMessenger) ? $newSiteMessenger : '';
    $mapsValue = !empty($newSiteMaps) ? $newSiteMaps : '';
    
    // Generate simple embed code - no need to include contact info as it will be fetched from API
    $embedCode = '<script src="https://chuyendoi.io.vn/assets/js/chuyendoi-track.js" data-api-key="' . $newSiteApiKey . '"></script>';
    
    // Generate PHP code snippet
    $phpSnippet = "<?php\n";
    $phpSnippet .= "// Add this code at the end of your page, before the closing </body> tag\n";
    $phpSnippet .= "?>\n";
    $phpSnippet .= '<script src="https://chuyendoi.io.vn/assets/js/chuyendoi-track.js" data-api-key="<?php echo \'' . $newSiteApiKey . '\'; ?>"></script>';
    
    // Generate JavaScript code snippet
    $jsSnippet = "// Add this code at the end of your page, before the closing </body> tag\n";
    $jsSnippet .= '<script src="https://chuyendoi.io.vn/assets/js/chuyendoi-track.js" data-api-key="' . $newSiteApiKey . '"></script>';
    
    // Note about the updated embed code
    $embedNote = '<div class="alert alert-info mt-3">
        <i class="fas fa-info-circle"></i> <strong>New Feature:</strong> Contact information is now automatically fetched from your site settings. 
        When you update your contact details here, the buttons on your website will update automatically without changing the embed code.
    </div>';
}

// Page title
$pageTitle = 'Add Website';
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
                            <li class="nav-item active">
                                <a class="nav-link" href="add_site.php">Add Website</a>
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
                    <h1 class="h2 mb-4"><?php echo $pageTitle; ?></h1>
                    
                    <?php if ($success && $showEmbedCode): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> Website has been added successfully. The embedding code is available below.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Integration Code</h5>
                                </div>
                                <div class="card-body">
                                    <p>Use the code below to integrate the tracking system into your website.</p>
                                    
                                    <div class="preview-container">
                                        <div style="padding: 20px;">
                                            <p><strong>Preview of Conversion Buttons:</strong></p>
                                            <p>The buttons will appear on your website as shown below:</p>
                                            <?php 
                                            // Generate button HTML only for preview
                                            echo generateButtonHtml($buttonOptions); 
                                            ?>
                                        </div>
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
                                        <li class="nav-item">
                                            <a class="nav-link" id="vite-tab" data-toggle="tab" href="#vite" role="tab" aria-controls="vite" aria-selected="false">Vite + React + TS</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="codeTabsContent">
                                        <div class="tab-pane fade show active" id="html" role="tabpanel" aria-labelledby="html-tab">
                                            <div class="code-container">
                                                <pre><code><?php echo htmlspecialchars($embedCode); ?></code></pre>
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
                                        <div class="tab-pane fade" id="vite" role="tabpanel" aria-labelledby="vite-tab">
                                            <div class="code-container">
                                                <pre><code>// In your Vite + React + TypeScript project
// src/components/ChuyenDoiTracker.tsx

import { useEffect, useState, FC } from 'react';

interface ChuyenDoiTrackerProps {
  apiKey: string;
  style?: 'fab' | 'bar';
  phone?: string;
  zalo?: string;
  messenger?: string;
  maps?: string;
  showLabels?: boolean;
  primaryColor?: string;
  animation?: boolean;
  debug?: boolean;
}

interface Tracker {
  init: (options: {
    apiKey: string;
    apiUrl: string;
    buttonSelector: string;
    debug: boolean;
  }) => void;
  shouldHideButtons: () => boolean;
  trackClick: (element: HTMLElement) => void;
  trackEvent: (eventName: string, eventData: any) => void;
  isBot: () => boolean;
}

declare global {
  interface Window {
    Tracker?: Tracker;
  }
}

const ChuyenDoiTracker: FC<ChuyenDoiTrackerProps> = ({
  apiKey,
  style,
  phone,
  zalo,
  messenger,
  maps,
  showLabels,
  primaryColor,
  animation,
  debug
}) => {
  const [isLoaded, setIsLoaded] = useState<boolean>(false);
  const [hideButtons, setHideButtons] = useState<boolean>(false);
  const [embedCode, setEmbedCode] = useState<string>('');
  
  useEffect(() => {
    // Load tracking script
    const script = document.createElement('script');
    script.src = 'https://chuyendoi.io.vn/assets/js/tracker.js';
    script.async = true;
    script.onload = function() {
      setIsLoaded(true);
      
      // Initialize tracker
      if (typeof window.Tracker !== 'undefined') {
        window.Tracker.init({
          apiKey: apiKey,
          apiUrl: 'https://chuyendoi.io.vn/api/track.php',
          buttonSelector: '.fab-wrapper, .bbas-pc-contact-bar',
          debug: debug || false
        });
        
        // Check if buttons should be hidden
        setHideButtons(window.Tracker.shouldHideButtons());
      }
    };
    document.head.appendChild(script);
    
    // Load CSS
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://chuyendoi.io.vn/assets/css/buttons.css';
    document.head.appendChild(link);
    
    // Fetch embed code
    fetch(`https://chuyendoi.io.vn/button_preview.php?api_key=${apiKey}&style=${style || 'fab'}&phone=${encodeURIComponent(phone || '')}&zalo=${encodeURIComponent(zalo || '')}&messenger=${encodeURIComponent(messenger || '')}&maps=${encodeURIComponent(maps || '')}&show_labels=${showLabels ? '1' : '0'}&primary_color=${encodeURIComponent(primaryColor || '#3961AA')}&animation=${animation ? '1' : '0'}`)
      .then(response => response.text())
      .then(html => {
        // Extract the button HTML from the response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const previewContainer = doc.querySelector('.preview-container');
        
        if (previewContainer) {
          setEmbedCode(previewContainer.innerHTML);
        }
      })
      .catch(error => {
        console.error('Error fetching embed code:', error);
      });
    
    return () => {
      // Cleanup
      document.head.removeChild(script);
      document.head.removeChild(link);
    };
  }, [apiKey, style, phone, zalo, messenger, maps, showLabels, primaryColor, animation, debug]);
  
  // Don't render anything if buttons should be hidden
  if (hideButtons) {
    return null;
  }
  
  // Don't render anything if not loaded yet
  if (!isLoaded || !embedCode) {
    return null;
  }
  
  return (
    <div dangerouslySetInnerHTML={{ __html: embedCode }} />
  );
};

export default ChuyenDoiTracker;

// Usage in your app:
// import ChuyenDoiTracker from './components/ChuyenDoiTracker';
//
// function App() {
//   return (
//     <div className="App">
//       <ChuyenDoiTracker
//         apiKey="<?php echo $newSiteApiKey; ?>"
//         phone="<?php echo $phoneValue; ?>"
//         zalo="<?php echo $zaloValue; ?>"
//         messenger="<?php echo $messengerValue; ?>"
//         maps="<?php echo $mapsValue; ?>"
//       />
//     </div>
//   );
// }
</code></pre>
                                                <button class="btn btn-sm btn-primary mt-2 copy-code-btn" data-target="vite">
                                                    <i class="fas fa-copy"></i> Copy Code
                                                </button>
                                            </div>
                                            <p class="mt-2 small">
                                                <i class="fas fa-info-circle"></i> Make sure to include the Vite + React + TypeScript component:
                                                <a href="../integration/vite-react-typescript-component.tsx" target="_blank">ChuyenDoiTracker.tsx</a>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <?php echo $embedNote; ?>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i> Add the code at the end of your page, before the closing <code>&lt;/body&gt;</code> tag.
                                    </div>
                                    
                                    <p>
                                        <a href="../button_preview.php?api_key=<?php echo urlencode($newSiteApiKey); ?>" target="_blank" class="btn btn-outline-primary btn-block">
                                            <i class="fas fa-eye"></i> Customize Button Appearance
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> Website has been added successfully.
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
                                            <label for="button_style">Button Style</label>
                                            <select class="form-control" id="button_style" name="button_style">
                                                <option value="fab" selected>Floating Action Button</option>
                                                <option value="bar">Contact Bar</option>
                                                <option value="sticky-right">Sticky Right (Zalo & Call)</option>
                                            </select>
                                            <small class="form-text text-muted">Choose the style of the conversion buttons</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="show_buttons" name="show_buttons" value="1" checked>
                                                <label class="custom-control-label" for="show_buttons">Show conversion buttons</label>
                                                <small class="form-text text-muted">Enable or disable the display of conversion buttons on the website</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Add Website</button>
                                            <a href="sites.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Instructions</h5>
                                </div>
                                <div class="card-body">
                                    <p>After adding a website, you will receive an API key that you can use to integrate the tracking system into your website.</p>
                                    
                                    <p>You can use the Button Preview tool to customize the appearance of the interactive buttons and generate the embed code.</p>
                                    
                                    <p>The contact information you provide here will be used as default values for the interactive buttons, but you can override them when generating the embed code.</p>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> The tracking system will automatically detect bots and hide the interactive buttons from them.
                                    </div>
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
    </script>
</body>
</html>
