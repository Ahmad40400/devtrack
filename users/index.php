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

<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0" style="color: #1e293b;">Developers</h1>
        <p class="text-muted small mb-0 mt-1">Connect with <?php echo $totalUsers; ?> developers</p>
    </div>
</div>

<!-- Search -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                    <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                           placeholder="Search by username or name..." 
                           value="<?php echo sanitizeOutput($search); ?>"
                           style="background: #f8fafc; border: none; font-size: 0.85rem;">
                    <button type="submit" class="btn btn-primary" style="background: #6366f1; border: none; font-size: 0.85rem; padding: 8px 20px;">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>users/" class="btn btn-outline-secondary w-100" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; color: #64748b;">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Grid -->
<?php if (empty($users)): ?>
    <div class="text-center py-5">
        <div style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <i class="fas fa-users-slash text-muted" style="font-size: 1.8rem; opacity: 0.5;"></i>
        </div>
        <h5 class="fw-bold mb-1" style="color: #1e293b;">No Developers Found</h5>
        <p class="text-muted small mb-0">Try adjusting your search criteria.</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($users as $user): 
            $userSkills = getUserSkills($user['id']);
            $skillCount = count($userSkills);
            $topSkills = array_slice($userSkills, 0, 3);
            $projectCount = getProjectsCount($user['id']);
        ?>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm h-100 hover-card" style="border-radius: 14px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-body text-center p-4">
                        <!-- Avatar -->
                        <div class="position-relative d-inline-block mb-3">
                            <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo $user['avatar'] ?? 'default-avatar.png'; ?>" 
                                 alt="Avatar" class="rounded-circle" 
                                 style="width: 64px; height: 64px; object-fit: cover; border: 2px solid #e2e8f0;">
                            <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white" 
                                  style="width: 14px; height: 14px;"></span>
                        </div>
                        
                        <!-- Name -->
                        <h6 class="mb-0 fw-bold" style="color: #1e293b; font-size: 0.95rem;"><?php echo sanitizeOutput($user['full_name'] ?: $user['username']); ?></h6>
                        <p class="text-muted small mb-2" style="font-size: 0.75rem;">@<?php echo sanitizeOutput($user['username']); ?></p>
                        
                        <!-- Skills -->
                        <?php if (!empty($topSkills)): ?>
                            <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
                                <?php foreach ($topSkills as $skill): ?>
                                    <span class="badge fw-normal px-2 py-1" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 0.65rem; border-radius: 6px;">
                                        <?php echo sanitizeOutput($skill['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if ($skillCount > 3): ?>
                                    <span class="badge fw-normal px-2 py-1" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 0.65rem; border-radius: 6px;">
                                        +<?php echo $skillCount - 3; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <span class="badge fw-normal px-2 py-1" style="background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; font-size: 0.65rem; border-radius: 6px;">
                                    No skills yet
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Stats -->
                        <div class="d-flex justify-content-center gap-4 text-muted small">
                            <div>
                                <span class="fw-bold" style="color: #1e293b; font-size: 0.9rem;"><?php echo $projectCount; ?></span>
                                <span class="d-block" style="font-size: 0.65rem;">Projects</span>
                            </div>
                            <div>
                                <span class="fw-bold" style="color: #1e293b; font-size: 0.9rem;"><?php echo $skillCount; ?></span>
                                <span class="d-block" style="font-size: 0.65rem;">Skills</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-3 pt-0 text-center">
                        <a href="<?php echo BASE_URL; ?>users/view.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm px-4 py-2" style="border-radius: 8px; font-weight: 500; font-size: 0.8rem; background: #6366f1; border: none;">
                            <i class="fas fa-eye me-1"></i>View Profile
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalUsers > $limit): ?>
        <div class="mt-4 d-flex justify-content-center">
            <?php 
                $url = "?search=" . urlencode($search) . "&";
                echo paginate($totalUsers, $page, $limit, $url);
            ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<style>
.hover-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.08) !important;
}
</style>

<?php include_once '../includes/footer.php'; ?>
