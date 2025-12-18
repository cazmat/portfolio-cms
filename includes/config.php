<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'portfolio_cms');

// Site Configuration
define('SITE_URL', 'http://localhost/portfolio-cms');
define('ADMIN_EMAIL', 'admin@example.com');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes

// Session Configuration
// Set session lifetime to 8 hours (28800 seconds)
ini_set('session.gc_maxlifetime', 28800);
ini_set('session.cookie_lifetime', 28800);
session_set_cookie_params(28800);

session_start();

// Check for Remember Me cookie before session timeout check
if (!isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/database.php';
    $db = new Database();
    require_once __DIR__ . '/functions.php';
    checkRememberMe($db);
}

// Session timeout constant (8 hours in seconds)
define('SESSION_TIMEOUT', 28800);

// Check for session timeout on protected pages
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    // Session expired
    session_unset();
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php?timeout=1');
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Timezone
date_default_timezone_set('America/New_York');
?>
