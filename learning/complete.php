<?php
require_once '../config.php';
requireLogin();

$goalId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

$goal = fetchOne("SELECT * FROM learning_goals WHERE id = ? AND user_id = ?", [$goalId, $userId]);

if (!$goal) {
    $_SESSION['error'] = 'Goal not found.';
    header('Location: ' . BASE_URL . 'learning/');
    exit();
}

update(
    "UPDATE learning_goals SET status = 'completed', progress = 100, completed_at = NOW() WHERE id = ? AND user_id = ?",
    [$goalId, $userId]
);

logActivity($userId, 'learning_goal_completed', "Completed goal: {$goal['title']}");
$_SESSION['success'] = 'Learning goal completed! 🎉';
header('Location: ' . BASE_URL . 'learning/');
exit();