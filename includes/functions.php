<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit();
    }
}

function login($username, $password, $db) {
    $sql = "SELECT id, username, email, password FROM users WHERE username = ? OR email = ?";
    $user = $db->fetchOne($sql, [$username, $username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        return true;
    }
    return false;
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: ' . SITE_URL . '/admin/login.php');
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
