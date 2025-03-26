<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Start session
session_start();

// Clear any existing session data for testing
if (isset($_GET['logout'])) {
    // Destroy the session
    session_unset();
    session_destroy();
    
    // Redirect to the test page
    header('Location: test_login_redirect.php');
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Test Login Redirect</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Test Login Redirect</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p><strong>Status:</strong> Not logged in</p>
                        <p>You will be redirected to the login page. After successful login, you should be redirected back to this page.</p>
                    </div>
                    <p>Redirecting to login page in 3 seconds...</p>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Animate progress bar
            let width = 0;
            const interval = setInterval(function() {
                width += 3;
                $(".progress-bar").css("width", width + "%");
                if (width >= 100) {
                    clearInterval(interval);
                    // Redirect to admin page which will then redirect to login
                    window.location.href = "admin/edit_site.php?id=4";
                }
            }, 100);
        </script>
    </body>
    </html>';
    exit;
} else {
    // User is logged in, show success message
    echo '<!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Test Login Redirect - Success</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4>Test Login Redirect - Success</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <p><strong>Status:</strong> Logged in successfully</p>
                        <p>You have been successfully redirected back to this page after login.</p>
                        <p>Username: ' . htmlspecialchars($_SESSION['username']) . '</p>
                    </div>
                    <p>You can <a href="test_login_redirect.php?logout=1" class="btn btn-warning">Logout</a> to test again.</p>
                    <p>Or go to <a href="admin/index.php" class="btn btn-primary">Admin Dashboard</a></p>
                </div>
            </div>
        </div>
    </body>
    </html>';
    exit;
}
?>
