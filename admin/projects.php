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
    $project = $db->fetchOne("SELECT featured_image FROM projects WHERE id = ?", [$id]);
    
    if ($project) {
        // Delete featured image
        if ($project['featured_image']) {
            deleteImage($project['featured_image']);
        }
        
        // Delete project images
        $images = $db->fetchAll("SELECT image_path FROM project_images WHERE project_id = ?", [$id]);
        foreach ($images as $image) {
            deleteImage($image['image_path']);
        }
        
        // Delete project
        $db->query("DELETE FROM projects WHERE id = ?", [$id]);
        header('Location: projects.php?msg=deleted');
        exit();
    }
}

// Get all projects
$projects = $db->fetchAll("SELECT * FROM projects ORDER BY display_order ASC, created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Projects</h1>
                    <a href="project-add.php" class="btn btn-primary">Add New Project</a>
                </div>
                
                <?php if (isset($_GET['msg'])): ?>
                    <?php if ($_GET['msg'] === 'deleted'): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            Project deleted successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($projects)): ?>
                            <p class="text-muted">No projects yet. <a href="project-add.php">Add your first project</a>.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Order</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($projects as $project): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($project['featured_image']): ?>
                                                        <img src="../uploads/<?php echo htmlspecialchars($project['featured_image']); ?>" 
                                                             alt="" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-secondary" style="width: 50px; height: 50px;"></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                                <td><?php echo htmlspecialchars($project['category'] ?: '-'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $project['status'] === 'published' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($project['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $project['display_order']; ?></td>
                                                <td><?php echo formatDate($project['created_at']); ?></td>
                                                <td>
                                                    <a href="../project.php?slug=<?php echo htmlspecialchars($project['slug']); ?>" 
                                                       class="btn btn-sm btn-outline-secondary" target="_blank">View</a>
                                                    <a href="project-edit.php?id=<?php echo $project['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">Edit</a>
                                                    <a href="?delete=<?php echo $project['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Are you sure you want to delete this project?')">Delete</a>
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
