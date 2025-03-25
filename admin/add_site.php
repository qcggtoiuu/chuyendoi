<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

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

// Initialize variables
$name = '';
$domain = '';
$phone = '';
$zalo = '';
$messenger = '';
$maps = '';
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
                INSERT INTO sites (name, domain, api_key, phone, zalo, messenger, maps)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param("sssssss", $name, $domain, $apiKey, $phone, $zalo, $messenger, $maps);
            
            if ($stmt->execute()) {
                $siteId = $db->getConnection()->insert_id;
                $success = true;
                
                // Reset form
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
                    
                    <?php if ($success): ?>
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
</body>
</html>
