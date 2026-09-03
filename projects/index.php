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

include_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">All Projects</h4>
        <p class="text-muted"><?php echo $totalProjects; ?> project(s) found</p>
    </div>
    <a href="<?php echo BASE_URL; ?>projects/add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>New Project
    </a>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search projects..." value="<?php echo sanitizeOutput($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all">All Status</option>
                    <option value="planning" <?php echo $status == 'planning' ? 'selected' : ''; ?>>Planning</option>
                    <option value="in-progress" <?php echo $status == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="on-hold" <?php echo $status == 'on-hold' ? 'selected' : ''; ?>>On Hold</option>
                    <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="<?php echo BASE_URL; ?>projects/" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Projects Grid -->
<?php if (empty($projects)): ?>
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
        <h4>No Projects Found</h4>
        <p class="text-muted">Start by creating your first project.</p>
        <a href="<?php echo BASE_URL; ?>projects/add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Project
        </a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($projects as $project): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($project['image']): ?>
                        <img src="<?php echo BASE_URL; ?>uploads/projects/<?php echo $project['image']; ?>" 
                             class="card-img-top" alt="<?php echo sanitizeOutput($project['title']); ?>" 
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-folder fa-5x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="<?php echo BASE_URL; ?>projects/view.php?id=<?php echo $project['id']; ?>" class="text-decoration-none">
                                <?php echo sanitizeOutput($project['title']); ?>
                            </a>
                        </h5>
                        <?php if ($project['description']): ?>
                            <p class="card-text text-muted">
                                <?php echo substr(sanitizeOutput($project['description']), 0, 100); ?>...
                            </p>
                        <?php endif; ?>
                        <?php if ($project['technologies']): ?>
                            <div class="mb-2">
                                <?php 
                                    $techs = explode(',', $project['technologies']);
                                    foreach ($techs as $tech):
                                ?>
                                    <span class="badge bg-info"><?php echo trim(sanitizeOutput($tech)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                        <span class="badge bg-<?php echo getStatusBadge($project['status']); ?>">
                            <?php echo str_replace('-', ' ', $project['status']); ?>
                        </span>
                        <div>
                            <a href="<?php echo BASE_URL; ?>projects/edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteProject(<?php echo $project['id']; ?>)" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
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

<?php include_once '../includes/footer.php'; ?>