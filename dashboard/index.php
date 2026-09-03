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
    // Task completion chart
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
                    backgroundColor: "rgba(54, 162, 235, 0.5)",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 1
                },
                {
                    label: "Completed Tasks",
                    data: completedData,
                    backgroundColor: "rgba(75, 192, 192, 0.5)",
                    borderColor: "rgba(75, 192, 192, 1)",
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "top"
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
';

include_once '../includes/header.php';
?>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Projects</h6>
                        <h2 class="mb-0"><?php echo $totalProjects; ?></h2>
                    </div>
                    <i class="fas fa-folder fa-2x opacity-50"></i>
                </div>
                <div class="mt-2">
                    <small><?php echo $completedProjects; ?> completed</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Tasks</h6>
                        <h2 class="mb-0"><?php echo $totalTasks; ?></h2>
                    </div>
                    <i class="fas fa-tasks fa-2x opacity-50"></i>
                </div>
                <div class="mt-2">
                    <small><?php echo $completedTasks; ?> completed</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Skills</h6>
                        <h2 class="mb-0"><?php echo $skillCount; ?></h2>
                    </div>
                    <i class="fas fa-code fa-2x opacity-50"></i>
                </div>
                <div class="mt-2">
                    <small><?php echo count(array_filter($skills, fn($s) => $s['proficiency'] >= 70)); ?> advanced</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Learning</h6>
                        <h2 class="mb-0"><?php echo $learningGoals; ?></h2>
                    </div>
                    <i class="fas fa-graduation-cap fa-2x opacity-50"></i>
                </div>
                <div class="mt-2">
                    <small><?php echo $completedGoals; ?> completed</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Progress -->
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Task Activity (Last 6 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="taskChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Productivity</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h3 class="display-4"><?php echo $completionRate; ?>%</h3>
                    <p class="text-muted">Task Completion Rate</p>
                </div>
                <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar bg-success" style="width: <?php echo $completionRate; ?>%">
                        <?php echo $completionRate; ?>%
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-6">
                        <h5><?php echo $pendingTasks + $inProgressTasks; ?></h5>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="col-6">
                        <h5><?php echo $completedTasks; ?></h5>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Projects</h5>
                <a href="<?php echo BASE_URL; ?>projects/" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentProjects)): ?>
                    <p class="text-muted">No projects yet. <a href="<?php echo BASE_URL; ?>projects/add.php">Create one</a></p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentProjects as $project): ?>
                            <a href="<?php echo BASE_URL; ?>projects/view.php?id=<?php echo $project['id']; ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold"><?php echo sanitizeOutput($project['title']); ?></div>
                                    <small class="text-muted"><?php echo timeAgo($project['created_at']); ?></small>
                                </div>
                                <span class="badge bg-<?php echo getStatusBadge($project['status']); ?>">
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Tasks</h5>
                <a href="<?php echo BASE_URL; ?>tasks/" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentTasks)): ?>
                    <p class="text-muted">No tasks yet. <a href="<?php echo BASE_URL; ?>tasks/add.php">Create one</a></p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentTasks as $task): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold"><?php echo sanitizeOutput($task['title']); ?></div>
                                    <small class="text-muted">
                                        <?php echo timeAgo($task['created_at']); ?>
                                        <?php if ($task['due_date']): ?>
                                            • Due: <?php echo formatDate($task['due_date']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-<?php echo getPriorityBadge($task['priority']); ?> me-1">
                                        <?php echo $task['priority']; ?>
                                    </span>
                                    <span class="badge bg-<?php echo getStatusBadge($task['status'], 'task'); ?>">
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

<!-- Activity Log -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php if (empty($activities)): ?>
                    <p class="text-muted">No recent activity.</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($activities as $activity): ?>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle p-2 text-white" style="width: 40px; height: 40px; text-align: center;">
                                        <i class="fas fa-circle"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold"><?php echo sanitizeOutput($activity['action']); ?></div>
                                    <?php if ($activity['details']): ?>
                                        <div class="text-muted"><?php echo sanitizeOutput($activity['details']); ?></div>
                                    <?php endif; ?>
                                    <small class="text-muted"><?php echo timeAgo($activity['created_at']); ?></small>
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