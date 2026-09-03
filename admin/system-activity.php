<?php
require_once '../config.php';
requireAdminRole();

$page_title = 'System Activity';

// Get activity logs with user info
$activities = fetchAll("
    SELECT al.*, u.username, u.email 
    FROM activity_logs al 
    JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 50
");

include_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">System Activity Log</h4>
    <a href="../dashboard/" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?php echo sanitizeOutput($activity['username']); ?></div>
                            <small class="text-muted"><?php echo sanitizeOutput($activity['email']); ?></small>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?php echo sanitizeOutput($activity['action']); ?></span>
                        </td>
                        <td><?php echo sanitizeOutput($activity['details']); ?></td>
                        <td><?php echo sanitizeOutput($activity['ip_address']); ?></td>
                        <td><?php echo timeAgo($activity['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>