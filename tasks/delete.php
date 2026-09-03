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

delete("DELETE FROM tasks WHERE id = ? AND user_id = ?", [$taskId, $userId]);

logActivity($userId, 'task_deleted', "Deleted task: {$task['title']}");
$_SESSION['success'] = 'Task deleted successfully!';
header('Location: ' . BASE_URL . 'tasks/');
exit();