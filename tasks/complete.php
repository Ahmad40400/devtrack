<?php
require_once '../config.php';
requireLogin();

$taskId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

$task = fetchOne("SELECT * FROM tasks WHERE id = ? AND user_id = ?", [$taskId, $userId]);

if (!$task) {
    $_SESSION['error'] = 'Task not found.';
    header('Location: ' . BASE_URL . 'tasks/');
    exit();
}

update(
    "UPDATE tasks SET status = 'completed', completed_at = NOW() WHERE id = ? AND user_id = ?",
    [$taskId, $userId]
);

logActivity($userId, 'task_completed', "Completed task: {$task['title']}");
$_SESSION['success'] = 'Task marked as completed!';
header('Location: ' . BASE_URL . 'tasks/');
exit();