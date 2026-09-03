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

// Project monthly data
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

// Productivity score
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
    const labels1 = ' . json_encode(array_column($monthlyData, 'month')) . ';
    const totalData1 = ' . json_encode(array_column($monthlyData, 'total')) . ';
    const completedData1 = ' . json_encode(array_column($monthlyData, 'completed')) . ';
    
    new Chart(ctx1, {
        type: "bar",
        data: {
            labels: labels1,
            datasets: [
                {
                    label: "Total",
                    data: totalData1,
                    backgroundColor: "rgba(99, 102, 241, 0.7)",
                    borderColor: "rgba(99, 102, 241, 1)",
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.5
                },
                {
                    label: "Completed",
                    data: completedData1,
                    backgroundColor: "rgba(16, 185, 129, 0.7)",
                    borderColor: "rgba(16, 185, 129, 1)",
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.5
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
                    grid: { color: "rgba(0,0,0,0.03)", drawBorder: false },
                    ticks: { font: { size: 11 } }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    // Project Activity Chart
    const ctx2 = document.getElementById("projectChart").getContext("2d");
    const labels2 = ' . json_encode(array_column($projectMonthlyData, 'month')) . ';
    const totalData2 = ' . json_encode(array_column($projectMonthlyData, 'total')) . ';
    const completedData2 = ' . json_encode(array_column($projectMonthlyData, 'completed')) . ';
    
    new Chart(ctx2, {
        type: "line",
        data: {
            labels: labels2,
            datasets: [
                {
                    label: "Total",
                    data: totalData2,
                    borderColor: "rgba(99, 102, 241, 1)",
                    backgroundColor: "rgba(99, 102, 241, 0.1)",
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: "#6366f1"
                },
                {
                    label: "Completed",
                    data: completedData2,
                    borderColor: "rgba(16, 185, 129, 1)",
                    backgroundColor: "rgba(16, 185, 129, 0.1)",
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: "#10b981"
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
                    grid: { color: "rgba(0,0,0,0.03)", drawBorder: false },
                    ticks: { font: { size: 11 } }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    // Task Status Chart
    const ctx3 = document.getElementById("statusChart").getContext("2d");
    const statusLabels = ' . json_encode(array_column($taskStatusData, 'status')) . ';
    const statusCounts = ' . json_encode(array_column($taskStatusData, 'count')) . ';
    const statusColors = {
        "pending": "#f59e0b",
        "in-progress": "#6366f1",
        "completed": "#10b981"
    };
    const colors = statusLabels.map(s => statusColors[s] || "#94a3b8");
    
    new Chart(ctx3, {
        type: "doughnut",
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: colors,
                borderWidth: 3,
                borderColor: "#ffffff",
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "65%",
            plugins: {
                legend: { 
                    position: "bottom",
                    labels: { 
                        usePointStyle: true,
                        pointStyle: "circle",
                        padding: 15,
                        font: { size: 11 }
                    }
                }
            }
        }
    });

    // Priority Chart
    const ctx4 = document.getElementById("priorityChart").getContext("2d");
    const priorityLabels = ' . json_encode(array_column($priorityData, 'priority')) . ';
    const priorityCounts = ' . json_encode(array_column($priorityData, 'count')) . ';
    const priorityColors = {
        "low": "#10b981",
        "medium": "#f59e0b",
        "high": "#ef4444"
    };
    const pColors = priorityLabels.map(p => priorityColors[p] || "#94a3b8");
    
    new Chart(ctx4, {
        type: "pie",
        data: {
            labels: priorityLabels,
            datasets: [{
                data: priorityCounts,
                backgroundColor: pColors,
                borderWidth: 3,
                borderColor: "#ffffff",
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: "bottom",
                    labels: { 
                        usePointStyle: true,
                        pointStyle: "circle",
                        padding: 15,
                        font: { size: 11 }
                    }
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
        <h1 class="h3 fw-bold mb-0" style="color: #1e293b;">Analytics & Statistics</h1>
        <p class="text-muted small mb-0 mt-1">Track your progress and performance.</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
            <i class="fas fa-chart-line me-2" style="color: #6366f1;"></i>Real-time
        </span>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row g-3 mb-4">
    
    <!-- Tasks Completed Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(99,102,241,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-list-check" style="color: #6366f1; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">
                        <?php echo $productivityScore; ?>% rate
                    </span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;">
                    <?php echo $completedTasks; ?><span style="font-size: 1.1rem; color: #94a3b8;">/<?php echo $totalTasks; ?></span>
                </h3>
                <p class="text-muted small mb-0 mt-1">Tasks Completed</p>
                <div class="d-flex align-items-center mt-2">
                    <span style="font-size: 0.7rem; color: #10b981;">
                        <i class="fas fa-chart-up me-1"></i><?php echo $productivityScore; ?>% completion
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Completed Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(16,185,129,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-folder-open" style="color: #10b981; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">
                        <?php echo $inProgressProjects; ?> active
                    </span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;">
                    <?php echo $completedProjects; ?><span style="font-size: 1.1rem; color: #94a3b8;">/<?php echo $totalProjects; ?></span>
                </h3>
                <p class="text-muted small mb-0 mt-1">Projects Completed</p>
                <div class="d-flex align-items-center mt-2">
                    <span style="font-size: 0.7rem; color: #10b981;">
                        <i class="fas fa-check-circle me-1"></i>On track
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Avg Skill Proficiency Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(59,130,246,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-code" style="color: #3b82f6; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">
                        <?php echo count($skills); ?> skills
                    </span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $avgProficiency; ?>%</h3>
                <p class="text-muted small mb-0 mt-1">Avg Skill Proficiency</p>
                <div class="d-flex align-items-center mt-2">
                    <span style="font-size: 0.7rem; color: #3b82f6;">
                        <i class="fas fa-chart-bar me-1"></i><?php echo count($skills); ?> tracked
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Learning Progress Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(245,158,11,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-graduation-cap" style="color: #f59e0b; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">
                        <?php echo $learningGoals > 0 ? round(($completedGoals / $learningGoals) * 100) : 0; ?>%
                    </span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;">
                    <?php echo $completedGoals; ?><span style="font-size: 1.1rem; color: #94a3b8;">/<?php echo $learningGoals; ?></span>
                </h3>
                <p class="text-muted small mb-0 mt-1">Learning Progress</p>
                <div class="d-flex align-items-center mt-2">
                    <span style="font-size: 0.7rem; color: #f59e0b;">
                        <i class="fas fa-trophy me-1"></i>Goals done
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0" style="color: #1e293b;">Task Activity</h6>
                        <small class="text-muted">Last 6 months</small>
                    </div>
                    <span class="badge bg-light text-dark border fw-normal px-3 py-1">
                        <i class="fas fa-chart-bar me-1" style="color: #6366f1;"></i>Monthly
                    </span>
                </div>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div style="height: 250px;">
                    <canvas id="taskChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="fw-bold mb-0" style="color: #1e293b;">Task Status</h6>
                <small class="text-muted">Distribution</small>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div style="height: 250px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0" style="color: #1e293b;">Project Activity</h6>
                        <small class="text-muted">Last 6 months</small>
                    </div>
                    <span class="badge bg-light text-dark border fw-normal px-3 py-1">
                        <i class="fas fa-chart-line me-1" style="color: #10b981;"></i>Trend
                    </span>
                </div>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div style="height: 250px;">
                    <canvas id="projectChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="fw-bold mb-0" style="color: #1e293b;">Task Priority</h6>
                <small class="text-muted">Distribution</small>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div style="height: 250px;">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Productivity Section -->
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="fw-bold mb-0" style="color: #1e293b;">Productivity Score</h6>
                <small class="text-muted">Overall task completion</small>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="text-center mb-3">
                    <div class="position-relative d-inline-flex align-items-center justify-content-center" style="width: 160px; height: 160px;">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e2e8f0" stroke-width="4" />
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#10b981" stroke-width="4" 
                                  stroke-dasharray="<?php echo $productivityScore; ?>, 100" stroke-linecap="round" />
                        </svg>
                        <div class="position-absolute" style="text-align: center;">
                            <h2 class="fw-bold mb-0" style="color: #1e293b; font-size: 2rem;"><?php echo $productivityScore; ?>%</h2>
                            <small class="text-muted" style="font-size: 0.7rem;">Complete</small>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-muted small mb-0">
                        <?php 
                            if ($productivityScore >= 70) echo '🌟 Excellent! Keep up the great work!';
                            elseif ($productivityScore >= 40) echo '📈 Good progress! You\'re on track.';
                            else echo '💪 Keep going! Consistency is key.';
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="fw-bold mb-0" style="color: #1e293b;">Quick Stats</h6>
                <small class="text-muted">At a glance</small>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="bg-light rounded p-3 text-center" style="border-radius: 10px !important;">
                            <div class="text-muted small mb-1">Tasks</div>
                            <h4 class="fw-bold mb-0" style="color: #1e293b;"><?php echo $totalTasks; ?></h4>
                            <small class="text-muted" style="font-size: 0.65rem;"><?php echo $completedTasks; ?> done</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3 text-center" style="border-radius: 10px !important;">
                            <div class="text-muted small mb-1">Projects</div>
                            <h4 class="fw-bold mb-0" style="color: #1e293b;"><?php echo $totalProjects; ?></h4>
                            <small class="text-muted" style="font-size: 0.65rem;"><?php echo $completedProjects; ?> done</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3 text-center" style="border-radius: 10px !important;">
                            <div class="text-muted small mb-1">Skills</div>
                            <h4 class="fw-bold mb-0" style="color: #1e293b;"><?php echo count($skills); ?></h4>
                            <small class="text-muted" style="font-size: 0.65rem;"><?php echo $avgProficiency; ?>% avg</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3 text-center" style="border-radius: 10px !important;">
                            <div class="text-muted small mb-1">Goals</div>
                            <h4 class="fw-bold mb-0" style="color: #1e293b;"><?php echo $learningGoals; ?></h4>
                            <small class="text-muted" style="font-size: 0.65rem;"><?php echo $completedGoals; ?> done</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
