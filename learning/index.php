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
$paused = array_filter($goals, fn($g) => $g['status'] === 'paused');

include_once '../includes/header.php';
?>

<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0" style="color: #1e293b;">Learning Roadmap</h1>
        <p class="text-muted small mb-0 mt-1">Track your learning goals and milestones.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>learning/add.php" class="btn btn-primary px-3 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
        <i class="fas fa-plus me-2"></i>New Goal
    </a>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    
    <!-- Total Goals -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(99,102,241,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-bullseye" style="color: #6366f1; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Total</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $totalGoals; ?></h3>
                <p class="text-muted small mb-0 mt-1">Total Goals</p>
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
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo count($completed); ?></h3>
                <p class="text-muted small mb-0 mt-1">Completed</p>
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
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo count($inProgress); ?></h3>
                <p class="text-muted small mb-0 mt-1">In Progress</p>
            </div>
        </div>
    </div>

    <!-- Not Started -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(148,163,184,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-hourglass-start" style="color: #94a3b8; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Idea</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo count($notStarted); ?></h3>
                <p class="text-muted small mb-0 mt-1">Not Started</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="status" class="form-select" onchange="this.form.submit()" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0;">
                    <option value="all">All Status</option>
                    <option value="not-started" <?php echo $status == 'not-started' ? 'selected' : ''; ?>>Not Started</option>
                    <option value="in-progress" <?php echo $status == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="paused" <?php echo $status == 'paused' ? 'selected' : ''; ?>>Paused</option>
                </select>
            </div>
            <div class="col-md-8 text-end">
                <a href="<?php echo BASE_URL; ?>learning/" class="btn btn-outline-secondary px-3" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; color: #64748b;">
                    <i class="fas fa-times me-1"></i>Clear Filter
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Goals List -->
<?php if (empty($goals)): ?>
    <div class="text-center py-5">
        <div style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <i class="fas fa-graduation-cap text-muted" style="font-size: 1.8rem; opacity: 0.5;"></i>
        </div>
        <h5 class="fw-bold mb-1" style="color: #1e293b;">No Learning Goals</h5>
        <p class="text-muted small mb-3">Start your learning journey today!</p>
        <a href="<?php echo BASE_URL; ?>learning/add.php" class="btn btn-primary px-4 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
            <i class="fas fa-plus me-2"></i>Create Goal
        </a>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($goals as $goal): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 goal-card" style="border-radius: 14px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-0" style="color: #1e293b; font-size: 0.95rem;">
                                    <?php echo sanitizeOutput($goal['title']); ?>
                                </h6>
                                <?php if ($goal['target_date']): ?>
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        <i class="fas fa-calendar me-1"></i>Target: <?php echo formatDate($goal['target_date']); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <?php
                                $statusColors = [
                                    'not-started' => ['bg' => 'rgba(148,163,184,0.1)', 'text' => '#64748b'],
                                    'in-progress' => ['bg' => 'rgba(59,130,246,0.1)', 'text' => '#3b82f6'],
                                    'completed' => ['bg' => 'rgba(16,185,129,0.1)', 'text' => '#047857'],
                                    'paused' => ['bg' => 'rgba(245,158,11,0.1)', 'text' => '#b45309']
                                ];
                                $sc = $statusColors[$goal['status']] ?? ['bg' => 'rgba(148,163,184,0.1)', 'text' => '#64748b'];
                            ?>
                            <span class="badge fw-normal px-2 py-1" style="background: <?php echo $sc['bg']; ?>; color: <?php echo $sc['text']; ?>; font-size: 0.65rem; border-radius: 6px;">
                                <?php echo str_replace('-', ' ', $goal['status']); ?>
                            </span>
                        </div>
                        
                        <?php if ($goal['description']): ?>
                            <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.5; color: #64748b;">
                                <?php echo substr(sanitizeOutput($goal['description']), 0, 80); ?>
                            </p>
                        <?php endif; ?>
                        
                        <!-- Progress Bar -->
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted" style="font-size: 0.7rem;">Progress</span>
                                <span class="fw-semibold" style="font-size: 0.75rem; color: #1e293b;"><?php echo $goal['progress']; ?>%</span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 10px; background: #f1f5f9;">
                                <div class="progress-bar" 
                                     style="width: <?php echo $goal['progress']; ?>%; border-radius: 10px; background: <?php echo $goal['progress'] == 100 ? '#10b981' : 'linear-gradient(90deg, #6366f1, #8b5cf6)'; ?>;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent border-0 p-3 pt-0 d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-1">
                            <a href="<?php echo BASE_URL; ?>learning/edit.php?id=<?php echo $goal['id']; ?>" 
                               class="btn btn-sm btn-primary" 
                               title="Edit"
                               style="border-radius: 8px; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; background: #6366f1; border: none;">
                                <i class="fas fa-edit" style="font-size: 0.7rem;"></i>
                            </a>
                            <button onclick="deleteGoal(<?php echo $goal['id']; ?>)" 
                                    class="btn btn-sm btn-danger" 
                                    title="Delete"
                                    style="border-radius: 8px; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; background: #ef4444; border: none;">
                                <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                            </button>
                        </div>
                        
                        <?php if ($goal['status'] !== 'completed'): ?>
                            <a href="<?php echo BASE_URL; ?>learning/complete.php?id=<?php echo $goal['id']; ?>" 
                               class="btn btn-sm btn-success fw-normal"
                               style="border-radius: 8px; font-size: 0.75rem; background: #10b981; border: none; padding: 6px 14px;">
                                <i class="fas fa-check me-1"></i>Complete
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

<style>
.goal-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.08) !important;
}

.goal-card .badge {
    border-radius: 6px;
}
</style>

<?php include_once '../includes/footer.php'; ?>
