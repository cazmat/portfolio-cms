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
session_start();

// Timezone
date_default_timezone_set('America/New_York');
?>
