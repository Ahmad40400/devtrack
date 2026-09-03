<?php
require_once '../config.php';
requireLogin();

$page_title = 'Developers';

$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT * FROM users WHERE id != ?";
$params = [$_SESSION['user_id']];

if ($search) {
    $sql .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$users = fetchAll($sql, $params);

// Get total count
$countSql = "SELECT COUNT(*) FROM users WHERE id != ?";
$countParams = [$_SESSION['user_id']];
if ($search) {
    $countSql .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}
$totalUsers = fetchColumn($countSql, $countParams);

include_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Developers</h4>
        <p class="text-muted">Connect with <?php echo $totalUsers; ?> developers</p>
    </div>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by username, name, or email..." value="<?php echo sanitizeOutput($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <a href="<?php echo BASE_URL; ?>users/" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Users Grid -->
<?php if (empty($users)): ?>
    <div class="text-center py-5">
        <i class="fas fa-users fa-4x text-muted mb-3"></i>
        <h4>No Developers Found</h4>
        <p class="text-muted">Try adjusting your search criteria.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($users as $user): ?>
            <?php 
                $userSkills = getUserSkills($user['id']);
                $skillCount = count($userSkills);
                $topSkills = array_slice($userSkills, 0, 3);
                $projectCount = getProjectsCount($user['id']);
            ?>
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo $user['avatar'] ?? 'default-avatar.png'; ?>" 
                             alt="Avatar" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                        <h5 class="card-title"><?php echo sanitizeOutput($user['full_name'] ?: $user['username']); ?></h5>
                        <p class="text-muted mb-2">@<?php echo sanitizeOutput($user['username']); ?></p>
                        
                        <?php if (!empty($topSkills)): ?>
                            <div class="mb-2">
                                <?php foreach ($topSkills as $skill): ?>
                                    <span class="badge bg-primary me-1"><?php echo sanitizeOutput($skill['name']); ?></span>
                                <?php endforeach; ?>
                                <?php if ($skillCount > 3): ?>
                                    <span class="badge bg-secondary">+<?php echo $skillCount - 3; ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-center gap-3 text-muted small">
                            <span><i class="fas fa-folder me-1"></i> <?php echo $projectCount; ?></span>
                            <span><i class="fas fa-code me-1"></i> <?php echo $skillCount; ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="<?php echo BASE_URL; ?>users/view.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-eye me-2"></i>View Profile
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalUsers > $limit): ?>
        <div class="mt-4">
            <?php 
                $url = "?search=" . urlencode($search) . "&";
                echo paginate($totalUsers, $page, $limit, $url);
            ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include_once '../includes/footer.php'; ?>