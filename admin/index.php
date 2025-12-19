<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

// Get statistics
$projectCount = $db->fetchOne("SELECT COUNT(*) as count FROM projects")['count'];
$publishedCount = $db->fetchOne("SELECT COUNT(*) as count FROM projects WHERE status = 'published'")['count'];
$messageCount = $db->fetchOne("SELECT COUNT(*) as count FROM messages WHERE status = 'unread'")['count'];

// Get recent projects
$recentProjects = $db->fetchAll(
    "SELECT id, title, status, created_at FROM projects ORDER BY created_at DESC LIMIT 5"
);

// Get recent messages
$recentMessages = $db->fetchAll(
    "SELECT id, name, email, subject, status, created_at FROM messages ORDER BY created_at DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Projects</h5>
                                <h2 class="mb-0"><?php echo $projectCount; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Published Projects</h5>
                                <h2 class="mb-0"><?php echo $publishedCount; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Unread Messages</h5>
                                <h2 class="mb-0"><?php echo $messageCount; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Projects -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Projects</h5>
                        <a href="projects.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentProjects)): ?>
                            <p class="text-muted">No projects yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentProjects as $project): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $project['status'] === 'published' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($project['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($project['created_at']); ?></td>
                                                <td>
                                                    <a href="project-edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Messages -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Messages</h5>
                        <a href="messages.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentMessages)): ?>
                            <p class="text-muted">No messages yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>From</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentMessages as $message): ?>
                                            <tr class="<?php echo $message['status'] === 'unread' ? 'table-warning' : ''; ?>">
                                                <td><?php echo htmlspecialchars($message['name']); ?></td>
                                                <td><?php echo htmlspecialchars($message['subject'] ?: 'No subject'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $message['status'] === 'unread' ? 'warning' : 'secondary'; ?>">
                                                        <?php echo ucfirst($message['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($message['created_at']); ?></td>
                                                <td>
                                                    <a href="message-view.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
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
