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

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">All Tasks</h4>
        <p class="text-muted"><?php echo $total; ?> task(s) found</p>
    </div>
    <a href="<?php echo BASE_URL; ?>tasks/add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>New Task
    </a>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search tasks..." value="<?php echo sanitizeOutput($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all">All Status</option>
                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in-progress" <?php echo $status == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select" onchange="this.form.submit()">
                    <option value="all">All Priority</option>
                    <option value="low" <?php echo $priority == 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo $priority == 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo $priority == 'high' ? 'selected' : ''; ?>>High</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo sanitizeOutput($cat['category']); ?>" <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo sanitizeOutput($cat['category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex">
                    <select name="sort" class="form-select me-2" onchange="this.form.submit()">
                        <option value="created_at" <?php echo $sort == 'created_at' ? 'selected' : ''; ?>>Created</option>
                        <option value="due_date" <?php echo $sort == 'due_date' ? 'selected' : ''; ?>>Due Date</option>
                        <option value="priority" <?php echo $sort == 'priority' ? 'selected' : ''; ?>>Priority</option>
                        <option value="status" <?php echo $sort == 'status' ? 'selected' : ''; ?>>Status</option>
                    </select>
                    <select name="order" class="form-select" onchange="this.form.submit()">
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
        <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
        <h4>No Tasks Found</h4>
        <p class="text-muted">Start by creating your first task.</p>
        <a href="<?php echo BASE_URL; ?>tasks/add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Task
        </a>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Category</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo sanitizeOutput($task['title']); ?></div>
                                <?php if ($task['description']): ?>
                                    <small class="text-muted"><?php echo substr(sanitizeOutput($task['description']), 0, 50); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($task['project_id']): ?>
                                    <?php $project = fetchOne("SELECT title FROM projects WHERE id = ?", [$task['project_id']]); ?>
                                    <?php if ($project): ?>
                                        <a href="<?php echo BASE_URL; ?>projects/view.php?id=<?php echo $task['project_id']; ?>">
                                            <?php echo sanitizeOutput($project['title']); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">No project</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo getStatusBadge($task['status'], 'task'); ?>">
                                    <?php echo $task['status']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo getPriorityBadge($task['priority']); ?>">
                                    <?php echo $task['priority']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($task['category']): ?>
                                    <span class="badge bg-secondary"><?php echo sanitizeOutput($task['category']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($task['due_date']): ?>
                                    <?php echo formatDate($task['due_date']); ?>
                                    <?php 
                                        $days = (strtotime($task['due_date']) - time()) / (60 * 60 * 24);
                                        if ($days < 0 && $task['status'] != 'completed'): 
                                    ?>
                                        <span class="badge bg-danger">Overdue</span>
                                    <?php elseif ($days < 3 && $task['status'] != 'completed'): ?>
                                        <span class="badge bg-warning"><?php echo round($days); ?> days left</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($task['status'] != 'completed'): ?>
                                        <a href="<?php echo BASE_URL; ?>tasks/complete.php?id=<?php echo $task['id']; ?>" class="btn btn-success" title="Complete">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo BASE_URL; ?>tasks/edit.php?id=<?php echo $task['id']; ?>" class="btn btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteTask(<?php echo $task['id']; ?>)" class="btn btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
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
        <div class="mt-4">
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

<?php include_once '../includes/footer.php'; ?>