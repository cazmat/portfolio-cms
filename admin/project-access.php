<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['project_id'])) {
    header('Location: projects.php');
    exit();
}

$project_id = (int)$_POST['project_id'];
$access = $_POST['access'] ?? [];
$download = $_POST['download'] ?? [];

// Delete all existing access for this project
$db->query("DELETE FROM project_access WHERE project_id = ?", [$project_id]);

// Insert new access
foreach ($access as $user_id => $value) {
    $can_download = isset($download[$user_id]) ? 1 : 0;
    $sql = "INSERT INTO project_access (project_id, user_id, can_view, can_download, created_at) 
            VALUES (?, ?, 1, ?, NOW())";
    $db->query($sql, [$project_id, $user_id, $can_download]);
}

header('Location: project-edit.php?id=' . $project_id . '&msg=access_updated');
exit();
?>
