<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/client/dashboard.php');
        exit();
    }
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isClient() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'client';
}

function isFamily() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'family';
}

function login($username, $password, $db) {
    $sql = "SELECT id, username, email, password, role, status, first_name, last_name FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
    $user = $db->fetchOne($sql, [$username, $username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        // Update last login
        $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        return true;
    }
    return false;
}

function setRememberMe($user_id, $db) {
    // Generate a secure random token
    $token = bin2hex(random_bytes(32));
    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
    
    // Store token in database
    $db->query(
        "INSERT INTO remember_tokens (user_id, token, expiry, created_at) VALUES (?, ?, FROM_UNIXTIME(?), NOW())",
        [$user_id, hash('sha256', $token), $expiry]
    );
    
    // Set cookie (store unhashed token in cookie)
    setcookie('remember_token', $token, $expiry, '/', '', true, true);
}

function checkRememberMe($db) {
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    $token = $_COOKIE['remember_token'];
    $hashedToken = hash('sha256', $token);
    
    // Look up token in database
    $sql = "SELECT rt.user_id, u.username, u.email, u.role, u.first_name, u.last_name, u.status
            FROM remember_tokens rt
            INNER JOIN users u ON rt.user_id = u.id
            WHERE rt.token = ? AND rt.expiry > UNIX_TIMESTAMP() AND u.status = 'active'";
    
    $result = $db->fetchOne($sql, [$hashedToken]);
    
    if ($result) {
        // Valid token - log user in
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['username'] = $result['username'];
        $_SESSION['email'] = $result['email'];
        $_SESSION['role'] = $result['role'];
        $_SESSION['first_name'] = $result['first_name'];
        $_SESSION['last_name'] = $result['last_name'];
        
        // Update last login
        $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$result['user_id']]);
        
        return true;
    }
    
    // Invalid or expired token - remove cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    return false;
}

function clearRememberMe($user_id, $db) {
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $hashedToken = hash('sha256', $token);
        
        // Delete from database
        $db->query("DELETE FROM remember_tokens WHERE user_id = ? AND token = ?", [$user_id, $hashedToken]);
        
        // Delete cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
}

function checkSpamEmail($email, $db) {
    // Check whitelist first - whitelisted emails are always clean
    $whitelisted = $db->fetchOne(
        "SELECT id FROM email_whitelist WHERE email = ?",
        [$email]
    );
    
    if ($whitelisted) {
        return 'clean';
    }
    
    // Check if this email has submitted messages before
    $count = $db->fetchOne(
        "SELECT COUNT(*) as count FROM messages WHERE email = ?",
        [$email]
    );
    
    if ($count && $count['count'] > 0) {
        // Email exists in database - mark as spam
        return 'spam';
    }
    
    // Check for suspicious patterns
    $email = strtolower($email);
    
    // Common spam email patterns
    $spamPatterns = [
        'temp',
        'disposable',
        'throwaway',
        'fake',
        'spam',
        '10minutemail',
        'guerrillamail',
        'mailinator',
        'maildrop',
        'tempmail'
    ];
    
    foreach ($spamPatterns as $pattern) {
        if (strpos($email, $pattern) !== false) {
            return 'suspicious';
        }
    }
    
    return 'clean';
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function logout() {
    // Clear remember me token if exists
    if (isset($_SESSION['user_id'])) {
        global $db;
        if (!isset($db)) {
            require_once __DIR__ . '/database.php';
            $db = new Database();
        }
        clearRememberMe($_SESSION['user_id'], $db);
    }
    
    session_unset();
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php');
    exit();
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

function uploadImage($file, $uploadDir = null) {
    if ($uploadDir === null) {
        $uploadDir = UPLOAD_DIR;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = MAX_FILE_SIZE;
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file.'];
}

function deleteImage($filename) {
    $filepath = UPLOAD_DIR . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function showAlert($message, $type = 'info') {
    $alertClass = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $class = $alertClass[$type] ?? 'alert-info';
    echo "<div class='alert {$class} alert-dismissible fade show' role='alert'>
            {$message}
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
          </div>";
}
?>
