<?php
require_once '../config.php';
requireLogin();

$page_title = 'Dashboard';
$userId = $_SESSION['user_id'];

// Get dashboard statistics
$totalProjects = getProjectsCount($userId);
$completedProjects = getProjectsCount($userId, 'completed');
$pendingProjects = getProjectsCount($userId, 'in-progress');

$totalTasks = getTasksCount($userId);
$completedTasks = getTasksCount($userId, 'completed');
$pendingTasks = getTasksCount($userId, 'pending');
$inProgressTasks = getTasksCount($userId, 'in-progress');

$learningGoals = getLearningGoalsCount($userId);
$completedGoals = getLearningGoalsCount($userId, 'completed');

// Get skills
$skills = getUserSkills($userId);
$skillCount = count($skills);

// Get recent activity
$activities = getRecentActivities($userId, 5);

// Get recent projects
$recentProjects = fetchAll("
    SELECT * FROM projects 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
", [$userId]);

// Get recent tasks
$recentTasks = fetchAll("
    SELECT * FROM tasks 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
", [$userId]);

// Calculate productivity
$completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

// Get monthly task data for chart
$monthlyData = fetchAll("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
           COUNT(*) as total,
           SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM tasks 
    WHERE user_id = ? 
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC 
    LIMIT 6
", [$userId]);
$monthlyData = array_reverse($monthlyData);

$page_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById("taskChart").getContext("2d");
    const labels = ' . json_encode(array_column($monthlyData, 'month')) . ';
    const totalData = ' . json_encode(array_column($monthlyData, 'total')) . ';
    const completedData = ' . json_encode(array_column($monthlyData, 'completed')) . ';
    
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Total Tasks",
                    data: totalData,
                    backgroundColor: "rgba(99, 102, 241, 0.7)",
                    borderColor: "rgba(99, 102, 241, 1)",
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.6
                },
                {
                    label: "Completed",
                    data: completedData,
                    backgroundColor: "rgba(16, 185, 129, 0.7)",
                    borderColor: "rgba(16, 185, 129, 1)",
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: "top",
                    labels: { 
                        usePointStyle: true,
                        pointStyle: "circle",
                        padding: 20,
                        font: { size: 12, weight: "500" }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { 
                        color: "rgba(0,0,0,0.03)",
                        drawBorder: false
                    },
                    ticks: { font: { size: 11 } }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
});
</script>
';

include_once '../includes/header.php';
?>

<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0" style="color: #1e293b;">Dashboard</h1>
        <p class="text-muted small mb-0 mt-1">Welcome back! Here's your overview.</p>
    </div>
    <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
        <i class="fas fa-calendar-alt me-2" style="color: #6366f1;"></i><?php echo date('M d, Y'); ?>
    </span>
</div>

<!-- Stats Cards Row -->
<div class="row g-3 mb-4">
    
    <!-- Projects Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(99,102,241,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-folder-open" style="color: #6366f1; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">
                        <?php echo $pendingProjects; ?> active
                    </span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $totalProjects; ?></h3>
                <p class="text-muted small mb-0 mt-1">Total Projects</p>
                <div class="d-flex align-items-center mt-2">
                    <span style="font-size: 0.7rem; color: #10b981;">
                        <i class="fas fa-check-circle me-1"></i><?php echo $completedProjects; ?> completed
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(16,185,129,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-list-check" style="color: #10b981; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">
                        <?php echo $pendingTasks + $inProgressTasks; ?> pending
                    </span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $totalTasks; ?></h3>
                <p class="text-muted small mb-0 mt-1">Total Tasks</p>
                <div class="d-flex align-items-center mt-2">
                    <span style="font-size: 0.7rem; color: #10b981;">
                        <i class="fas fa-check-circle me-1"></i><?php echo $completedTasks; ?> completed
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Skills Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(59,130,246,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-code" style="color: #3b82f6; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">
                        <?php echo count(array_filter($skills, fn($s) => $s['proficiency'] >= 70)); ?> advanced
                    </span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $skillCount; ?></h3>
                <p class="text-muted small mb-0 mt-1">Skills Tracked</p>
                <div class="d-flex align-items-center mt-2">
                    <span style="font-size: 0.7rem; color: #f59e0b;">
                        <i class="fas fa-star me-1"></i>Avg proficiency
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Learning Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(245,158,11,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-graduation-cap" style="color: #f59e0b; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">
                        <?php echo $completedGoals; ?> done
                    </span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $learningGoals; ?></h3>
                <p class="text-muted small mb-0 mt-1">Learning Goals</p>
                <div class="d-flex align-items-center mt-2">
                    <span style="font-size: 0.7rem; color: #f59e0b;">
                        <i class="fas fa-trophy me-1"></i>Keep going!
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart + Productivity -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0" style="color: #1e293b;">Task Activity</h6>
                        <small class="text-muted">Last 6 months performance</small>
                    </div>
                    <span class="badge bg-light text-dark border fw-normal px-3 py-1">
                        <i class="fas fa-chart-bar me-1" style="color: #6366f1;"></i>Monthly
                    </span>
                </div>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div style="height: 260px;">
                    <canvas id="taskChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="fw-bold mb-0" style="color: #1e293b;">Productivity</h6>
                <small class="text-muted">Task completion rate</small>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="text-center mb-3">
                    <div class="position-relative d-inline-flex align-items-center justify-content-center" style="width: 130px; height: 130px;">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e2e8f0" stroke-width="3.5" />
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#10b981" stroke-width="3.5" 
                                  stroke-dasharray="<?php echo $completionRate; ?>, 100" />
                        </svg>
                        <div class="position-absolute" style="text-align: center;">
                            <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.6rem;"><?php echo $completionRate; ?>%</h3>
                            <small class="text-muted" style="font-size: 0.65rem;">Complete</small>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between text-center border-top pt-3 mt-2">
                    <div>
                        <h5 class="fw-bold mb-0" style="color: #1e293b;"><?php echo $pendingTasks + $inProgressTasks; ?></h5>
                        <small class="text-muted" style="font-size: 0.7rem;">Pending</small>
                    </div>
                    <div class="border-start ps-3">
                        <h5 class="fw-bold mb-0" style="color: #1e293b;"><?php echo $completedTasks; ?></h5>
                        <small class="text-muted" style="font-size: 0.7rem;">Completed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Projects & Tasks -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0" style="color: #1e293b;">
                    <i class="fas fa-folder-open me-2" style="color: #6366f1;"></i>Recent Projects
                </h6>
                <a href="<?php echo BASE_URL; ?>projects/" class="small text-decoration-none" style="color: #6366f1;">View All</a>
            </div>
            <div class="card-body px-4 pb-3 pt-1">
                <?php if (empty($recentProjects)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-2x text-muted mb-2" style="opacity: 0.4;"></i>
                        <p class="text-muted small mb-0">No projects yet</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentProjects as $project): ?>
                            <a href="<?php echo BASE_URL; ?>projects/view.php?id=<?php echo $project['id']; ?>" 
                               class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold small" style="color: #1e293b;"><?php echo sanitizeOutput($project['title']); ?></div>
                                    <small class="text-muted" style="font-size: 0.7rem;"><?php echo timeAgo($project['created_at']); ?></small>
                                </div>
                                <span class="badge bg-<?php echo getStatusBadge($project['status']); ?>-opacity-10 text-<?php echo getStatusBadge($project['status']); ?> border border-<?php echo getStatusBadge($project['status']); ?>-25 fw-normal px-2 py-1" style="font-size: 0.65rem;">
                                    <?php echo str_replace('-', ' ', $project['status']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0" style="color: #1e293b;">
                    <i class="fas fa-list-check me-2" style="color: #10b981;"></i>Recent Tasks
                </h6>
                <a href="<?php echo BASE_URL; ?>tasks/" class="small text-decoration-none" style="color: #6366f1;">View All</a>
            </div>
            <div class="card-body px-4 pb-3 pt-1">
                <?php if (empty($recentTasks)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-list-check fa-2x text-muted mb-2" style="opacity: 0.4;"></i>
                        <p class="text-muted small mb-0">No tasks yet</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentTasks as $task): ?>
                            <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold small" style="color: #1e293b;"><?php echo sanitizeOutput($task['title']); ?></div>
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        <?php echo timeAgo($task['created_at']); ?>
                                    </small>
                                </div>
                                <div class="d-flex gap-1">
                                    <span class="badge bg-<?php echo getPriorityBadge($task['priority']); ?>-opacity-10 text-<?php echo getPriorityBadge($task['priority']); ?> border border-<?php echo getPriorityBadge($task['priority']); ?>-25 fw-normal px-2 py-1" style="font-size: 0.65rem;">
                                        <?php echo $task['priority']; ?>
                                    </span>
                                    <span class="badge bg-<?php echo getStatusBadge($task['status'], 'task'); ?>-opacity-10 text-<?php echo getStatusBadge($task['status'], 'task'); ?> border border-<?php echo getStatusBadge($task['status'], 'task'); ?>-25 fw-normal px-2 py-1" style="font-size: 0.65rem;">
                                        <?php echo $task['status']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
