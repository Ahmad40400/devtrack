<?php
require_once '../config.php';
requireLogin();

$page_title = 'Learning Roadmap';
$userId = $_SESSION['user_id'];

// Get filter
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM learning_goals WHERE user_id = ?";
$params = [$userId];

if ($status && $status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY created_at DESC";
$goals = fetchAll($sql, $params);
$totalGoals = count($goals);

// Calculate stats
$completed = array_filter($goals, fn($g) => $g['status'] === 'completed');
$inProgress = array_filter($goals, fn($g) => $g['status'] === 'in-progress');
$notStarted = array_filter($goals, fn($g) => $g['status'] === 'not-started');

include_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Learning Roadmap</h4>
        <p class="text-muted"><?php echo $totalGoals; ?> goal(s) in progress</p>
    </div>
    <a href="<?php echo BASE_URL; ?>learning/add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>New Goal
    </a>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6>Total Goals</h6>
                <h3><?php echo $totalGoals; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Completed</h6>
                <h3><?php echo count($completed); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>In Progress</h6>
                <h3><?php echo count($inProgress); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <h6>Not Started</h6>
                <h3><?php echo count($notStarted); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all">All Status</option>
                    <option value="not-started" <?php echo $status == 'not-started' ? 'selected' : ''; ?>>Not Started</option>
                    <option value="in-progress" <?php echo $status == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="paused" <?php echo $status == 'paused' ? 'selected' : ''; ?>>Paused</option>
                </select>
            </div>
            <div class="col-md-8">
                <a href="<?php echo BASE_URL; ?>learning/" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Goals List -->
<?php if (empty($goals)): ?>
    <div class="text-center py-5">
        <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
        <h4>No Learning Goals</h4>
        <p class="text-muted">Start your learning journey today!</p>
        <a href="<?php echo BASE_URL; ?>learning/add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Goal
        </a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($goals as $goal): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title"><?php echo sanitizeOutput($goal['title']); ?></h5>
                            <span class="badge bg-<?php echo getStatusBadge($goal['status'], 'learning'); ?>">
                                <?php echo str_replace('-', ' ', $goal['status']); ?>
                            </span>
                        </div>
                        <?php if ($goal['description']): ?>
                            <p class="card-text text-muted">
                                <?php echo substr(sanitizeOutput($goal['description']), 0, 100); ?>
                            </p>
                        <?php endif; ?>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <span>Progress</span>
                                <span><?php echo $goal['progress']; ?>%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-<?php echo getProgressColor($goal['progress']); ?>" 
                                     style="width: <?php echo $goal['progress']; ?>%">
                                </div>
                            </div>
                        </div>
                        <?php if ($goal['target_date']): ?>
                            <small class="text-muted mt-2 d-block">
                                Target: <?php echo formatDate($goal['target_date']); ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between">
                        <div>
                            <a href="<?php echo BASE_URL; ?>learning/edit.php?id=<?php echo $goal['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteGoal(<?php echo $goal['id']; ?>)" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <?php if ($goal['status'] !== 'completed'): ?>
                            <a href="<?php echo BASE_URL; ?>learning/complete.php?id=<?php echo $goal['id']; ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-check"></i> Complete
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function deleteGoal(id) {
    if (confirm('Are you sure you want to delete this learning goal?')) {
        window.location.href = '<?php echo BASE_URL; ?>learning/delete.php?id=' + id;
    }
}
</script>

<?php include_once '../includes/footer.php'; ?>