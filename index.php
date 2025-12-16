<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once('includes/class.display.php');

$db = new Database();
$display = new Display;

// Get site settings
$settings = [];
$settingsData = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Get published projects
$projects = $db->fetchAll(
    "SELECT * FROM projects WHERE status = 'published' ORDER BY display_order ASC, created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name'] ?? 'My Portfolio'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
  <body>
    <div class='portfolio-container'>

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
                    <li class="nav-item"><a class="nav-link active" href="index.php">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero bg-primary text-white text-center py-5">
        <div class="container">
            <h1 class="display-4"><?php echo htmlspecialchars($settings['site_name'] ?? 'My Portfolio'); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($settings['site_tagline'] ?? 'Creative Professional'); ?></p>
        </div>
    </section>

    <!-- Portfolio Grid -->
    <section class="py-5 page-fill">
        <div class="container">
            <h2 class="text-center mb-5">My Work</h2>
            <div class="row g-4">
                <?php if (empty($projects)): ?>
                    <div class="col-12">
                        <p class="text-center text-muted">No projects yet. Check back soon!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm">
                                <?php if ($project['featured_image']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($project['featured_image']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($project['title']); ?>"
                                         style="height: 250px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                                         style="height: 250px;">
                                        <span class="text-white">No Image</span>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                    <?php if ($project['category']): ?>
                                        <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($project['category']); ?></span>
                                    <?php endif; ?>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($project['description'], 0, 100)); ?>...</p>
                                    <a href="project.php?slug=<?php echo htmlspecialchars($project['slug']); ?>" 
                                       class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name'] ?? 'My Portfolio'); ?>. All rights reserved.</p>
            <div class="social-links mt-3">
                <?php if (!empty($settings['social_linkedin'])): ?>
                    <a href="<?php echo htmlspecialchars($settings['social_linkedin']); ?>" class="text-white me-3">LinkedIn</a>
                <?php endif; ?>
                <?php if (!empty($settings['social_github'])): ?>
                    <a href="<?php echo htmlspecialchars($settings['social_github']); ?>" class="text-white me-3">GitHub</a>
                <?php endif; ?>
                <?php if (!empty($settings['social_twitter'])): ?>
                    <a href="<?php echo htmlspecialchars($settings['social_twitter']); ?>" class="text-white">Twitter</a>
                <?php endif; ?>
            </div>
        </div>
    </footer>
<?php
  $display->output();
?>
