<?php
require_once '../config.php';
requireLogin();

$page_title = 'Projects';
$userId = $_SESSION['user_id'];

// Get filter parameters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM projects WHERE user_id = ?";
$params = [$userId];

if ($status && $status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

if ($search) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR technologies LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$projects = fetchAll($sql, $params);
$totalProjects = count($projects);

// Get stats for cards
$allProjects = fetchAll("SELECT status, COUNT(*) as count FROM projects WHERE user_id = ? GROUP BY status", [$userId]);
$statusMap = [];
foreach ($allProjects as $p) { $statusMap[$p['status']] = $p['count']; }
$totalAll = array_sum($statusMap);
$planningCount = $statusMap['planning'] ?? 0;
$inProgressCount = $statusMap['in-progress'] ?? 0;
$completedCount = $statusMap['completed'] ?? 0;

include_once '../includes/header.php';
?>

<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0" style="color: #1e293b;">Projects</h1>
        <p class="text-muted small mb-0 mt-1">Manage your development projects.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>projects/add.php" class="btn btn-primary px-3 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
        <i class="fas fa-plus me-2"></i>New Project
    </a>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    
    <!-- All Projects -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(99,102,241,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-folder-open" style="color: #6366f1; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Total</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $totalAll; ?></h3>
                <p class="text-muted small mb-0 mt-1">All Projects</p>
            </div>
        </div>
    </div>

    <!-- Planning -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(245,158,11,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-pencil-ruler" style="color: #f59e0b; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Ideas</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $planningCount; ?></h3>
                <p class="text-muted small mb-0 mt-1">Planning</p>
            </div>
        </div>
    </div>

    <!-- In Progress -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(59,130,246,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-spinner" style="color: #3b82f6; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Active</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $inProgressCount; ?></h3>
                <p class="text-muted small mb-0 mt-1">In Progress</p>
            </div>
        </div>
    </div>

    <!-- Completed -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(16,185,129,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle" style="color: #10b981; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Done</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $completedCount; ?></h3>
                <p class="text-muted small mb-0 mt-1">Completed</p>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                    <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                           placeholder="Search projects..." 
                           value="<?php echo sanitizeOutput($search); ?>"
                           style="background: #f8fafc; border: none; font-size: 0.85rem;">
                    <button type="submit" class="btn btn-primary" style="background: #6366f1; border: none; font-size: 0.85rem; padding: 8px 20px;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0;">
                    <option value="all">All Status</option>
                    <option value="planning" <?php echo $status == 'planning' ? 'selected' : ''; ?>>Planning</option>
                    <option value="in-progress" <?php echo $status == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="on-hold" <?php echo $status == 'on-hold' ? 'selected' : ''; ?>>On Hold</option>
                    <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <a href="<?php echo BASE_URL; ?>projects/" class="btn btn-outline-secondary w-100" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; color: #64748b;">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Projects Grid -->
<?php if (empty($projects)): ?>
    <div class="text-center py-5">
        <div style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <i class="fas fa-folder-open text-muted" style="font-size: 1.8rem; opacity: 0.5;"></i>
        </div>
        <h5 class="fw-bold mb-1" style="color: #1e293b;">No Projects Found</h5>
        <p class="text-muted small mb-3">Start by creating your first project.</p>
        <a href="<?php echo BASE_URL; ?>projects/add.php" class="btn btn-primary px-4 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
            <i class="fas fa-plus me-2"></i>Create Project
        </a>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($projects as $project): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 project-card" style="border-radius: 14px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <!-- Image or Placeholder -->
                    <?php if ($project['image']): ?>
                        <img src="<?php echo BASE_URL; ?>uploads/projects/<?php echo $project['image']; ?>" 
                             class="card-img-top" alt="<?php echo sanitizeOutput($project['title']); ?>" 
                             style="height: 160px; object-fit: cover; border-radius: 14px 14px 0 0;">
                    <?php else: ?>
                        <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 160px; background: #f8fafc; border-radius: 14px 14px 0 0;">
                            <i class="fas fa-folder-open" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold mb-0" style="color: #1e293b; font-size: 0.95rem;">
                                <?php echo sanitizeOutput($project['title']); ?>
                            </h6>
                            <?php
                                $statusColors = [
                                    'planning' => ['bg' => 'rgba(245,158,11,0.1)', 'text' => '#b45309'],
                                    'in-progress' => ['bg' => 'rgba(99,102,241,0.1)', 'text' => '#4f46e5'],
                                    'completed' => ['bg' => 'rgba(16,185,129,0.1)', 'text' => '#047857'],
                                    'on-hold' => ['bg' => 'rgba(148,163,184,0.1)', 'text' => '#64748b'],
                                    'cancelled' => ['bg' => 'rgba(239,68,68,0.1)', 'text' => '#b91c1c']
                                ];
                                $sc = $statusColors[$project['status']] ?? ['bg' => 'rgba(148,163,184,0.1)', 'text' => '#64748b'];
                            ?>
                            <span class="badge fw-normal px-2 py-1" style="background: <?php echo $sc['bg']; ?>; color: <?php echo $sc['text']; ?>; font-size: 0.65rem; border-radius: 6px;">
                                <?php echo str_replace('-', ' ', $project['status']); ?>
                            </span>
                        </div>
                        
                        <?php if ($project['description']): ?>
                            <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.5; color: #64748b;">
                                <?php echo substr(sanitizeOutput($project['description']), 0, 80); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($project['technologies']): ?>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <?php 
                                    $techs = explode(',', $project['technologies']);
                                    foreach (array_slice($techs, 0, 3) as $tech):
                                ?>
                                    <span class="badge bg-light text-muted border fw-normal px-2 py-1" style="font-size: 0.65rem; border-radius: 6px;">
                                        <?php echo trim(sanitizeOutput($tech)); ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if (count($techs) > 3): ?>
                                    <span class="badge bg-light text-muted border fw-normal px-2 py-1" style="font-size: 0.65rem; border-radius: 6px;">
                                        +<?php echo count($techs) - 3; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between text-muted small" style="font-size: 0.7rem;">
                            <span><i class="fas fa-calendar me-1"></i><?php echo formatDate($project['created_at'], 'M d'); ?></span>
                            <span><i class="fas fa-tasks me-1"></i><?php echo getTasksCount($userId) . ' tasks'; ?></span>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent border-0 p-3 pt-0 d-flex justify-content-between align-items-center">
                        <a href="<?php echo BASE_URL; ?>projects/view.php?id=<?php echo $project['id']; ?>" 
                           class="btn btn-sm btn-light fw-normal" 
                           style="border-radius: 8px; font-size: 0.75rem; background: #f1f5f9; color: #475569; border: none;">
                            <i class="fas fa-eye me-1"></i>View
                        </a>
                        <div class="d-flex gap-1">
                            <a href="<?php echo BASE_URL; ?>projects/edit.php?id=<?php echo $project['id']; ?>" 
                               class="btn btn-sm btn-primary" 
                               title="Edit"
                               style="border-radius: 8px; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; background: #6366f1; border: none;">
                                <i class="fas fa-edit" style="font-size: 0.7rem;"></i>
                            </a>
                            <button onclick="deleteProject(<?php echo $project['id']; ?>)" 
                                    class="btn btn-sm btn-danger" 
                                    title="Delete"
                                    style="border-radius: 8px; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; background: #ef4444; border: none;">
                                <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function deleteProject(id) {
    if (confirm('Are you sure you want to delete this project?')) {
        window.location.href = '<?php echo BASE_URL; ?>projects/delete.php?id=' + id;
    }
}
</script>

<style>
.project-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.08) !important;
}

.project-card .badge {
    border-radius: 6px;
}
</style>

<?php include_once '../includes/footer.php'; ?>
