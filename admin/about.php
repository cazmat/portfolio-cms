<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

$message = '';
$messageType = '';

// Get current about content
$about = $db->fetchOne("SELECT * FROM about LIMIT 1");

if (!$about) {
    // Create default entry if none exists
    $db->query("INSERT INTO about (content, skills) VALUES ('', '')");
    $about = $db->fetchOne("SELECT * FROM about LIMIT 1");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = sanitizeInput($_POST['content'] ?? '');
    $skills = sanitizeInput($_POST['skills'] ?? '');
    $currentImage = $about['profile_image'];
    
    // Handle profile image upload
    $profile_image = $currentImage;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['profile_image']);
        if ($uploadResult['success']) {
            // Delete old image
            if ($currentImage) {
                deleteImage($currentImage);
            }
            $profile_image = $uploadResult['filename'];
        } else {
            $message = $uploadResult['message'];
            $messageType = 'error';
        }
    }
    
    if (empty($message)) {
        $sql = "UPDATE about SET content = ?, profile_image = ?, skills = ?, updated_at = NOW() WHERE id = ?";
        if ($db->query($sql, [$content, $profile_image, $skills, $about['id']])) {
            $message = 'About page updated successfully!';
            $messageType = 'success';
            // Refresh data
            $about = $db->fetchOne("SELECT * FROM about LIMIT 1");
        } else {
            $message = 'Failed to update about page.';
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
    <title>About Page - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">About Page</h1>
                    <a href="../about.php" target="_blank" class="btn btn-secondary">Preview</a>
                </div>
                
                <?php if ($message): ?>
                    <?php showAlert($message, $messageType); ?>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Profile Image</label>
                                <?php if ($about['profile_image']): ?>
                                    <div class="mb-2">
                                        <img src="../uploads/<?php echo htmlspecialchars($about['profile_image']); ?>" 
                                             alt="Current profile" 
                                             class="img-thumbnail" 
                                             style="max-width: 200px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">About Content</label>
                                <textarea class="form-control" id="content" name="content" rows="10"><?php echo htmlspecialchars($about['content'] ?? ''); ?></textarea>
                                <small class="text-muted">Write your bio or about section here</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="skills" class="form-label">Skills & Expertise</label>
                                <input type="text" class="form-control" id="skills" name="skills" 
                                       value="<?php echo htmlspecialchars($about['skills'] ?? ''); ?>">
                                <small class="text-muted">Comma-separated list (e.g., Web Design, Photography, Branding)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update About Page</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
