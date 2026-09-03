<?php
require_once '../config.php';
requireLogin();

$page_title = 'Project Details';

$projectId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// Get project
$project = fetchOne("SELECT * FROM projects WHERE id = ? AND user_id = ?", [$projectId, $userId]);

if (!$project) {
    $_SESSION['error'] = 'Project not found.';
    header('Location: ' . BASE_URL . 'projects/');
    exit();
}

// Get project tasks
$tasks = fetchAll("SELECT * FROM tasks WHERE project_id = ? AND user_id = ?", [$projectId, $userId]);

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0"><?php echo sanitizeOutput($project['title']); ?></h4>
                <div>
                    <a href="<?php echo BASE_URL; ?>projects/edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="<?php echo BASE_URL; ?>projects/" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($project['image']): ?>
                    <img src="<?php echo BASE_URL; ?>uploads/projects/<?php echo $project['image']; ?>" 
                         alt="Project image" class="img-fluid rounded mb-3" style="max-height: 400px; width: 100%; object-fit: cover;">
                <?php endif; ?>
                
                <div class="mb-3">
                    <h6>Description</h6>
                    <p><?php echo nl2br(sanitizeOutput($project['description'] ?? 'No description provided.')); ?></p>
                </div>
                
                <?php if ($project['technologies']): ?>
                    <div class="mb-3">
                        <h6>Technologies</h6>
                        <?php 
                            $techs = explode(',', $project['technologies']);
                            foreach ($techs as $tech):
                        ?>
                            <span class="badge bg-info"><?php echo trim(sanitizeOutput($tech)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Status</h6>
                        <span class="badge bg-<?php echo getStatusBadge($project['status']); ?>">
                            <?php echo str_replace('-', ' ', $project['status']); ?>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <h6>Dates</h6>
                        <p>
                            <?php if ($project['start_date']): ?>
                                Start: <?php echo formatDate($project['start_date']); ?><br>
                            <?php endif; ?>
                            <?php if ($project['end_date']): ?>
                                End: <?php echo formatDate($project['end_date']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <?php if ($project['github_url'] || $project['demo_url']): ?>
                    <div class="mt-3">
                        <h6>Links</h6>
                        <?php if ($project['github_url']): ?>
                            <a href="<?php echo sanitizeOutput($project['github_url']); ?>" target="_blank" class="btn btn-dark btn-sm">
                                <i class="fab fa-github"></i> GitHub
                            </a>
                        <?php endif; ?>
                        <?php if ($project['demo_url']): ?>
                            <a href="<?php echo sanitizeOutput($project['demo_url']); ?>" target="_blank" class="btn btn-success btn-sm">
                                <i class="fas fa-external-link-alt"></i> Live Demo
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tasks (<?php echo count($tasks); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($tasks)): ?>
                    <p class="text-muted">No tasks for this project.</p>
                    <a href="<?php echo BASE_URL; ?>tasks/add.php?project_id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add Task
                    </a>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($tasks as $task): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold"><?php echo sanitizeOutput($task['title']); ?></div>
                                        <small class="text-muted">
                                            <?php echo $task['status']; ?>
                                            <?php if ($task['due_date']): ?>
                                                • Due: <?php echo formatDate($task['due_date']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo getStatusBadge($task['status'], 'task'); ?>">
                                        <?php echo $task['status']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <a href="<?php echo BASE_URL; ?>tasks/add.php?project_id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Task
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>