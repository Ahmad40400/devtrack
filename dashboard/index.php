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

<!-- ============================================ -->
<!-- FLOATING AI BUTTON (Bottom Right) -->
<!-- ============================================ -->
<button id="aiFloatingBtn" onclick="toggleAIPanel()" 
        style="position: fixed; bottom: 20px; right: 20px; width: 60px; height: 60px; border-radius: 50%; 
               background: linear-gradient(135deg, #6366f1, #a855f7); border: none; box-shadow: 0 8px 30px rgba(99,102,241,0.4); 
               z-index: 9999; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease;">
    <i class="fas fa-robot text-white" style="font-size: 1.4rem;"></i>
</button>

<!-- AI PANEL (Slide-in from Right - Mobile Perfect) -->
<div id="aiPanel" 
     style="position: fixed; top: 0; right: -100%; width: 100%; max-width: 420px; height: 100dvh; background: #ffffff; 
            box-shadow: -10px 0 40px rgba(0,0,0,0.15); z-index: 9998; transition: right 0.3s ease; 
            display: flex; flex-direction: column; overflow: hidden;">
    
    <!-- Panel Header -->
    <div class="p-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #6366f1, #a855f7); flex-shrink: 0;">
        <div class="d-flex align-items-center">
            <div class="rounded-circle p-1 me-2" style="background: rgba(255,255,255,0.2); width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-robot text-white" style="font-size: 1rem;"></i>
            </div>
            <div>
                <h6 class="fw-bold text-white mb-0" style="font-size: 0.95rem;">AI Assistant</h6>
                <small class="text-white-50" style="font-size: 0.7rem;">Always here to help</small>
            </div>
        </div>
        <button onclick="toggleAIPanel()" class="btn btn-sm text-white p-0" style="font-size: 1.5rem; background: transparent; border: none;">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <!-- Chat Container (Flex Grow - Scrollable) -->
    <div id="aiChatContainer" class="flex-grow-1 p-3" style="overflow-y: auto; background: #f8fafc; min-height: 0;">
        <div class="ai-message ai-bot mb-2">
            <div class="d-flex align-items-start">
                <div class="rounded-circle p-1 me-2" style="background: rgba(99,102,241,0.1); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-robot" style="font-size: 0.7rem; color: #6366f1;"></i>
                </div>
                <div class="bg-white rounded p-2 px-3" style="border-radius: 12px 12px 12px 4px !important; font-size: 0.82rem; color: #334155; max-width: 85%; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    👋 Hi! I'm your AI Assistant. I can help you:
                    <br><br>
                    📁 <strong>Create Project</strong> - "create project MyApp"
                    <br>
                    ✅ <strong>Add Task</strong> - "add task Fix bug"
                    <br>
                    🏷️ <strong>Add Skill</strong> - "add skill PHP"
                    <br>
                    📚 <strong>Create Learning Goal</strong> - "create goal Learn React"
                    <br>
                    📊 <strong>Get Stats</strong> - "show my stats"
                    <br><br>
                    Try it now! 🚀
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Commands (Fixed) -->
    <div class="p-2" style="background: white; border-top: 1px solid #f1f5f9; flex-shrink: 0;">
        <div class="d-flex flex-wrap gap-1 justify-content-center">
            <button class="btn btn-sm btn-outline-primary px-2 py-1" onclick="setAICommand('create project ')" style="font-size: 0.7rem; border-radius: 8px;">📁 Project</button>
            <button class="btn btn-sm btn-outline-success px-2 py-1" onclick="setAICommand('add task ')" style="font-size: 0.7rem; border-radius: 8px;">✅ Task</button>
            <button class="btn btn-sm btn-outline-info px-2 py-1" onclick="setAICommand('add skill ')" style="font-size: 0.7rem; border-radius: 8px;">🏷️ Skill</button>
            <button class="btn btn-sm btn-outline-warning px-2 py-1" onclick="setAICommand('create goal ')" style="font-size: 0.7rem; border-radius: 8px;">📚 Goal</button>
            <button class="btn btn-sm btn-outline-secondary px-2 py-1" onclick="setAICommand('show my stats')" style="font-size: 0.7rem; border-radius: 8px;">📊 Stats</button>
        </div>
    </div>
    
    <!-- Input Box (Fixed - Hamesha Neeche Visible) -->
    <div class="p-2" style="background: white; border-top: 1px solid #f1f5f9; flex-shrink: 0; padding-bottom: max(10px, env(safe-area-inset-bottom)) !important;">
        <div class="input-group">
            <input type="text" id="aiCommandInput" class="form-control" 
                   placeholder="Type command..."
                   style="border-radius: 10px 0 0 10px; border: 1px solid #e2e8f0; font-size: 0.82rem; padding: 10px 12px;"
                   onkeypress="if(event.key==='Enter') processAICommand()">
            <button class="btn btn-primary" onclick="processAICommand()" 
                    style="border-radius: 0 10px 10px 0; background: #6366f1; border: none; padding: 10px 16px;">
                <i class="fas fa-paper-plane" style="font-size: 0.85rem;"></i>
            </button>
        </div>
    </div>
</div>

<!-- AI Processing Overlay (Simple Div - No Bootstrap Modal) -->
<div id="aiProcessingOverlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; padding: 30px; text-align: center; width: 280px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="spinner-border mb-3" style="color: #6366f1; width: 50px; height: 50px;"></div>
        <h6 class="fw-bold mb-1" style="color: #1e293b;">Processing...</h6>
        <p class="text-muted small mb-0">AI is working on your command</p>
    </div>
</div>

<style>
/* AI Chat Styles */
.ai-message {
    display: flex;
    margin-bottom: 12px;
}

.ai-bot {
    justify-content: flex-start;
}

.ai-user {
    justify-content: flex-end;
}

.ai-user .bg-white {
    background: #6366f1 !important;
    color: white !important;
    border-radius: 12px 12px 4px 12px !important;
    box-shadow: 0 2px 8px rgba(99,102,241,0.3) !important;
}

/* Scrollbar */
#aiChatContainer::-webkit-scrollbar {
    width: 6px;
}

#aiChatContainer::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

#aiChatContainer::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

#aiChatContainer::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Floating button hover */
#aiFloatingBtn:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 40px rgba(99,102,241,0.5);
}

/* Panel responsive - Mobile Full Screen */
@media (max-width: 576px) {
    #aiPanel {
        width: 100%;
        max-width: 100%;
        right: -100%;
        height: 100dvh !important;
    }
    
    #aiFloatingBtn {
        bottom: 16px;
        right: 16px;
        width: 55px;
        height: 55px;
    }
    
    #aiFloatingBtn i {
        font-size: 1.2rem;
    }
    
    .p-2 {
        padding: 8px !important;
    }
    
    .p-3 {
        padding: 10px !important;
    }
}

/* iPhone SE, iPhone 12 Mini, etc. */
@media (max-width: 375px) {
    #aiFloatingBtn {
        bottom: 12px;
        right: 12px;
        width: 50px;
        height: 50px;
    }
    
    #aiFloatingBtn i {
        font-size: 1.1rem;
    }
    
    .btn-sm {
        padding: 4px 8px !important;
        font-size: 0.65rem !important;
    }
}
</style>

<script>
// ============================================
// AI PANEL TOGGLE
// ============================================

function toggleAIPanel() {
    const panel = document.getElementById('aiPanel');
    
    if (panel.style.right === '-100%' || panel.style.right === '' || panel.style.right === '-420px') {
        panel.style.right = '0';
    } else {
        panel.style.right = '-100%';
    }
}

// ============================================
// AI ASSISTANT LOGIC
// ============================================

function setAICommand(text) {
    document.getElementById('aiCommandInput').value = text;
    document.getElementById('aiCommandInput').focus();
}

function addAIMessage(message, isUser) {
    const container = document.getElementById('aiChatContainer');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'ai-message ' + (isUser ? 'ai-user' : 'ai-bot');
    
    if (isUser) {
        msgDiv.innerHTML = `
            <div class="bg-white rounded p-2 px-3" style="font-size: 0.82rem; max-width: 85%; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                ${message}
            </div>
        `;
    } else {
        msgDiv.innerHTML = `
            <div class="d-flex align-items-start">
                <div class="rounded-circle p-1 me-2" style="background: rgba(99,102,241,0.1); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-robot" style="font-size: 0.7rem; color: #6366f1;"></i>
                </div>
                <div class="bg-white rounded p-2 px-3" style="border-radius: 12px 12px 12px 4px !important; font-size: 0.82rem; color: #334155; max-width: 85%; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    ${message}
                </div>
            </div>
        `;
    }
    
    container.appendChild(msgDiv);
    container.scrollTop = container.scrollHeight;
}

// Show Processing (Simple Div - No Bootstrap)
function showAIProcessing() {
    const overlay = document.getElementById('aiProcessingOverlay');
    overlay.style.display = 'flex';
}

// Hide Processing (Direct DOM - 100% Guaranteed)
function hideAIProcessing() {
    const overlay = document.getElementById('aiProcessingOverlay');
    overlay.style.display = 'none';
}

function processAICommand() {
    const input = document.getElementById('aiCommandInput');
    const command = input.value.trim();
    
    if (!command) return;
    
    // Add user message
    addAIMessage(command, true);
    input.value = '';
    
    // Show processing
    showAIProcessing();
    
    // Send to backend
    const formData = new FormData();
    formData.append('ai_command', command);
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
    
    fetch('<?php echo BASE_URL; ?>dashboard/ai_assistant.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Hide loader IMMEDIATELY
        hideAIProcessing();
        
        // Add AI response
        addAIMessage(data.message, false);
        
        // If action was successful, reload page after 2 seconds
        if (data.success && data.reload) {
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    })
    .catch(error => {
        // Hide loader IMMEDIATELY on error
        hideAIProcessing();
        addAIMessage('❌ Sorry, something went wrong. Please try again.', false);
        console.error('Error:', error);
    });
}
</script>

<?php include_once '../includes/footer.php'; ?>
