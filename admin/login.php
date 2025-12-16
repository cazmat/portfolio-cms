<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = new Database();
$error = '';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: index.php');
    } else {
        header('Location: ../client/dashboard.php');
    }
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif (login($username, $password, $db)) {
        // Redirect based on role
        if (isAdmin()) {
            header('Location: index.php');
        } else {
            header('Location: ../client/dashboard.php');
        }
        exit();
    } else {
        $error = 'Invalid username or password, or account is inactive.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Portfolio CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Admin Login</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username or Email</label>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-muted small">← Back to Portfolio</a>
                        </div>
                        
                        <div class="alert alert-info mt-4 small">
                            <strong>Default credentials:</strong><br>
                            Username: admin<br>
                            Password: admin123<br>
                            <em>Please change these after first login!</em>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
