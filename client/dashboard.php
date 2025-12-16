<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
$db = new Database();

// Get user info
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Get projects accessible to this user
if (isAdmin()) {
    // Admins see all projects
    $projects = $db->fetchAll("SELECT * FROM projects ORDER BY display_order ASC, created_at DESC");
} else {
    // Clients only see projects they have access to
    $sql = "SELECT p.*, pa.can_download 
            FROM projects p
            INNER JOIN project_access pa ON p.id = pa.project_id
            WHERE pa.user_id = ? AND pa.can_view = 1
            ORDER BY p.display_order ASC, p.created_at DESC";
    $projects = $db->fetchAll($sql, [$_SESSION['user_id']]);
}

// Get project count
$projectCount = count($projects);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($user['first_name'] ?: $user['username']); ?>!</h1>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Your Projects</h5>
                        <h2 class="mb-0"><?php echo $projectCount; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Account Status</h5>
                        <h2 class="mb-0"><?php echo ucfirst($user['status']); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Last Login</h5>
                        <h2 class="mb-0 fs-6"><?php echo $user['last_login'] ? formatDate($user['last_login']) : 'First time'; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Projects -->
        <div class="row">
            <div class="col-12">
                <h2 class="mb-3">Your Projects</h2>
            </div>
        </div>
        
        <div class="row g-4">
            <?php if (empty($projects)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <h4>No Projects Yet</h4>
                        <p class="mb-0">You don't have access to any projects yet. Please contact your administrator.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <?php if ($project['featured_image']): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($project['featured_image']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($project['title']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <span class="text-white">No Image</span>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                <?php if ($project['category']): ?>
                                    <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($project['category']); ?></span>
                                <?php endif; ?>
                                <p class="card-text"><?php echo htmlspecialchars(substr($project['description'], 0, 100)); ?>...</p>
                                <a href="project-view.php?id=<?php echo $project['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
