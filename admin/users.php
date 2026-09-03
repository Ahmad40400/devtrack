<?php
require_once '../config.php';
requireAdminRole();

$page_title = 'Manage Users';

// Handle user status toggle
if (isset($_GET['toggle'])) {
    $userId = $_GET['toggle'];
    $user = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    if ($user && $user['id'] != $_SESSION['user_id']) {
        $newStatus = $user['is_active'] ? 0 : 1;
        update("UPDATE users SET is_active = ? WHERE id = ?", [$newStatus, $userId]);
        $_SESSION['success'] = 'User status updated.';
    }
    header('Location: ' . BASE_URL . 'admin/users.php');
    exit();
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    $user = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    if ($user && $user['id'] != $_SESSION['user_id']) {
        delete("DELETE FROM users WHERE id = ?", [$userId]);
        $_SESSION['success'] = 'User deleted successfully.';
    }
    header('Location: ' . BASE_URL . 'admin/users.php');
    exit();
}

// Get all users
$users = fetchAll("SELECT * FROM users ORDER BY created_at DESC");
$totalUsers = count($users);

include_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Manage Users</h4>
        <p class="text-muted"><?php echo $totalUsers; ?> registered users</p>
    </div>
    <a href="../dashboard/" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        <td>
                            <div class="fw-bold"><?php echo sanitizeOutput($user['username']); ?></div>
                            <?php if ($user['full_name']): ?>
                                <small class="text-muted"><?php echo sanitizeOutput($user['full_name']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo sanitizeOutput($user['email']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'warning' : 'secondary'; ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($user['created_at']); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?toggle=<?php echo $user['id']; ?>" class="btn btn-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>">
                                        <i class="fas fa-<?php echo $user['is_active'] ? 'pause' : 'play'; ?>"></i>
                                    </a>
                                    <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this user?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-lock"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>