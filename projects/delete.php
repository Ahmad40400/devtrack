<?php
require_once '../config.php';
requireLogin();

$projectId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// Get project
$project = fetchOne("SELECT * FROM projects WHERE id = ? AND user_id = ?", [$projectId, $userId]);

if (!$project) {
    $_SESSION['error'] = 'Project not found.';
    header('Location: ' . BASE_URL . 'projects/');
    exit();
}

// Delete project image
if ($project['image'] && file_exists(UPLOAD_PATH . 'projects/' . $project['image'])) {
    unlink(UPLOAD_PATH . 'projects/' . $project['image']);
}

// Delete project
delete("DELETE FROM projects WHERE id = ? AND user_id = ?", [$projectId, $userId]);

logActivity($userId, 'project_deleted', "Deleted project: {$project['title']}");
$_SESSION['success'] = 'Project deleted successfully!';
header('Location: ' . BASE_URL . 'projects/');
exit();