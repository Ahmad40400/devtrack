<?php
require_once '../config.php';
requireLogin();

$userId = $_GET['id'] ?? 0;
$currentUserId = $_SESSION['user_id'];

// Get user
$user = getUserById($userId);

if (!$user) {
    $_SESSION['error'] = 'User not found.';
    header('Location: ' . BASE_URL . 'users/');
    exit();
}

$page_title = $user['full_name'] . ' - Profile';

// Get user data
$skills = getUserSkills($userId);
$projects = fetchAll("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
$goals = fetchAll("SELECT * FROM learning_goals WHERE user_id = ? AND status = 'completed' ORDER BY completed_at DESC LIMIT 5", [$userId]);
$github = fetchOne("SELECT * FROM github_profiles WHERE user_id = ?", [$userId]);

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
                    <?php if ($user['github_username']): ?>
                        <p><i class="fab fa-github me-2"></i> @<?php echo sanitizeOutput($user['github_username']); ?></p>
                    <?php endif; ?>
                    <?php if ($user['website']): ?>
                        <p><i class="fas fa-globe me-2"></i> <a href="<?php echo sanitizeOutput($user['website']); ?>" target="_blank">Website</a></p>
                    <?php endif; ?>
                    <?php if ($user['linkedin']): ?>
                        <p><i class="fab fa-linkedin me-2"></i> <a href="<?php echo sanitizeOutput($user['linkedin']); ?>" target="_blank">LinkedIn</a></p>
                    <?php endif; ?>
                    <?php if ($user['twitter']): ?>
                        <p><i class="fab fa-twitter me-2"></i> <a href="<?php echo sanitizeOutput($user['twitter']); ?>" target="_blank">Twitter</a></p>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>portfolio/?username=<?php echo sanitizeOutput($user['username']); ?>" target="_blank" class="btn btn-primary">
                        <i class="fas fa-globe me-2"></i>View Portfolio
                    </a>
                    <a href="<?php echo BASE_URL; ?>users/" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Developers
                    </a>
                </div>
            </div>
        </div>
        
        <!-- GitHub Info -->
        <?php if ($github): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="fab fa-github me-2"></i>GitHub</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <span><i class="fas fa-users me-1"></i> <?php echo $github['followers_count']; ?> followers</span>
                    <span><i class="fas fa-folder me-1"></i> <?php echo $github['public_repos']; ?> repos</span>
                </div>
                <?php if ($github['bio']): ?>
                    <p class="mt-2 small"><?php echo sanitizeOutput($github['bio']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-8">
        <!-- Skills -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-code me-2"></i>Skills</h5>
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
        
        <!-- Projects -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-folder me-2"></i>Projects (<?php echo count($projects); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($projects)): ?>
                    <p class="text-muted">No projects yet.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($projects as $project): ?>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <?php if ($project['image']): ?>
                                        <img src="<?php echo BASE_URL; ?>uploads/projects/<?php echo $project['image']; ?>" 
                                             class="card-img-top" alt="<?php echo sanitizeOutput($project['title']); ?>" 
                                             style="height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <i class="fas fa-folder fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <a href="<?php echo BASE_URL; ?>projects/view-public.php?id=<?php echo $project['id']; ?>&user=<?php echo $user['id']; ?>" class="text-decoration-none">
                                                <?php echo sanitizeOutput($project['title']); ?>
                                            </a>
                                        </h6>
                                        <?php if ($project['description']): ?>
                                            <p class="card-text small text-muted">
                                                <?php echo substr(sanitizeOutput($project['description']), 0, 80); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($project['technologies']): ?>
                                            <div class="mb-2">
                                                <?php 
                                                    $techs = explode(',', $project['technologies']);
                                                    foreach (array_slice($techs, 0, 3) as $tech):
                                                ?>
                                                    <span class="badge bg-info"><?php echo trim(sanitizeOutput($tech)); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?php echo getStatusBadge($project['status']); ?>">
                                            <?php echo str_replace('-', ' ', $project['status']); ?>
                                        </span>
                                        <a href="<?php echo BASE_URL; ?>projects/view-public.php?id=<?php echo $project['id']; ?>&user=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Learning Goals -->
        <?php if (!empty($goals)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-graduation-cap me-2"></i>Completed Learning Goals</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($goals as $goal): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <?php echo sanitizeOutput($goal['title']); ?>
                                <span class="badge bg-success">Completed ✓</span>
                            </div>
                            <?php if ($goal['completed_at']): ?>
                                <small class="text-muted"><?php echo formatDate($goal['completed_at']); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>