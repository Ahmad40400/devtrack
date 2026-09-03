<?php
require_once '../config.php';
requireLogin();

$page_title = 'Add Learning Goal';
$userId = $_SESSION['user_id']; // ✅ YEH LINE ADD KAREIN
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'not-started';
        $progress = $_POST['progress'] ?? 0;
        $start_date = $_POST['start_date'] ?? null;
        $target_date = $_POST['target_date'] ?? null;
        
        if (empty($title)) {
            $error = 'Title is required.';
        } else {
            insert(
                "INSERT INTO learning_goals (user_id, title, description, status, progress, start_date, target_date) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, $title, $description, $status, $progress, $start_date, $target_date]
            );
            
            logActivity($userId, 'learning_goal_created', "Created goal: $title");
            $_SESSION['success'] = 'Learning goal created successfully!';
            header('Location: ' . BASE_URL . 'learning/');
            exit();
        }
    }
}

include_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Create Learning Goal</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="not-started">Not Started</option>
                                    <option value="in-progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="paused">Paused</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Progress (%)</label>
                                <input type="number" name="progress" class="form-control" min="0" max="100" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Target Date</label>
                                <input type="date" name="target_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>learning/" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Goal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>