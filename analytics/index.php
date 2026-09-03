<?php
require_once '../config.php';
requireLogin();

$page_title = 'Analytics & Statistics';
$userId = $_SESSION['user_id'];

// Get all statistics
$totalProjects = getProjectsCount($userId);
$completedProjects = getProjectsCount($userId, 'completed');
$inProgressProjects = getProjectsCount($userId, 'in-progress');

$totalTasks = getTasksCount($userId);
$completedTasks = getTasksCount($userId, 'completed');
$pendingTasks = getTasksCount($userId, 'pending');
$inProgressTasks = getTasksCount($userId, 'in-progress');

$learningGoals = getLearningGoalsCount($userId);
$completedGoals = getLearningGoalsCount($userId, 'completed');

$skills = getUserSkills($userId);
$avgProficiency = !empty($skills) ? round(array_sum(array_column($skills, 'proficiency')) / count($skills)) : 0;

// Monthly task data
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

// Monthly project data
$projectMonthlyData = fetchAll("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
           COUNT(*) as total,
           SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM projects 
    WHERE user_id = ? 
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC 
    LIMIT 6
", [$userId]);
$projectMonthlyData = array_reverse($projectMonthlyData);

// Task status distribution
$taskStatusData = fetchAll("
    SELECT status, COUNT(*) as count 
    FROM tasks 
    WHERE user_id = ? 
    GROUP BY status
", [$userId]);

// Priority distribution
$priorityData = fetchAll("
    SELECT priority, COUNT(*) as count 
    FROM tasks 
    WHERE user_id = ? 
    GROUP BY priority
", [$userId]);

// Productivity score (tasks completed / total tasks * 100)
$productivityScore = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

// Weekly activity (last 7 days)
$weeklyActivity = fetchAll("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM activity_logs 
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
", [$userId]);

$page_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Task Activity Chart
    const ctx1 = document.getElementById("taskChart").getContext("2d");
    const labels = ' . json_encode(array_column($monthlyData, 'month')) . ';
    const totalData = ' . json_encode(array_column($monthlyData, 'total')) . ';
    const completedData = ' . json_encode(array_column($monthlyData, 'completed')) . ';
    
    new Chart(ctx1, {
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
                legend: { position: "top" }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    
    // Project Activity Chart
    const ctx2 = document.getElementById("projectChart").getContext("2d");
    const projLabels = ' . json_encode(array_column($projectMonthlyData, 'month')) . ';
    const projTotal = ' . json_encode(array_column($projectMonthlyData, 'total')) . ';
    const projCompleted = ' . json_encode(array_column($projectMonthlyData, 'completed')) . ';
    
    new Chart(ctx2, {
        type: "line",
        data: {
            labels: projLabels,
            datasets: [
                {
                    label: "Total Projects",
                    data: projTotal,
                    borderColor: "rgba(54, 162, 235, 1)",
                    backgroundColor: "rgba(54, 162, 235, 0.1)",
                    fill: true,
                    tension: 0.4
                },
                {
                    label: "Completed Projects",
                    data: projCompleted,
                    borderColor: "rgba(75, 192, 192, 1)",
                    backgroundColor: "rgba(75, 192, 192, 0.1)",
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: "top" }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    
    // Task Status Chart
    const ctx3 = document.getElementById("statusChart").getContext("2d");
    const statusLabels = ' . json_encode(array_column($taskStatusData, 'status')) . ';
    const statusCounts = ' . json_encode(array_column($taskStatusData, 'count')) . ';
    const statusColors = {
        "pending": "#ffc107",
        "in-progress": "#0d6efd",
        "completed": "#198754"
    };
    const colors = statusLabels.map(s => statusColors[s] || "#6c757d");
    
    new Chart(ctx3, {
        type: "doughnut",
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: "#fff"
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: "bottom" }
            }
        }
    });
    
    // Priority Chart
    const ctx4 = document.getElementById("priorityChart").getContext("2d");
    const priorityLabels = ' . json_encode(array_column($priorityData, 'priority')) . ';
    const priorityCounts = ' . json_encode(array_column($priorityData, 'count')) . ';
    const priorityColors = {
        "low": "#198754",
        "medium": "#ffc107",
        "high": "#dc3545"
    };
    const pColors = priorityLabels.map(p => priorityColors[p] || "#6c757d");
    
    new Chart(ctx4, {
        type: "pie",
        data: {
            labels: priorityLabels,
            datasets: [{
                data: priorityCounts,
                backgroundColor: pColors,
                borderWidth: 2,
                borderColor: "#fff"
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: "bottom" }
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
                <h6>Tasks Completed</h6>
                <h2><?php echo $completedTasks; ?>/<?php echo $totalTasks; ?></h2>
                <small><?php echo $productivityScore; ?>% completion rate</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Projects Completed</h6>
                <h2><?php echo $completedProjects; ?>/<?php echo $totalProjects; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6>Avg Skill Proficiency</h6>
                <h2><?php echo $avgProficiency; ?>%</h2>
                <small><?php echo count($skills); ?> skills tracked</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6>Learning Progress</h6>
                <h2><?php echo $completedGoals; ?>/<?php echo $learningGoals; ?></h2>
                <small>goals completed</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-4">
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
                <h5 class="card-title mb-0">Task Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Project Activity (Last 6 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="projectChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Task Priority Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="priorityChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Productivity Metrics -->
<div class="row g-4 mt-2">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Productivity Score</h5>
            </div>
            <div class="card-body text-center">
                <div class="display-1 mb-3"><?php echo $productivityScore; ?>%</div>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-<?php echo $productivityScore >= 70 ? 'success' : ($productivityScore >= 40 ? 'warning' : 'danger'); ?>" 
                         style="width: <?php echo $productivityScore; ?>%">
                        <?php echo $productivityScore; ?>%
                    </div>
                </div>
                <p class="mt-3 text-muted">
                    <?php 
                        if ($productivityScore >= 70) echo '🌟 Excellent productivity! Keep up the great work!';
                        elseif ($productivityScore >= 40) echo '📈 Good progress! You\'re on the right track.';
                        else echo '💪 Keep going! Consistency is key to success.';
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center p-3 border rounded">
                            <h6>Tasks</h6>
                            <h3><?php echo $totalTasks; ?></h3>
                            <small class="text-muted"><?php echo $completedTasks; ?> completed</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 border rounded">
                            <h6>Projects</h6>
                            <h3><?php echo $totalProjects; ?></h3>
                            <small class="text-muted"><?php echo $completedProjects; ?> completed</small>
                        </div>
                    </div>
                    <div class="col-6 mt-2">
                        <div class="text-center p-3 border rounded">
                            <h6>Skills</h6>
                            <h3><?php echo count($skills); ?></h3>
                            <small class="text-muted"><?php echo $avgProficiency; ?>% avg</small>
                        </div>
                    </div>
                    <div class="col-6 mt-2">
                        <div class="text-center p-3 border rounded">
                            <h6>Goals</h6>
                            <h3><?php echo $learningGoals; ?></h3>
                            <small class="text-muted"><?php echo $completedGoals; ?> done</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>