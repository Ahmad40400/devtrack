<?php
require_once '../config.php';
requireLogin();

$page_title = 'Tasks';
$userId = $_SESSION['user_id'];

// Get filter parameters
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$userId];

if ($status && $status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

if ($priority && $priority !== 'all') {
    $sql .= " AND priority = ?";
    $params[] = $priority;
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if ($search) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get total count
$countSql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
$total = fetchOne($countSql, $params)['total'];

$sql .= " ORDER BY $sort $order LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$tasks = fetchAll($sql, $params);

// Get unique categories for filter
$categories = fetchAll("SELECT DISTINCT category FROM tasks WHERE user_id = ? AND category IS NOT NULL", [$userId]);

include_once '../includes/header.php';
?>

<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0" style="color: #1e293b;">Tasks</h1>
        <p class="text-muted small mb-0 mt-1">Manage your tasks and stay productive.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>tasks/add.php" class="btn btn-primary px-3 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
        <i class="fas fa-plus me-2"></i>New Task
    </a>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <?php 
        // Calculate stats for cards
        $allStatusCounts = fetchAll("SELECT status, COUNT(*) as count FROM tasks WHERE user_id = ? GROUP BY status", [$userId]);
        $statusMap = [];
        foreach ($allStatusCounts as $s) { $statusMap[$s['status']] = $s['count']; }
        $totalTasks = array_sum($statusMap);
        $pendingCount = $statusMap['pending'] ?? 0;
        $inProgressCount = $statusMap['in-progress'] ?? 0;
        $completedCount = $statusMap['completed'] ?? 0;
    ?>
    
    <!-- All Tasks -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(99,102,241,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-list-check" style="color: #6366f1; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Total</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $totalTasks; ?></h3>
                <p class="text-muted small mb-0 mt-1">All Tasks</p>
            </div>
        </div>
    </div>

    <!-- Pending Tasks -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(245,158,11,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock" style="color: #f59e0b; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Pending</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $pendingCount; ?></h3>
                <p class="text-muted small mb-0 mt-1">Pending Tasks</p>
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
                <p class="text-muted small mb-0 mt-1">Completed Tasks</p>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                    <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                           placeholder="Search tasks..." 
                           value="<?php echo sanitizeOutput($search); ?>"
                           style="background: #f8fafc; border: none; font-size: 0.85rem;">
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0;">
                    <option value="all">All Status</option>
                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in-progress" <?php echo $status == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select" onchange="this.form.submit()" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0;">
                    <option value="all">All Priority</option>
                    <option value="low" <?php echo $priority == 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo $priority == 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo $priority == 'high' ? 'selected' : ''; ?>>High</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select" onchange="this.form.submit()" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo sanitizeOutput($cat['category']); ?>" <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo sanitizeOutput($cat['category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <select name="sort" class="form-select" onchange="this.form.submit()" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0;">
                        <option value="created_at" <?php echo $sort == 'created_at' ? 'selected' : ''; ?>>Created</option>
                        <option value="due_date" <?php echo $sort == 'due_date' ? 'selected' : ''; ?>>Due Date</option>
                        <option value="priority" <?php echo $sort == 'priority' ? 'selected' : ''; ?>>Priority</option>
                    </select>
                    <select name="order" class="form-select" onchange="this.form.submit()" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0;">
                        <option value="DESC" <?php echo $order == 'DESC' ? 'selected' : ''; ?>>Desc</option>
                        <option value="ASC" <?php echo $order == 'ASC' ? 'selected' : ''; ?>>Asc</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tasks Table -->
<?php if (empty($tasks)): ?>
    <div class="text-center py-5">
        <div style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <i class="fas fa-tasks text-muted" style="font-size: 1.8rem; opacity: 0.5;"></i>
        </div>
        <h5 class="fw-bold mb-1" style="color: #1e293b;">No Tasks Found</h5>
        <p class="text-muted small mb-3">Start by creating your first task.</p>
        <a href="<?php echo BASE_URL; ?>tasks/add.php" class="btn btn-primary px-4 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
            <i class="fas fa-plus me-2"></i>Create Task
        </a>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                <thead style="background: #f8fafc;">
                    <tr>
                        <th class="border-0 px-3 py-3 fw-semibold" style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Title</th>
                        <th class="border-0 px-3 py-3 fw-semibold" style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                        <th class="border-0 px-3 py-3 fw-semibold" style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Priority</th>
                        <th class="border-0 px-3 py-3 fw-semibold" style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Due Date</th>
                        <th class="border-0 px-3 py-3 fw-semibold text-end" style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr style="transition: background 0.2s ease;">
                            <td class="px-3 py-3">
                                <div class="fw-semibold" style="color: #1e293b;"><?php echo sanitizeOutput($task['title']); ?></div>
                                <?php if ($task['category']): ?>
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        <i class="fas fa-tag me-1" style="font-size: 0.6rem;"></i><?php echo sanitizeOutput($task['category']); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3">
                                <?php
                                    $statusColors = [
                                        'pending' => ['bg' => '#f59e0b', 'text' => '#b45309', 'bg_opacity' => 'rgba(245,158,11,0.1)'],
                                        'in-progress' => ['bg' => '#6366f1', 'text' => '#4f46e5', 'bg_opacity' => 'rgba(99,102,241,0.1)'],
                                        'completed' => ['bg' => '#10b981', 'text' => '#047857', 'bg_opacity' => 'rgba(16,185,129,0.1)']
                                    ];
                                    $sc = $statusColors[$task['status']] ?? ['bg' => '#94a3b8', 'text' => '#64748b', 'bg_opacity' => 'rgba(148,163,184,0.1)'];
                                ?>
                                <span class="badge fw-normal px-2 py-1" style="background: <?php echo $sc['bg_opacity']; ?>; color: <?php echo $sc['text']; ?>; font-size: 0.7rem; border-radius: 6px;">
                                    <?php echo $task['status']; ?>
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <?php
                                    $priorityColors = [
                                        'low' => ['bg' => '#10b981', 'text' => '#047857', 'bg_opacity' => 'rgba(16,185,129,0.1)'],
                                        'medium' => ['bg' => '#f59e0b', 'text' => '#b45309', 'bg_opacity' => 'rgba(245,158,11,0.1)'],
                                        'high' => ['bg' => '#ef4444', 'text' => '#b91c1c', 'bg_opacity' => 'rgba(239,68,68,0.1)']
                                    ];
                                    $pc = $priorityColors[$task['priority']] ?? ['bg' => '#94a3b8', 'text' => '#64748b', 'bg_opacity' => 'rgba(148,163,184,0.1)'];
                                ?>
                                <span class="badge fw-normal px-2 py-1" style="background: <?php echo $pc['bg_opacity']; ?>; color: <?php echo $pc['text']; ?>; font-size: 0.7rem; border-radius: 6px;">
                                    <?php echo $task['priority']; ?>
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <?php if ($task['due_date']): ?>
                                    <div style="color: #475569; font-size: 0.8rem;">
                                        <?php echo formatDate($task['due_date'], 'M d, Y'); ?>
                                    </div>
                                    <?php 
                                        $days = (strtotime($task['due_date']) - time()) / (60 * 60 * 24);
                                        if ($days < 0 && $task['status'] != 'completed'): 
                                    ?>
                                        <small class="text-danger" style="font-size: 0.65rem;">
                                            <i class="fas fa-exclamation-circle me-1"></i>Overdue
                                        </small>
                                    <?php elseif ($days < 3 && $days >= 0 && $task['status'] != 'completed'): ?>
                                        <small class="text-warning" style="font-size: 0.65rem;">
                                            <i class="fas fa-clock me-1"></i><?php echo round($days); ?> days left
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.8rem;">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 text-end">
                                <div class="d-inline-flex gap-1">
                                    <?php if ($task['status'] != 'completed'): ?>
                                        <a href="<?php echo BASE_URL; ?>tasks/complete.php?id=<?php echo $task['id']; ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="Complete"
                                           style="border-radius: 8px; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: #10b981; border: none;">
                                            <i class="fas fa-check" style="font-size: 0.75rem;"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo BASE_URL; ?>tasks/edit.php?id=<?php echo $task['id']; ?>" 
                                       class="btn btn-sm btn-primary" 
                                       title="Edit"
                                       style="border-radius: 8px; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: #6366f1; border: none;">
                                        <i class="fas fa-edit" style="font-size: 0.75rem;"></i>
                                    </a>
                                    <button onclick="deleteTask(<?php echo $task['id']; ?>)" 
                                            class="btn btn-sm btn-danger" 
                                            title="Delete"
                                            style="border-radius: 8px; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: #ef4444; border: none;">
                                        <i class="fas fa-trash" style="font-size: 0.75rem;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if ($total > $limit): ?>
        <div class="mt-4 d-flex justify-content-center">
            <?php 
                $url = "?status=$status&priority=$priority&category=$category&search=$search&sort=$sort&order=$order";
                echo paginate($total, $page, $limit, $url . '&');
            ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
function deleteTask(id) {
    if (confirm('Are you sure you want to delete this task?')) {
        window.location.href = '<?php echo BASE_URL; ?>tasks/delete.php?id=' + id;
    }
}
</script>

<style>
/* Table hover effect */
.table-hover tbody tr:hover {
    background-color: #f8fafc !important;
}

/* Pagination customization */
.pagination .page-link {
    border-radius: 8px !important;
    margin: 0 2px;
    border: none;
    color: #475569;
    font-size: 0.85rem;
    padding: 8px 14px;
}

.pagination .page-item.active .page-link {
    background: #6366f1;
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: #cbd5e1;
}
</style>

<?php include_once '../includes/footer.php'; ?>
