<?php
require_once '../config.php';
requireLogin();

$projectId = $_GET['id'] ?? 0;
$ownerId = $_GET['user'] ?? 0;

// Get project with owner info
$project = fetchOne("
    SELECT p.*, u.username, u.full_name, u.id as owner_id 
    FROM projects p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ? AND p.user_id = ?
", [$projectId, $ownerId]);

if (!$project) {
    $_SESSION['error'] = 'Project not found.';
    header('Location: ' . BASE_URL . 'users/');
    exit();
}

$page_title = $project['title'] . ' - Project Details';

// Check if this is the owner
$isOwner = ($project['owner_id'] == $_SESSION['user_id']);

// Get project files
$files = [];
if ($project['file_path']) {
    $files = [
        'path' => $project['file_path'],
        'name' => $project['file_name'],
        'size' => $project['file_size'],
        'allow_download' => $project['allow_download']
    ];
}

// Get project tasks (only if public or owner)
$tasks = [];
if ($project['is_public'] || $isOwner) {
    $tasks = fetchAll("SELECT * FROM tasks WHERE project_id = ? AND user_id = ?", [$projectId, $ownerId]);
}

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0"><?php echo sanitizeOutput($project['title']); ?></h4>
                <div>
                    <a href="<?php echo BASE_URL; ?>users/view.php?id=<?php echo $project['owner_id']; ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-user me-1"></i> <?php echo sanitizeOutput($project['username']); ?>
                    </a>
                    <?php if ($isOwner): ?>
                        <a href="<?php echo BASE_URL; ?>projects/edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    <?php endif; ?>
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
                
                <!-- File Download Section -->
                <?php if (!empty($files) && file_exists(UPLOAD_PATH . 'projects/files/' . $files['path'])): ?>
                    <div class="mt-4 border-top pt-3">
                        <h6><i class="fas fa-file-archive me-2"></i>Project Files</h6>
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-3 rounded flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-file-archive fa-2x text-primary me-3"></i>
                                        <strong><?php echo sanitizeOutput($files['name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo round($files['size'] / 1024, 2); ?> KB</small>
                                    </div>
                                    <?php if ($files['allow_download'] || $isOwner): ?>
                                        <a href="<?php echo BASE_URL; ?>projects/download.php?project=<?php echo $project['id']; ?>" class="btn btn-success">
                                            <i class="fas fa-download me-2"></i>Download
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-lock me-1"></i> Download disabled</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if (!$files['allow_download'] && !$isOwner): ?>
                            <small class="text-muted">The project owner has disabled file downloads.</small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Tasks -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tasks (<?php echo count($tasks); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($tasks)): ?>
                    <p class="text-muted">No tasks for this project.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($tasks as $task): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold"><?php echo sanitizeOutput($task['title']); ?></div>
                                        <small class="text-muted">
                                            <?php if ($task['due_date']): ?>
                                                Due: <?php echo formatDate($task['due_date']); ?>
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
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Project Info -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Project Info</h5>
            </div>
            <div class="card-body">
                <p><strong>Owner:</strong> <?php echo sanitizeOutput($project['full_name'] ?: $project['username']); ?></p>
                <p><strong>Created:</strong> <?php echo formatDate($project['created_at']); ?></p>
                <?php if ($project['updated_at']): ?>
                    <p><strong>Updated:</strong> <?php echo formatDate($project['updated_at']); ?></p>
                <?php endif; ?>
                <p><strong>Visibility:</strong> 
                    <span class="badge bg-<?php echo $project['is_public'] ? 'success' : 'danger'; ?>">
                        <?php echo $project['is_public'] ? 'Public' : 'Private'; ?>
                    </span>
                </p>
                <?php if ($project['file_path']): ?>
                    <p><strong>Files:</strong> 
                        <span class="badge bg-info">Available</span>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>users/view.php?id=<?php echo $project['owner_id']; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-user me-2"></i>View Developer Profile
                    </a>
                    <a href="<?php echo BASE_URL; ?>portfolio/?username=<?php echo sanitizeOutput($project['username']); ?>" target="_blank" class="btn btn-outline-secondary">
                        <i class="fas fa-globe me-2"></i>View Portfolio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>