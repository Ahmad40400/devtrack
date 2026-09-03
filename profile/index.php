<?php
require_once '../config.php';
requireLogin();

$page_title = 'My Profile';
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Get user data
$skills = getUserSkills($userId);
$projects = fetchAll("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$userId]);
$goals = fetchAll("SELECT * FROM learning_goals WHERE user_id = ? AND status != 'completed' ORDER BY target_date ASC LIMIT 5", [$userId]);

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo $user['avatar'] ?? 'default-avatar.png'; ?>" 
                     alt="Profile Photo" class="rounded-circle img-fluid mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <h4><?php echo sanitizeOutput($user['full_name'] ?: $user['username']); ?></h4>
                <p class="text-muted">@<?php echo sanitizeOutput($user['username']); ?></p>
                <?php if ($user['bio']): ?>
                    <p><?php echo nl2br(sanitizeOutput($user['bio'])); ?></p>
                <?php endif; ?>
                
                <hr>
                <div class="text-start">
                    <p><i class="fas fa-envelope me-2"></i> <?php echo sanitizeOutput($user['email']); ?></p>
                    <?php if ($user['website']): ?>
                        <p><i class="fas fa-globe me-2"></i> <a href="<?php echo sanitizeOutput($user['website']); ?>" target="_blank">Website</a></p>
                    <?php endif; ?>
                    <?php if ($user['github_username']): ?>
                        <p><i class="fab fa-github me-2"></i> @<?php echo sanitizeOutput($user['github_username']); ?></p>
                    <?php endif; ?>
                    <?php if ($user['linkedin']): ?>
                        <p><i class="fab fa-linkedin me-2"></i> <a href="<?php echo sanitizeOutput($user['linkedin']); ?>" target="_blank">LinkedIn</a></p>
                    <?php endif; ?>
                    <?php if ($user['twitter']): ?>
                        <p><i class="fab fa-twitter me-2"></i> <a href="<?php echo sanitizeOutput($user['twitter']); ?>" target="_blank">Twitter</a></p>
                    <?php endif; ?>
                </div>
                
                <a href="<?php echo BASE_URL; ?>profile/edit.php" class="btn btn-primary w-100">
                    <i class="fas fa-edit me-2"></i>Edit Profile
                </a>
                <a href="<?php echo BASE_URL; ?>profile/change-password.php" class="btn btn-outline-secondary w-100 mt-2">
    <i class="fas fa-key me-2"></i>Change Password
</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Skills -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Skills</h5>
                <a href="<?php echo BASE_URL; ?>skills/" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body">
                <?php if (empty($skills)): ?>
                    <p class="text-muted">No skills added yet.</p>
                <?php else: ?>
                    <?php foreach ($skills as $skill): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span><?php echo sanitizeOutput($skill['name']); ?></span>
                                <span><?php echo $skill['proficiency']; ?>%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-<?php echo getProgressColor($skill['proficiency']); ?>" 
                                     style="width: <?php echo $skill['proficiency']; ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Projects -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Projects</h5>
                <a href="<?php echo BASE_URL; ?>projects/" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($projects)): ?>
                    <p class="text-muted">No projects yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($projects as $project): ?>
                            <a href="<?php echo BASE_URL; ?>projects/view.php?id=<?php echo $project['id']; ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <?php echo sanitizeOutput($project['title']); ?>
                                <span class="badge bg-<?php echo getStatusBadge($project['status']); ?>">
                                    <?php echo str_replace('-', ' ', $project['status']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Active Learning Goals -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Active Learning Goals</h5>
                <a href="<?php echo BASE_URL; ?>learning/" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($goals)): ?>
                    <p class="text-muted">No active learning goals.</p>
                <?php else: ?>
                    <?php foreach ($goals as $goal): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span><?php echo sanitizeOutput($goal['title']); ?></span>
                                <span><?php echo $goal['progress']; ?>%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-<?php echo getProgressColor($goal['progress']); ?>" 
                                     style="width: <?php echo $goal['progress']; ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>