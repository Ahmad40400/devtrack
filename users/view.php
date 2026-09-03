<?php
require_once '../config.php';
requireLogin();

$userId = $_GET['id'] ?? 0;
$currentUserId = $_SESSION['user_id'];

// Get user - YAHAN $profileUser USE KARENGE, $user NAHI
$profileUser = getUserById($userId);

if (!$profileUser) {
    $_SESSION['error'] = 'User not found.';
    header('Location: ' . BASE_URL . 'users/');
    exit();
}

$page_title = ($profileUser['full_name'] ?? $profileUser['username']) . ' - Profile';

// Get user data - YAHAN BHI $profileUser['id'] USE KARENGE
$skills = getUserSkills($userId);
$projects = fetchAll("SELECT * FROM projects WHERE user_id = ? AND (is_public = 1 OR is_public IS NULL) ORDER BY created_at DESC", [$userId]);
$goals = fetchAll("SELECT * FROM learning_goals WHERE user_id = ? AND status = 'completed' ORDER BY completed_at DESC LIMIT 5", [$userId]);
$github = fetchOne("SELECT * FROM github_profiles WHERE user_id = ?", [$userId]);

include_once '../includes/header.php';
?>

<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0" style="color: #1e293b;">Developer Profile</h1>
        <p class="text-muted small mb-0 mt-1">View <?php echo sanitizeOutput($profileUser['username']); ?>'s profile and work.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>users/" class="btn btn-outline-secondary px-3 py-2" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; color: #64748b;">
        <i class="fas fa-arrow-left me-2"></i>Back to Developers
    </a>
</div>

<div class="row g-4">
    <!-- Left Column - Profile Card -->
    <div class="col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-body text-center p-4">
                <!-- Avatar - YAHAN $profileUser USE KARENGE -->
                <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo $profileUser['avatar'] ?? 'default-avatar.png'; ?>" 
                     alt="Profile Photo" class="rounded-circle img-fluid mb-3" 
                     style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e2e8f0;">
                
                <!-- Name - YAHAN $profileUser USE KARENGE -->
                <h5 class="mb-0 fw-bold" style="color: #1e293b;"><?php echo sanitizeOutput($profileUser['full_name'] ?: $profileUser['username']); ?></h5>
                <p class="text-muted small mb-2">@<?php echo sanitizeOutput($profileUser['username']); ?></p>
                
                <!-- Bio - YAHAN $profileUser USE KARENGE -->
                <?php if ($profileUser['bio']): ?>
                    <p class="small text-muted mb-3"><?php echo nl2br(sanitizeOutput($profileUser['bio'])); ?></p>
                <?php endif; ?>
                
                <hr class="my-3" style="border-color: #f1f5f9;">
                
                <!-- Social Links - YAHAN $profileUser USE KARENGE -->
                <div class="text-start small">
                    <?php if ($profileUser['github_username']): ?>
                        <p class="mb-1"><i class="fab fa-github me-2 text-dark"></i> @<?php echo sanitizeOutput($profileUser['github_username']); ?></p>
                    <?php endif; ?>
                    <?php if ($profileUser['website']): ?>
                        <p class="mb-1"><i class="fas fa-globe me-2 text-primary"></i> <a href="<?php echo sanitizeOutput($profileUser['website']); ?>" target="_blank" class="text-decoration-none">Website</a></p>
                    <?php endif; ?>
                    <?php if ($profileUser['linkedin']): ?>
                        <p class="mb-1"><i class="fab fa-linkedin me-2 text-primary"></i> <a href="<?php echo sanitizeOutput($profileUser['linkedin']); ?>" target="_blank" class="text-decoration-none">LinkedIn</a></p>
                    <?php endif; ?>
                    <?php if ($profileUser['twitter']): ?>
                        <p class="mb-1"><i class="fab fa-twitter me-2 text-info"></i> <a href="<?php echo sanitizeOutput($profileUser['twitter']); ?>" target="_blank" class="text-decoration-none">Twitter</a></p>
                    <?php endif; ?>
                </div>
                
                <hr class="my-3" style="border-color: #f1f5f9;">
                
                <!-- Buttons - YAHAN $profileUser USE KARENGE -->
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>portfolio/?username=<?php echo sanitizeOutput($profileUser['username']); ?>" target="_blank" class="btn btn-primary btn-sm py-2" style="border-radius: 8px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
                        <i class="fas fa-globe me-1"></i>View Portfolio
                    </a>
                </div>
            </div>
        </div>
        
        <!-- GitHub Card -->
        <?php if ($github): ?>
        <div class="card border-0 shadow-sm mt-3" style="border-radius: 14px;">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-2" style="color: #1e293b; font-size: 0.9rem;">
                    <i class="fab fa-github me-2" style="color: #6366f1;"></i>GitHub
                </h6>
                <div class="d-flex justify-content-between text-center">
                    <div>
                        <div class="fw-bold" style="color: #1e293b;"><?php echo $github['followers_count']; ?></div>
                        <small class="text-muted" style="font-size: 0.7rem;">Followers</small>
                    </div>
                    <div>
                        <div class="fw-bold" style="color: #1e293b;"><?php echo $github['following_count']; ?></div>
                        <small class="text-muted" style="font-size: 0.7rem;">Following</small>
                    </div>
                    <div>
                        <div class="fw-bold" style="color: #1e293b;"><?php echo $github['public_repos']; ?></div>
                        <small class="text-muted" style="font-size: 0.7rem;">Repos</small>
                    </div>
                </div>
                <?php if ($github['bio']): ?>
                    <p class="small text-muted mt-2 mb-0"><?php echo sanitizeOutput($github['bio']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Right Column -->
    <div class="col-md-8 col-lg-9">
        <!-- Skills -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="card-title mb-0 fw-bold" style="color: #1e293b;">
                    <i class="fas fa-code me-2" style="color: #6366f1;"></i>Skills
                    <?php if (!empty($skills)): ?>
                        <span class="badge rounded-pill ms-2" style="background: rgba(99,102,241,0.1); color: #6366f1; font-size: 0.7rem;"><?php echo count($skills); ?></span>
                    <?php endif; ?>
                </h6>
            </div>
            <div class="card-body pt-0 px-4 pb-4">
                <?php if (empty($skills)): ?>
                    <p class="text-muted small mb-0">No skills added yet.</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($skills as $skill): ?>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span style="color: #334155; font-weight: 500;"><?php echo sanitizeOutput($skill['name']); ?></span>
                                    <span style="color: #64748b;"><?php echo $skill['proficiency']; ?>%</span>
                                </div>
                                <div class="progress" style="height: 4px; border-radius: 10px; background: #f1f5f9;">
                                    <div class="progress-bar" style="width: <?php echo $skill['proficiency']; ?>%; border-radius: 10px; background: linear-gradient(90deg, #6366f1, #a855f7);">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Projects -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold" style="color: #1e293b;">
                    <i class="fas fa-folder-open me-2" style="color: #10b981;"></i>Projects
                    <?php if (!empty($projects)): ?>
                        <span class="badge rounded-pill ms-2" style="background: rgba(16,185,129,0.1); color: #10b981; font-size: 0.7rem;"><?php echo count($projects); ?></span>
                    <?php endif; ?>
                </h6>
            </div>
            <div class="card-body pt-0 px-4 pb-4">
                <?php if (empty($projects)): ?>
                    <p class="text-muted small mb-0">No public projects yet.</p>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($projects as $project): ?>
                            <div class="col-md-6">
                                <div class="card border-0" style="background: #f8fafc; border-radius: 10px;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="mb-1 fw-bold small" style="color: #1e293b;">
                                                <?php echo sanitizeOutput($project['title']); ?>
                                            </h6>
                                            <span class="badge fw-normal px-2 py-1" style="font-size: 0.6rem; border-radius: 6px; background: rgba(16,185,129,0.1); color: #047857;">
                                                <?php echo str_replace('-', ' ', $project['status']); ?>
                                            </span>
                                        </div>
                                        <?php if ($project['description']): ?>
                                            <p class="text-muted small mb-1" style="font-size: 0.75rem;">
                                                <?php echo substr(sanitizeOutput($project['description']), 0, 60); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($project['technologies']): ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php 
                                                    $techs = explode(',', $project['technologies']);
                                                    foreach (array_slice($techs, 0, 3) as $tech):
                                                ?>
                                                    <span class="badge fw-normal px-2 py-1" style="font-size: 0.6rem; border-radius: 6px; background: white; color: #475569; border: 1px solid #e2e8f0;">
                                                        <?php echo trim(sanitizeOutput($tech)); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
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
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="card-title mb-0 fw-bold" style="color: #1e293b;">
                    <i class="fas fa-graduation-cap me-2" style="color: #f59e0b;"></i>Completed Learning
                    <span class="badge rounded-pill ms-2" style="background: rgba(245,158,11,0.1); color: #b45309; font-size: 0.7rem;"><?php echo count($goals); ?></span>
                </h6>
            </div>
            <div class="card-body pt-0 px-4 pb-4">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($goals as $goal): ?>
                        <span class="badge fw-normal px-3 py-2" style="background: rgba(16,185,129,0.1); color: #047857; border: 1px solid rgba(16,185,129,0.2); font-size: 0.75rem; border-radius: 8px;">
                            <i class="fas fa-check-circle me-1"></i>
                            <?php echo sanitizeOutput($goal['title']); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
