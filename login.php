<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once('includes/class.display.php');

$db = new Database();
$display = new Display;
$error = '';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/index.php');
    } elseif (isFamily()) {
        header('Location: family/dashboard.php');
    } else {
        header('Location: client/dashboard.php');
    }
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif (login($username, $password, $db)) {
        // Set remember me cookie if checked
        if ($remember) {
            setRememberMe($_SESSION['user_id'], $db);
        }
        
        // Redirect based on role
        if (isAdmin()) {
            header('Location: admin/index.php');
        } elseif (isFamily()) {
            header('Location: family/dashboard.php');
        } else {
            header('Location: client/dashboard.php');
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
    <title>Login - Portfolio CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class='portfolio-container'>

    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Portfolio CMS Login</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['timeout'])): ?>
                            <div class="alert alert-warning">Your session has expired due to inactivity. Please login again.</div>
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
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Remember me for 30 days
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="text-muted small">← Back to Portfolio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
  $display->output(false);
?>
