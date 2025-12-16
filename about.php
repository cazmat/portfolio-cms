<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$db = new Database();

// Get settings
$settings = [];
$settingsData = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Get about content
$about = $db->fetchOne("SELECT * FROM about LIMIT 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - <?php echo htmlspecialchars($settings['site_name'] ?? 'My Portfolio'); ?></title>
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
                    <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- About Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="mb-4">About Me</h1>
                    
                    <?php if ($about && $about['profile_image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($about['profile_image']); ?>" 
                             class="img-fluid rounded mb-4" 
                             alt="Profile" 
                             style="max-width: 300px;">
                    <?php endif; ?>
                    
                    <div class="about-content mb-4">
                        <?php echo nl2br(htmlspecialchars($about['content'] ?? 'Welcome to my portfolio.')); ?>
                    </div>
                    
                    <?php if ($about && $about['skills']): ?>
                        <div class="skills-section">
                            <h3>Skills & Expertise</h3>
                            <div class="mb-3">
                                <?php 
                                $skills = explode(',', $about['skills']);
                                foreach ($skills as $skill): ?>
                                    <span class="badge bg-primary me-2 mb-2"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
