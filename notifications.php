<?php
require_once 'config.php';
requireLogin();

$page_title = 'Notifications';
$userId = $_SESSION['user_id'];

// Mark all as read
if (isset($_GET['mark_read'])) {
    update("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$userId]);
    $_SESSION['success'] = 'All notifications marked as read.';
    header('Location: ' . BASE_URL . 'notifications.php');
    exit();
}

// Mark individual as read
if (isset($_GET['read']) && $_GET['read']) {
    $notifId = $_GET['read'];
    update("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$notifId, $userId]);
    header('Location: ' . BASE_URL . 'notifications.php');
    exit();
}

// Delete notification
if (isset($_GET['delete']) && $_GET['delete']) {
    $notifId = $_GET['delete'];
    delete("DELETE FROM notifications WHERE id = ? AND user_id = ?", [$notifId, $userId]);
    $_SESSION['success'] = 'Notification deleted.';
    header('Location: ' . BASE_URL . 'notifications.php');
    exit();
}

// Get notifications
$notifications = fetchAll("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
$unreadCount = getUnreadNotificationsCount($userId);

include_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Notifications</h4>
        <p class="text-muted"><?php echo count($notifications); ?> notification(s) total</p>
    </div>
    <?php if ($unreadCount > 0): ?>
        <a href="?mark_read=1" class="btn btn-sm btn-primary">
            <i class="fas fa-check-double me-2"></i>Mark All Read
        </a>
    <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
    <div class="text-center py-5">
        <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
        <h4>No Notifications</h4>
        <p class="text-muted">You're all caught up!</p>
    </div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($notifications as $notif): ?>
            <div class="list-group-item <?php echo $notif['is_read'] ? '' : 'bg-light'; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center">
                            <?php if (!$notif['is_read']): ?>
                                <span class="badge bg-primary me-2">New</span>
                            <?php endif; ?>
                            <span class="badge bg-info me-2"><?php echo $notif['type']; ?></span>
                            <h6 class="mb-0"><?php echo sanitizeOutput($notif['title']); ?></h6>
                        </div>
                        <p class="mb-1 mt-2"><?php echo sanitizeOutput($notif['message']); ?></p>
                        <small class="text-muted"><?php echo timeAgo($notif['created_at']); ?></small>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <?php if (!$notif['is_read']): ?>
                            <a href="?read=<?php echo $notif['id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-check"></i>
                            </a>
                        <?php endif; ?>
                        <a href="?delete=<?php echo $notif['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this notification?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php if ($notif['link']): ?>
                    <div class="mt-2">
                        <a href="<?php echo sanitizeOutput($notif['link']); ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-right me-1"></i>View
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>