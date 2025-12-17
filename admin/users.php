<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Prevent deleting yourself
    if ($id !== $_SESSION['user_id']) {
        $db->query("DELETE FROM users WHERE id = ? AND role IN ('client', 'family')", [$id]);
        header('Location: users.php?msg=deleted');
        exit();
    }
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $user = $db->fetchOne("SELECT status FROM users WHERE id = ?", [$id]);
    if ($user) {
        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        $db->query("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);
        header('Location: users.php?msg=status_updated');
        exit();
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT * FROM users";
$params = [];

if ($filter === 'admin') {
    $sql .= " WHERE role = 'admin'";
} elseif ($filter === 'client') {
    $sql .= " WHERE role = 'client'";
} elseif ($filter === 'family') {
    $sql .= " WHERE role = 'family'";
} elseif ($filter === 'active') {
    $sql .= " WHERE status = 'active'";
} elseif ($filter === 'inactive') {
    $sql .= " WHERE status = 'inactive'";
}

$sql .= " ORDER BY created_at DESC";
$users = $db->fetchAll($sql, $params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">User Management</h1>
                    <a href="user-add.php" class="btn btn-primary">Add New User</a>
                </div>
                
                <?php if (isset($_GET['msg'])): ?>
                    <?php if ($_GET['msg'] === 'deleted'): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            User deleted successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($_GET['msg'] === 'status_updated'): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            User status updated successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Filter Buttons -->
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <a href="?filter=all" class="btn btn-outline-primary <?php echo $filter === 'all' ? 'active' : ''; ?>">All Users</a>
                        <a href="?filter=admin" class="btn btn-outline-primary <?php echo $filter === 'admin' ? 'active' : ''; ?>">Admins</a>
                        <a href="?filter=client" class="btn btn-outline-primary <?php echo $filter === 'client' ? 'active' : ''; ?>">Clients</a>
                        <a href="?filter=family" class="btn btn-outline-primary <?php echo $filter === 'family' ? 'active' : ''; ?>">Family</a>
                        <a href="?filter=active" class="btn btn-outline-success <?php echo $filter === 'active' ? 'active' : ''; ?>">Active</a>
                        <a href="?filter=inactive" class="btn btn-outline-secondary <?php echo $filter === 'inactive' ? 'active' : ''; ?>">Inactive</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <p class="text-muted">No users found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Company</th>
                                            <th>Last Login</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $user['role'] === 'admin' ? 'danger' : 
                                                            ($user['role'] === 'family' ? 'info' : 'primary'); 
                                                    ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['company'] ?: '-'); ?></td>
                                                <td><?php echo $user['last_login'] ? formatDate($user['last_login']) : 'Never'; ?></td>
                                                <td>
                                                    <a href="user-edit.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">Edit</a>
                                                    
                                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                        <a href="?toggle_status=<?php echo $user['id']; ?>" 
                                                           class="btn btn-sm btn-outline-warning">
                                                            <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                        </a>
                                                        
                                                        <?php if ($user['role'] === 'client'): ?>
                                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($user['role'] === 'family'): ?>
                                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this family member?')">Delete</a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
