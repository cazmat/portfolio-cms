<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$db = new Database();

// Get slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: index.php');
    exit();
}

// Get project details
$project = $db->fetchOne(
    "SELECT * FROM projects WHERE slug = ? AND status = 'published'",
    [$slug]
);

if (!$project) {
    header('Location: index.php');
    exit();
}

// Get project images
$images = $db->fetchAll(
    "SELECT * FROM project_images WHERE project_id = ? ORDER BY display_order ASC",
    [$project['id']]
);

// Get settings
$settings = [];
$settingsData = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['title']); ?> - <?php echo htmlspecialchars($settings['site_name'] ?? 'My Portfolio'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <?php echo htmlspecialchars($settings['site_name'] ?? 'My Portfolio'); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Project Detail -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="mb-3"><?php echo htmlspecialchars($project['title']); ?></h1>
                    
                    <?php if ($project['category']): ?>
                        <span class="badge bg-secondary mb-3"><?php echo htmlspecialchars($project['category']); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($project['featured_image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($project['featured_image']); ?>" 
                             class="img-fluid mb-4 rounded" 
                             alt="<?php echo htmlspecialchars($project['title']); ?>">
                    <?php endif; ?>
                    
                    <div class="project-description mb-4">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                    </div>
                    
                    <?php if ($project['content']): ?>
                        <div class="project-content mb-4">
                            <h3>Details</h3>
                            <?php echo nl2br(htmlspecialchars($project['content'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($images)): ?>
                        <div class="project-gallery mb-4">
                            <h3>Gallery</h3>
                            <div class="row g-3">
                                <?php foreach ($images as $image): ?>
                                    <div class="col-md-6">
                                        <img src="uploads/<?php echo htmlspecialchars($image['image_path']); ?>" 
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
                            <h5 class="card-title">Project Info</h5>
                            
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
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="index.php" class="btn btn-secondary">← Back to Portfolio</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name'] ?? 'My Portfolio'); ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
