<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

// Redirect to admin dashboard if logged in
if ($isLoggedIn) {
    header('Location: admin/index.php');
    exit;
}

// Redirect to login page
header('Location: admin/login.php');
exit;
