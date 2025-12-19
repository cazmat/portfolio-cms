<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
$db = new Database();

$file_id = (int)($_GET['id'] ?? 0);

if ($file_id === 0) {
    die('Invalid file ID');
}

// Get file info
$file = $db->fetchOne("SELECT * FROM project_files WHERE id = ?", [$file_id]);

if (!$file) {
    die('File not found');
}

// Check if user has download access to this project
$hasAccess = $db->fetchOne(
    "SELECT pa.can_download 
     FROM project_access pa 
     WHERE pa.project_id = ? AND pa.user_id = ? AND pa.can_download = 1",
    [$file['project_id'], $_SESSION['user_id']]
);

if (!$hasAccess && !isAdmin()) {
    die('Access denied - You do not have download permission for this project');
}

// File path
$filepath = __DIR__ . '/../downloads/' . $file['filename'];

if (!file_exists($filepath)) {
    die('File not found on server');
}

// Increment download count
$db->query("UPDATE project_files SET download_count = download_count + 1 WHERE id = ?", [$file_id]);

// Send file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

readfile($filepath);
exit();
