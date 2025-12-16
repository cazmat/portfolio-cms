<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$db = new Database();
$message = '';
$messageType = '';

// Get settings
$settings = [];
$settingsData = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $messageText = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($messageText)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $sql = "INSERT INTO messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())";
        if ($db->query($sql, [$name, $email, $subject, $messageText])) {
            $message = 'Thank you for your message! We will get back to you soon.';
            $messageType = 'success';
        } else {
            $message = 'There was an error sending your message. Please try again.';
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
    <title>Contact - <?php echo htmlspecialchars($settings['site_name'] ?? 'My Portfolio'); ?></title>
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
                    <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="mb-4">Get In Touch</h1>
                    
                    <?php if ($message): ?>
                        <?php showAlert($message, $messageType); ?>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>
                    
                    <?php if (!empty($settings['contact_email'])): ?>
                        <div class="mt-4">
                            <p>You can also reach me directly at: 
                                <a href="mailto:<?php echo htmlspecialchars($settings['contact_email']); ?>">
                                    <?php echo htmlspecialchars($settings['contact_email']); ?>
                                </a>
                            </p>
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
