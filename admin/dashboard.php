<?php
require_once '../config.php';
requireAdminRole();

$page_title = 'Admin Dashboard';

// Get statistics
$totalUsers = getTotalUsers();
$totalProjects = getTotalProjects();
$totalTasks = getTotalTasks();
$completedTasks = getCompletedTasks();
$pendingTasks = getPendingTasks();

// Get recent users
$recentUsers = fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Get system activity
$recentActivity = fetchAll("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10");

include_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Admin Dashboard</h4>
    <span class="badge bg-warning text-dark">Admin Panel</span>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Total Users</h6>
                <h2><?php echo $totalUsers; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Total Projects</h6>
                <h2><?php echo $totalProjects; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6>Total Tasks</h6>
                <h2><?php echo $totalTasks; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6>Pending Tasks</h6>
                <h2><?php echo $pendingTasks; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Users -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Users</h5>
                <a href="users.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($recentUsers as $user): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold"><?php echo sanitizeOutput($user['username']); ?></div>
                                <small class="text-muted"><?php echo sanitizeOutput($user['email']); ?></small>
                            </div>
                            <div>
                                <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'warning' : 'secondary'; ?>">
                                    <?php echo $user['role']; ?>
                                </span>
                                <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">System Activity</h5>
                <a href="system-activity.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="list-group-item">
                            <div class="fw-bold"><?php echo sanitizeOutput($activity['action']); ?></div>
                            <?php if ($activity['details']): ?>
                                <small class="text-muted"><?php echo sanitizeOutput($activity['details']); ?></small>
                            <?php endif; ?>
                            <br>
                            <small class="text-muted"><?php echo timeAgo($activity['created_at']); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>