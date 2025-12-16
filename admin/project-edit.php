<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
$db = new Database();

$id = (int)($_GET['id'] ?? 0);
$errors = [];
$success = false;

if ($id === 0) {
    header('Location: projects.php');
    exit();
}

// Get project
$project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$id]);

if (!$project) {
    header('Location: projects.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $slug = sanitizeInput($_POST['slug'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $content = sanitizeInput($_POST['content'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $tags = sanitizeInput($_POST['tags'] ?? '');
    $project_url = sanitizeInput($_POST['project_url'] ?? '');
    $client = sanitizeInput($_POST['client'] ?? '');
    $date_completed = sanitizeInput($_POST['date_completed'] ?? '');
    $status = sanitizeInput($_POST['status'] ?? 'draft');
    $display_order = (int)($_POST['display_order'] ?? 0);
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    
    // Generate slug if empty
    if (empty($slug)) {
        $slug = generateSlug($title);
    } else {
        $slug = generateSlug($slug);
    }
    
    // Check if slug exists (excluding current project)
    $existingSlug = $db->fetchOne("SELECT id FROM projects WHERE slug = ? AND id != ?", [$slug, $id]);
    if ($existingSlug) {
        $errors[] = 'This slug already exists. Please choose a different one.';
    }
    
    if (empty($errors)) {
        // Handle featured image upload
        $featured_image = $project['featured_image'];
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['featured_image']);
            if ($uploadResult['success']) {
                // Delete old image
                if ($featured_image) {
                    deleteImage($featured_image);
                }
                $featured_image = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['message'];
            }
        }
        
        if (empty($errors)) {
            $sql = "UPDATE projects SET 
                    title = ?, slug = ?, description = ?, content = ?, featured_image = ?, 
                    category = ?, tags = ?, project_url = ?, client = ?, date_completed = ?, 
                    status = ?, display_order = ?, updated_at = NOW()
                    WHERE id = ?";
            
            if ($db->query($sql, [$title, $slug, $description, $content, $featured_image, $category, $tags, $project_url, $client, $date_completed, $status, $display_order, $id])) {
                $success = true;
                // Refresh project data
                $project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$id]);
            } else {
                $errors[] = 'Failed to update project.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Project</h1>
                    <div>
                        <a href="../project.php?slug=<?php echo htmlspecialchars($project['slug']); ?>" 
                           class="btn btn-secondary" target="_blank">Preview</a>
                        <a href="projects.php" class="btn btn-secondary">Back to Projects</a>
                    </div>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Project updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required 
                                               value="<?php echo htmlspecialchars($project['title']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug (URL-friendly name)</label>
                                        <input type="text" class="form-control" id="slug" name="slug"
                                               value="<?php echo htmlspecialchars($project['slug']); ?>">
                                        <small class="text-muted">Leave empty to auto-generate from title</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Short Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($project['description']); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Full Content</label>
                                        <textarea class="form-control" id="content" name="content" rows="10"><?php echo htmlspecialchars($project['content']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">Featured Image</label>
                                        <?php if ($project['featured_image']): ?>
                                            <div class="mb-2">
                                                <img src="../uploads/<?php echo htmlspecialchars($project['featured_image']); ?>" 
                                                     class="img-fluid rounded" alt="Current image">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                        <small class="text-muted">Leave empty to keep current image</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <input type="text" class="form-control" id="category" name="category"
                                               value="<?php echo htmlspecialchars($project['category']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="tags" class="form-label">Tags</label>
                                        <input type="text" class="form-control" id="tags" name="tags"
                                               value="<?php echo htmlspecialchars($project['tags']); ?>">
                                        <small class="text-muted">Comma-separated</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="client" class="form-label">Client</label>
                                        <input type="text" class="form-control" id="client" name="client"
                                               value="<?php echo htmlspecialchars($project['client']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="project_url" class="form-label">Project URL</label>
                                        <input type="url" class="form-control" id="project_url" name="project_url"
                                               value="<?php echo htmlspecialchars($project['project_url']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="date_completed" class="form-label">Date Completed</label>
                                        <input type="date" class="form-control" id="date_completed" name="date_completed"
                                               value="<?php echo htmlspecialchars($project['date_completed']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="display_order" class="form-label">Display Order</label>
                                        <input type="number" class="form-control" id="display_order" name="display_order"
                                               value="<?php echo htmlspecialchars($project['display_order']); ?>">
                                        <small class="text-muted">Lower numbers appear first</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo $project['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo $project['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Update Project</button>
                                <a href="projects.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
