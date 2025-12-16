<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

$id = (int)($_GET['id'] ?? 0);
$errors = [];
$success = false;

if ($id === 0) {
    header('Location: users.php');
    exit();
}

// Get user
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);

if (!$user) {
    header('Location: users.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $role = sanitizeInput($_POST['role'] ?? 'client');
    $status = sanitizeInput($_POST['status'] ?? 'active');
    $company = sanitizeInput($_POST['company'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    
    // Check if username exists for another user
    $existingUser = $db->fetchOne("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
    if ($existingUser) {
        $errors[] = 'Username already exists.';
    }
    
    // Check if email exists for another user
    $existingEmail = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
    if ($existingEmail) {
        $errors[] = 'Email already exists.';
    }
    
    // Handle password change
    $updatePassword = false;
    $hashedPassword = '';
    
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        } else {
            $updatePassword = true;
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        }
    }
    
    if (empty($errors)) {
        if ($updatePassword) {
            $sql = "UPDATE users SET username = ?, email = ?, password = ?, first_name = ?, last_name = ?, role = ?, status = ?, company = ?, phone = ?, notes = ?, updated_at = NOW() WHERE id = ?";
            $result = $db->query($sql, [$username, $email, $hashedPassword, $first_name, $last_name, $role, $status, $company, $phone, $notes, $id]);
        } else {
            $sql = "UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, status = ?, company = ?, phone = ?, notes = ?, updated_at = NOW() WHERE id = ?";
            $result = $db->query($sql, [$username, $email, $first_name, $last_name, $role, $status, $company, $phone, $notes, $id]);
        }
        
        if ($result) {
            $success = true;
            // Refresh user data
            $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        } else {
            $errors[] = 'Failed to update user.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit User</h1>
                    <a href="users.php" class="btn btn-secondary">Back to Users</a>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        User updated successfully!
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
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Account Information</h5>
                                    
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" class="form-control" id="username" name="username" required 
                                               value="<?php echo htmlspecialchars($user['username']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" required 
                                               value="<?php echo htmlspecialchars($user['email']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" minlength="6">
                                        <small class="text-muted">Leave blank to keep current password. Minimum 6 characters if changing.</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role *</label>
                                        <select class="form-select" id="role" name="role">
                                            <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status *</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            <option value="pending" <?php echo $user['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5 class="mb-3">Personal Information</h5>
                                    
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                               value="<?php echo htmlspecialchars($user['first_name']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                               value="<?php echo htmlspecialchars($user['last_name']); ?>">
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
                                    
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="4"><?php echo htmlspecialchars($user['notes']); ?></textarea>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <small><strong>Last Login:</strong> <?php echo $user['last_login'] ? formatDate($user['last_login']) : 'Never'; ?></small><br>
                                        <small><strong>Created:</strong> <?php echo formatDate($user['created_at']); ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Update User</button>
                                <a href="users.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
