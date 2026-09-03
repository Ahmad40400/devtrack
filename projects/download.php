<?php
require_once '../config.php';
requireLogin();

$projectId = $_GET['project'] ?? 0;
$userId = $_SESSION['user_id'];

// Get project
$project = fetchOne("SELECT * FROM projects WHERE id = ?", [$projectId]);

if (!$project) {
    $_SESSION['error'] = 'Project not found.';
    header('Location: ' . BASE_URL . 'projects/');
    exit();
}

// Check permissions
$isOwner = ($project['user_id'] == $userId);
if (!$isOwner && !$project['allow_download']) {
    $_SESSION['error'] = 'Download not allowed by project owner.';
    header('Location: ' . BASE_URL . 'projects/view-public.php?id=' . $projectId . '&user=' . $project['user_id']);
    exit();
}

// Check if file exists
$filePath = UPLOAD_PATH . 'projects/files/' . $project['file_path'];
if (!$project['file_path'] || !file_exists($filePath)) {
    $_SESSION['error'] = 'File not found.';
    header('Location: ' . BASE_URL . 'projects/');
    exit();
}

// Log download
logActivity($userId, 'file_downloaded', "Downloaded file: {$project['file_name']} from project: {$project['title']}");

// Force download
header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $project['file_name'] . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

readfile($filePath);
exit();