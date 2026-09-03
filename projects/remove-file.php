<?php
require_once '../config.php';
requireLogin();

$projectId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

$project = fetchOne("SELECT * FROM projects WHERE id = ? AND user_id = ?", [$projectId, $userId]);

if (!$project) {
    $_SESSION['error'] = 'Project not found.';
    header('Location: ' . BASE_URL . 'projects/');
    exit();
}

// Delete file
if ($project['file_path'] && file_exists(UPLOAD_PATH . 'projects/files/' . $project['file_path'])) {
    unlink(UPLOAD_PATH . 'projects/files/' . $project['file_path']);
}

// Update database
update(
    "UPDATE projects SET file_path = NULL, file_name = NULL, file_size = NULL WHERE id = ?",
    [$projectId]
);

logActivity($userId, 'file_removed', "Removed file from project: {$project['title']}");
$_SESSION['success'] = 'File removed successfully!';
header('Location: ' . BASE_URL . 'projects/edit.php?id=' . $projectId);
exit();