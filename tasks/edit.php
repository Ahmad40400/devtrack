<?php
require_once '../config.php';
requireLogin();

$page_title = 'Edit Task';

$taskId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

$task = fetchOne("SELECT * FROM tasks WHERE id = ? AND user_id = ?", [$taskId, $userId]);

if (!$task) {
    $_SESSION['error'] = 'Task not found.';
    header('Location: ' . BASE_URL . 'tasks/');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $project_id = $_POST['project_id'] ?? null;
        $status = $_POST['status'] ?? 'pending';
        $priority = $_POST['priority'] ?? 'medium';
        $category = sanitizeInput($_POST['category'] ?? '');
        $due_date = $_POST['due_date'] ?? null;
        
        if (empty($title)) {
            $error = 'Task title is required.';
        } else {
            // ✅ FIX: Validate that project exists if project_id is provided
            if (!empty($project_id)) {
                $projectCheck = fetchOne("SELECT id FROM projects WHERE id = ? AND user_id = ?", [$project_id, $userId]);
                if (!$projectCheck) {
                    $error = 'Selected project does not exist or you do not have access to it.';
                }
            }
            
            if (!$error) {
                // ✅ If project_id is empty, set to NULL
                $project_id = !empty($project_id) ? $project_id : null;
                
                update(
                    "UPDATE tasks SET 
                        title = ?, description = ?, project_id = ?, status = ?, 
                        priority = ?, category = ?, due_date = ?
                     WHERE id = ? AND user_id = ?",
                    [$title, $description, $project_id, $status, $priority, $category, $due_date, $taskId, $userId]
                );
                
                logActivity($userId, 'task_updated', "Updated task: $title");
                $_SESSION['success'] = 'Task updated successfully!';
                header('Location: ' . BASE_URL . 'tasks/');
                exit();
            }
        }
    }
}

// Get user projects for dropdown (only valid projects)
$projects = fetchAll("SELECT id, title FROM projects WHERE user_id = ? AND status != 'cancelled' ORDER BY title ASC", [$userId]);

include_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Task</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Task Title *</label>
                        <input type="text" name="title" class="form-control" value="<?php echo sanitizeOutput($task['title']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo sanitizeOutput($task['description']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Project</label>
                                <select name="project_id" class="form-select">
                                    <option value="">None</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?php echo $project['id']; ?>" <?php echo $task['project_id'] == $project['id'] ? 'selected' : ''; ?>>
                                            <?php echo sanitizeOutput($project['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" value="<?php echo sanitizeOutput($task['category']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in-progress" <?php echo $task['status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="low" <?php echo $task['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo $task['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo $task['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control" value="<?php echo $task['due_date']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>tasks/" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>