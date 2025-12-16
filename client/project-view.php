<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
$db = new Database();

$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: dashboard.php');
    exit();
}

// Get project and check access
if (isAdmin()) {
    $project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$id]);
    $canDownload = true;
} else {
    $sql = "SELECT p.*, pa.can_download 
            FROM projects p
            INNER JOIN project_access pa ON p.id = pa.project_id
            WHERE p.id = ? AND pa.user_id = ? AND pa.can_view = 1";
    $project = $db->fetchOne($sql, [$id, $_SESSION['user_id']]);
    $canDownload = $project['can_download'] ?? false;
}

if (!$project) {
    header('Location: dashboard.php');
    exit();
}

// Get project images
$images = $db->fetchAll(
    "SELECT * FROM project_images WHERE project_id = ? ORDER BY display_order ASC",
    [$project['id']]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['title']); ?> - Client Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <h1 class="mb-3"><?php echo htmlspecialchars($project['title']); ?></h1>
                
                <?php if ($project['category']): ?>
                    <span class="badge bg-secondary mb-3"><?php echo htmlspecialchars($project['category']); ?></span>
                <?php endif; ?>
                
                <?php if ($project['featured_image']): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($project['featured_image']); ?>" 
                         class="img-fluid mb-4 rounded" 
                         alt="<?php echo htmlspecialchars($project['title']); ?>">
                <?php endif; ?>
                
                <?php if ($project['description']): ?>
                    <div class="mb-4">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($project['content']): ?>
                    <div class="mb-4">
                        <h3>Details</h3>
                        <?php echo nl2br(htmlspecialchars($project['content'])); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($images)): ?>
                    <div class="mb-4">
                        <h3>Gallery</h3>
                        <div class="row g-3">
                            <?php foreach ($images as $image): ?>
                                <div class="col-md-6">
                                    <img src="../uploads/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         class="img-fluid rounded" 
                                         alt="<?php echo htmlspecialchars($image['caption'] ?? ''); ?>">
                                    <?php if ($image['caption']): ?>
                                        <p class="text-muted small mt-2"><?php echo htmlspecialchars($image['caption']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Project Information</h5>
                        
                        <?php if ($project['client']): ?>
                            <p><strong>Client:</strong><br><?php echo htmlspecialchars($project['client']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($project['date_completed']): ?>
                            <p><strong>Completed:</strong><br><?php echo formatDate($project['date_completed']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($project['tags']): ?>
                            <p><strong>Tags:</strong><br>
                                <?php 
                                $tags = explode(',', $project['tags']);
                                foreach ($tags as $tag): ?>
                                    <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($project['project_url']): ?>
                            <p><strong>Website:</strong><br>
                                <a href="<?php echo htmlspecialchars($project['project_url']); ?>" 
                                   target="_blank" 
                                   class="btn btn-primary btn-sm">Visit Project</a>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($canDownload): ?>
                            <hr>
                            <div class="alert alert-info">
                                <small><strong>Download Access:</strong> You have permission to download project files.</small>
                            </div>
                        <?php endif; ?>
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
