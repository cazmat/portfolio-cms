<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

$message = '';
$messageType = '';

// Get current settings
$settings = [];
$settingsData = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [
        'site_name' => sanitizeInput($_POST['site_name'] ?? ''),
        'site_tagline' => sanitizeInput($_POST['site_tagline'] ?? ''),
        'contact_email' => sanitizeInput($_POST['contact_email'] ?? ''),
        'social_linkedin' => sanitizeInput($_POST['social_linkedin'] ?? ''),
        'social_github' => sanitizeInput($_POST['social_github'] ?? ''),
        'social_twitter' => sanitizeInput($_POST['social_twitter'] ?? ''),
        'items_per_page' => sanitizeInput($_POST['items_per_page'] ?? '9')
    ];
    
    $success = true;
    foreach ($updates as $key => $value) {
        $sql = "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?";
        if (!$db->query($sql, [$value, $key])) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        $message = 'Settings updated successfully!';
        $messageType = 'success';
        $settings = $updates; // Update local settings
    } else {
        $message = 'Failed to update settings.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Site Settings</h1>
                </div>
                
                <?php if ($message): ?>
                    <?php showAlert($message, $messageType); ?>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <h5 class="mb-3">General Settings</h5>
                            
                            <div class="mb-3">
                                <label for="site_name" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                       value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="site_tagline" class="form-label">Site Tagline</label>
                                <input type="text" class="form-control" id="site_tagline" name="site_tagline" 
                                       value="<?php echo htmlspecialchars($settings['site_tagline'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                       value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3">Social Media Links</h5>
                            
                            <div class="mb-3">
                                <label for="social_linkedin" class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control" id="social_linkedin" name="social_linkedin" 
                                       value="<?php echo htmlspecialchars($settings['social_linkedin'] ?? ''); ?>"
                                       placeholder="https://linkedin.com/in/yourprofile">
                            </div>
                            
                            <div class="mb-3">
                                <label for="social_github" class="form-label">GitHub URL</label>
                                <input type="url" class="form-control" id="social_github" name="social_github" 
                                       value="<?php echo htmlspecialchars($settings['social_github'] ?? ''); ?>"
                                       placeholder="https://github.com/yourusername">
                            </div>
                            
                            <div class="mb-3">
                                <label for="social_twitter" class="form-label">Twitter URL</label>
                                <input type="url" class="form-control" id="social_twitter" name="social_twitter" 
                                       value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>"
                                       placeholder="https://twitter.com/yourusername">
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3">Display Settings</h5>
                            
                            <div class="mb-3">
                                <label for="items_per_page" class="form-label">Items Per Page</label>
                                <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                                       value="<?php echo htmlspecialchars($settings['items_per_page'] ?? '9'); ?>"
                                       min="1" max="100">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
