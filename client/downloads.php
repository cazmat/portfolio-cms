<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
$db = new Database();

// Clients can only see their assigned projects
$sql = "SELECT DISTINCT p.id, p.title, p.description, p.featured_image
        FROM projects p
        INNER JOIN project_access pa ON p.id = pa.project_id
        WHERE pa.user_id = ? AND pa.can_download = 1
        ORDER BY p.title ASC";

$projects = $db->fetchAll($sql, [$_SESSION['user_id']]);

// Get files for each project
foreach ($projects as &$project) {
    $files = $db->fetchAll(
        "SELECT * FROM project_files WHERE project_id = ? ORDER BY uploaded_at DESC",
        [$project['id']]
    );
    $project['files'] = $files;
    $project['file_count'] = count($files);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Downloads - Client Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">📥 Downloads</h1>
                <p class="lead">Download files from your projects</p>
            </div>
        </div>
        
        <?php if (empty($projects)): ?>
            <div class="alert alert-info">
                <h4>No Downloadable Projects</h4>
                <p class="mb-0">You don't have download access to any projects yet. Contact us if you need access.</p>
            </div>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($project['title']); ?></h5>
                            <span class="badge bg-primary"><?php echo $project['file_count']; ?> file(s)</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($project['description'])): ?>
                            <p class="text-muted"><?php echo htmlspecialchars($project['description']); ?></p>
                        <?php endif; ?>
                        
                        <?php if (empty($project['files'])): ?>
                            <p class="text-muted mb-0">No files available for download yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>File Name</th>
                                            <th>Description</th>
                                            <th>Size</th>
                                            <th>Uploaded</th>
                                            <th>Downloads</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($project['files'] as $file): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($file['original_filename']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($file['file_type']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($file['description'] ?: '-'); ?></td>
                                                <td><?php echo formatFileSize($file['file_size']); ?></td>
                                                <td><?php echo formatDate($file['uploaded_at']); ?></td>
                                                <td><?php echo $file['download_count']; ?></td>
                                                <td>
                                                    <a href="download.php?id=<?php echo $file['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        📥 Download
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
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Portfolio CMS. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
