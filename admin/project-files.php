<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

$project_id = (int)($_GET['project_id'] ?? 0);

if ($project_id === 0) {
    header('Location: projects.php');
    exit();
}

$project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$project_id]);

if (!$project) {
    header('Location: projects.php');
    exit();
}

$message = '';
$messageType = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $description = sanitizeInput($_POST['description'] ?? '');
    
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $originalFilename = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];
        $tmpName = $_FILES['file']['tmp_name'];
        
        // Generate unique filename
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        $uploadPath = __DIR__ . '/../downloads/' . $filename;
        
        if (move_uploaded_file($tmpName, $uploadPath)) {
            $sql = "INSERT INTO project_files (project_id, filename, original_filename, file_size, file_type, description, uploaded_by, uploaded_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            if ($db->query($sql, [$project_id, $filename, $originalFilename, $fileSize, $fileType, $description, $_SESSION['user_id']])) {
                $message = 'File uploaded successfully!';
                $messageType = 'success';
            } else {
                $message = 'Database error.';
                $messageType = 'error';
            }
        } else {
            $message = 'Failed to upload file.';
            $messageType = 'error';
        }
    } else {
        $message = 'Upload error: ' . $_FILES['file']['error'];
        $messageType = 'error';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $file_id = (int)$_GET['delete'];
    $file = $db->fetchOne("SELECT * FROM project_files WHERE id = ? AND project_id = ?", [$file_id, $project_id]);
    
    if ($file) {
        $filepath = __DIR__ . '/../downloads/' . $file['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        $db->query("DELETE FROM project_files WHERE id = ?", [$file_id]);
        $message = 'File deleted successfully!';
        $messageType = 'success';
    }
}

// Get all files for this project
$files = $db->fetchAll(
    "SELECT pf.*, u.username as uploaded_by_name 
     FROM project_files pf 
     LEFT JOIN users u ON pf.uploaded_by = u.id 
     WHERE pf.project_id = ? 
     ORDER BY pf.uploaded_at DESC",
    [$project_id]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Files - <?php echo htmlspecialchars($project['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">📁 Files: <?php echo htmlspecialchars($project['title']); ?></h1>
                    <a href="project-edit.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">Back to Project</a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📤 Upload File</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Select File *</label>
                                        <input type="file" class="form-control" name="file" required>
                                        <small class="text-muted">Max size: <?php echo ini_get('upload_max_filesize'); ?></small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description (Optional)</label>
                                        <textarea class="form-control" name="description" rows="3" placeholder="What is this file?"></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">📤 Upload File</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">ℹ️ About Project Files</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>What are project files?</strong></p>
                                <p>Files uploaded here are available for download by clients who have "Can Download" permission for this project.</p>
                                
                                <p class="mb-0"><strong>Use cases:</strong></p>
                                <ul class="mb-0">
                                    <li>Final deliverables (images, videos)</li>
                                    <li>Design files (PSD, AI, etc.)</li>
                                    <li>Documents and reports</li>
                                    <li>Source files and assets</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📁 Uploaded Files (<?php echo count($files); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($files)): ?>
                            <p class="text-muted">No files uploaded yet. Upload files above to make them available to clients.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>File Name</th>
                                            <th>Description</th>
                                            <th>Size</th>
                                            <th>Uploaded</th>
                                            <th>Downloads</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($files as $file): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($file['original_filename']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($file['file_type']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($file['description'] ?: '-'); ?></td>
                                                <td><?php echo formatFileSize($file['file_size']); ?></td>
                                                <td>
                                                    <?php echo formatDate($file['uploaded_at']); ?>
                                                    <br><small class="text-muted">by <?php echo htmlspecialchars($file['uploaded_by_name'] ?: 'Unknown'); ?></small>
                                                </td>
                                                <td><?php echo $file['download_count']; ?> time(s)</td>
                                                <td>
                                                    <a href="../downloads/<?php echo $file['filename']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       download="<?php echo htmlspecialchars($file['original_filename']); ?>">
                                                        📥 Download
                                                    </a>
                                                    <a href="?project_id=<?php echo $project_id; ?>&delete=<?php echo $file['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Delete this file permanently?')">
                                                        Delete
                                                    </a>
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
