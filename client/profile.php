<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
$db = new Database();

$message = '';
$messageType = '';
$errors = [];

// Get user info
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $company = sanitizeInput($_POST['company'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    
    // Check if email exists for another user
    $existingEmail = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $_SESSION['user_id']]);
    if ($existingEmail) {
        $errors[] = 'This email is already in use by another account.';
    }
    
    // Handle password change
    $updatePassword = false;
    $hashedPassword = '';
    
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'Current password is required to set a new password.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match.';
        } else {
            $updatePassword = true;
            $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
        }
    }
    
    if (empty($errors)) {
        if ($updatePassword) {
            $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, company = ?, phone = ?, password = ?, updated_at = NOW() WHERE id = ?";
            $result = $db->query($sql, [$first_name, $last_name, $email, $company, $phone, $hashedPassword, $_SESSION['user_id']]);
        } else {
            $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, company = ?, phone = ?, updated_at = NOW() WHERE id = ?";
            $result = $db->query($sql, [$first_name, $last_name, $email, $company, $phone, $_SESSION['user_id']]);
        }
        
        if ($result) {
            // Update session
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;
            
            $message = 'Profile updated successfully!';
            $messageType = 'success';
            
            // Refresh user data
            $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        } else {
            $message = 'Failed to update profile.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Client Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">My Profile</h1>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                               value="<?php echo htmlspecialchars($user['first_name']); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                               value="<?php echo htmlspecialchars($user['last_name']); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="company" class="form-label">Company</label>
                                <input type="text" class="form-control" id="company" name="company"
                                       value="<?php echo htmlspecialchars($user['company']); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3">Change Password</h5>
                            <p class="text-muted small">Leave blank to keep current password</p>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Username:</strong><br><?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>Account Type:</strong><br>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </p>
                        <p><strong>Status:</strong><br>
                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </p>
                        <p><strong>Member Since:</strong><br><?php echo formatDate($user['created_at']); ?></p>
                        <p><strong>Last Login:</strong><br><?php echo $user['last_login'] ? formatDate($user['last_login']) : 'First time'; ?></p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Need Help?</h5>
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">If you need assistance or have questions about your projects, please contact your administrator.</p>
                        <a href="../contact.php" class="btn btn-sm btn-outline-primary">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Portfolio CMS. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
