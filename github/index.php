<?php
require_once '../config.php';
requireLogin();

$page_title = 'GitHub Integration';
$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user's GitHub profile
$githubProfile = fetchOne("SELECT * FROM github_profiles WHERE user_id = ?", [$userId]);

// Get GitHub username from user table
$user = getUserById($userId);
$githubUsername = $user['github_username'] ?? '';

// Handle GitHub username save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_username'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $username = sanitizeInput($_POST['github_username'] ?? '');
        if (empty($username)) {
            $error = 'Please enter a GitHub username.';
        } else {
            update("UPDATE users SET github_username = ? WHERE id = ?", [$username, $userId]);
            $_SESSION['success'] = 'GitHub username saved! Click Sync to fetch data.';
            header('Location: ' . BASE_URL . 'github/');
            exit();
        }
    }
}

// Handle GitHub data sync (API call)
if (isset($_GET['sync']) && $_GET['sync'] == '1') {
    if (!$githubUsername) {
        $_SESSION['error'] = 'Please save your GitHub username first.';
    } else {
        // GitHub API call
        $apiUrl = "https://api.github.com/users/{$githubUsername}";
        $options = [
            'http' => [
                'header' => "User-Agent: DevTrack\r\n",
                'method' => 'GET'
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($apiUrl, false, $context);
        
        if ($response === false) {
            $_SESSION['error'] = 'Failed to fetch GitHub data. User may not exist or API rate limit exceeded.';
        } else {
            $data = json_decode($response, true);
            
            // Get repositories
            $repoUrl = "https://api.github.com/users/{$githubUsername}/repos?sort=updated&per_page=10";
            $repoResponse = @file_get_contents($repoUrl, false, $context);
            $repos = $repoResponse ? json_decode($repoResponse, true) : [];
            
            // Save to database
            if ($githubProfile) {
                update(
                    "UPDATE github_profiles SET 
                        github_username = ?, avatar_url = ?, profile_name = ?, 
                        bio = ?, company = ?, location = ?, 
                        followers_count = ?, following_count = ?, public_repos = ?,
                        repo_data = ?, last_fetched = NOW()
                     WHERE user_id = ?",
                    [
                        $githubUsername,
                        $data['avatar_url'] ?? '',
                        $data['name'] ?? '',
                        $data['bio'] ?? '',
                        $data['company'] ?? '',
                        $data['location'] ?? '',
                        $data['followers'] ?? 0,
                        $data['following'] ?? 0,
                        $data['public_repos'] ?? 0,
                        json_encode($repos),
                        $userId
                    ]
                );
            } else {
                insert(
                    "INSERT INTO github_profiles 
                        (user_id, github_username, avatar_url, profile_name, bio, company, location, 
                         followers_count, following_count, public_repos, repo_data, last_fetched) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $userId,
                        $githubUsername,
                        $data['avatar_url'] ?? '',
                        $data['name'] ?? '',
                        $data['bio'] ?? '',
                        $data['company'] ?? '',
                        $data['location'] ?? '',
                        $data['followers'] ?? 0,
                        $data['following'] ?? 0,
                        $data['public_repos'] ?? 0,
                        json_encode($repos)
                    ]
                );
            }
            
            $_SESSION['success'] = 'GitHub data synced successfully!';
            header('Location: ' . BASE_URL . 'github/');
            exit();
        }
    }
}

// Handle GitHub unlink/disconnect
if (isset($_GET['unlink']) && $_GET['unlink'] == '1') {
    // Clear github_username from users table
    update("UPDATE users SET github_username = NULL WHERE id = ?", [$userId]);
    
    // Delete github profile data
    delete("DELETE FROM github_profiles WHERE user_id = ?", [$userId]);
    
    logActivity($userId, 'github_unlinked', 'GitHub account disconnected');
    $_SESSION['success'] = 'GitHub account disconnected successfully!';
    header('Location: ' . BASE_URL . 'github/');
    exit();
}

// Get fresh profile data after sync
$githubProfile = fetchOne("SELECT * FROM github_profiles WHERE user_id = ?", [$userId]);

include_once '../includes/header.php';
?>

<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0" style="color: #1e293b;">GitHub Integration</h1>
        <p class="text-muted small mb-0 mt-1">Connect your GitHub account and showcase your repositories.</p>
    </div>
    <?php if ($githubUsername && $githubProfile): ?>
        <div class="d-flex gap-2">
            <a href="?sync=1" class="btn btn-primary px-3 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
                <i class="fas fa-sync me-2"></i>Sync Data
            </a>
            <button onclick="confirmUnlink()" class="btn btn-outline-danger px-3 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; border: 1.5px solid #ef4444; color: #ef4444;">
                <i class="fas fa-unlink me-2"></i>Unlink
            </button>
        </div>
    <?php endif; ?>
</div>

<div class="row g-4">
    <!-- Left Column - GitHub Settings -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="fw-bold mb-0" style="color: #1e293b; font-size: 0.95rem;">
                    <i class="fas fa-cog me-2" style="color: #6366f1;"></i>GitHub Settings
                </h6>
                <small class="text-muted">Manage your GitHub connection</small>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius: 8px; font-size: 0.8rem;">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.8rem; color: #334155;">GitHub Username</label>
                        <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                            <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                                <i class="fab fa-github text-muted"></i>
                            </span>
                            <input type="text" name="github_username" class="form-control border-start-0 ps-0" 
                                   value="<?php echo sanitizeOutput($githubUsername); ?>" 
                                   placeholder="Enter your GitHub username"
                                   style="background: #f8fafc; border: none; font-size: 0.85rem; padding: 10px 12px;">
                        </div>
                    </div>
                    
                    <button type="submit" name="save_username" class="btn btn-primary w-100 py-2 mb-2" 
                            style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
                        <i class="fas fa-save me-2"></i>Save Username
                    </button>
                </form>
                
                <?php if ($githubUsername && $githubProfile): ?>
                    <div class="border-top pt-3 mt-3" style="border-color: #f1f5f9 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-muted small" style="font-size: 0.75rem;">
                                <i class="fas fa-clock me-1"></i>Last synced:
                            </span>
                            <span class="fw-semibold" style="font-size: 0.75rem; color: #334155;">
                                <?php echo timeAgo($githubProfile['last_fetched']); ?>
                            </span>
                        </div>
                        <a href="?sync=1" class="btn btn-outline-primary w-100 py-2 mb-2" 
                           style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; border: 1.5px solid #6366f1; color: #6366f1;">
                            <i class="fas fa-sync me-2"></i>Sync Now
                        </a>
                        <button onclick="confirmUnlink()" class="btn btn-outline-danger w-100 py-2" 
                                style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; border: 1.5px solid #ef4444; color: #ef4444;">
                            <i class="fas fa-unlink me-2"></i>Disconnect GitHub
                        </button>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <!-- Right Column - GitHub Profile -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="fw-bold mb-0" style="color: #1e293b; font-size: 0.95rem;">
                    <i class="fab fa-github me-2" style="color: #6366f1;"></i>GitHub Profile
                </h6>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                
                <?php if (!$githubUsername): ?>
                    <!-- No Username Case -->
                    <div class="text-center py-5">
                        <div style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                            <i class="fab fa-github text-muted" style="font-size: 2rem; opacity: 0.5;"></i>
                        </div>
                        <h5 class="fw-bold mb-1" style="color: #1e293b;">No GitHub Account Linked</h5>
                        <p class="text-muted small mb-0" style="max-width: 300px; margin: 0 auto;">
                            Enter your GitHub username on the left and sync to display your profile.
                        </p>
                    </div>
                    
                <?php elseif (!$githubProfile): ?>
                    <!-- Saved Username but Not Synced -->
                    <div class="text-center py-5">
                        <div style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                            <i class="fas fa-sync text-muted" style="font-size: 1.5rem; opacity: 0.5;"></i>
                        </div>
                        <h5 class="fw-bold mb-1" style="color: #1e293b;">Sync Your GitHub Data</h5>
                        <p class="text-muted small mb-3" style="max-width: 300px; margin: 0 auto;">
                            Click "Sync Now" to fetch your public GitHub profile and repositories.
                        </p>
                        <a href="?sync=1" class="btn btn-primary px-4 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
                            <i class="fas fa-sync me-2"></i>Sync Now
                        </a>
                    </div>
                    
                <?php else: ?>
                    <!-- Profile Display -->
                    <div class="d-flex align-items-start flex-column flex-sm-row">
                        <?php if ($githubProfile['avatar_url']): ?>
                            <img src="<?php echo sanitizeOutput($githubProfile['avatar_url']); ?>" 
                                 alt="GitHub Avatar" class="rounded-circle me-3 mb-3 mb-sm-0" 
                                 style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #f1f5f9;">
                        <?php endif; ?>
                        <div>
                            <h4 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.3rem;">
                                <?php echo sanitizeOutput($githubProfile['profile_name'] ?: $githubProfile['github_username']); ?>
                            </h4>
                            <p class="text-muted small mb-2">@<?php echo sanitizeOutput($githubProfile['github_username']); ?></p>
                            
                            <?php if ($githubProfile['bio']): ?>
                                <p class="text-muted small mb-2" style="font-size: 0.85rem; color: #475569;">
                                    <?php echo sanitizeOutput($githubProfile['bio']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($githubProfile['company'] || $githubProfile['location']): ?>
                                <div class="d-flex flex-wrap gap-3 text-muted small mb-2" style="font-size: 0.8rem;">
                                    <?php if ($githubProfile['company']): ?>
                                        <span><i class="fas fa-building me-1"></i> <?php echo sanitizeOutput($githubProfile['company']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($githubProfile['location']): ?>
                                        <span><i class="fas fa-map-marker-alt me-1"></i> <?php echo sanitizeOutput($githubProfile['location']); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="https://github.com/<?php echo sanitizeOutput($githubProfile['github_username']); ?>" 
                               target="_blank" class="btn btn-sm btn-dark mt-1" 
                               style="border-radius: 8px; font-size: 0.75rem; padding: 6px 14px; background: #0f172a; border: none;">
                                <i class="fab fa-github me-1"></i>Visit Profile
                            </a>
                        </div>
                    </div>
                    
                    <hr style="border-color: #f1f5f9; margin: 20px 0;">
                    
                    <!-- GitHub Stats -->
                    <div class="row g-2 mb-4">
                        <div class="col-4">
                            <div class="card border-0" style="background: #f8fafc; border-radius: 10px;">
                                <div class="card-body text-center py-3">
                                    <h5 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.2rem;"><?php echo $githubProfile['followers_count']; ?></h5>
                                    <small class="text-muted" style="font-size: 0.7rem;">Followers</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card border-0" style="background: #f8fafc; border-radius: 10px;">
                                <div class="card-body text-center py-3">
                                    <h5 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.2rem;"><?php echo $githubProfile['following_count']; ?></h5>
                                    <small class="text-muted" style="font-size: 0.7rem;">Following</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card border-0" style="background: #f8fafc; border-radius: 10px;">
                                <div class="card-body text-center py-3">
                                    <h5 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.2rem;"><?php echo $githubProfile['public_repos']; ?></h5>
                                    <small class="text-muted" style="font-size: 0.7rem;">Repositories</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Repositories -->
                    <?php if ($githubProfile['repo_data']): ?>
                        <h6 class="fw-bold mb-3" style="color: #1e293b; font-size: 0.9rem;">
                            <i class="fas fa-folder-open me-2" style="color: #6366f1;"></i>Recent Repositories
                        </h6>
                        <?php 
                            $repos = json_decode($githubProfile['repo_data'], true);
                            if ($repos && is_array($repos)):
                        ?>
                            <div class="row g-2">
                                <?php foreach (array_slice($repos, 0, 6) as $repo): ?>
                                    <div class="col-md-6">
                                        <a href="<?php echo sanitizeOutput($repo['html_url']); ?>" target="_blank" 
                                           class="card border-0 text-decoration-none" style="background: #f8fafc; border-radius: 10px; transition: all 0.2s ease;">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="fw-bold" style="font-size: 0.85rem; color: #1e293b;">
                                                        <i class="fab fa-github me-1" style="font-size: 0.7rem; color: #6366f1;"></i>
                                                        <?php echo sanitizeOutput($repo['name']); ?>
                                                    </div>
                                                    <?php if ($repo['stargazers_count'] > 0): ?>
                                                        <span class="badge fw-normal px-2 py-1" style="font-size: 0.6rem; border-radius: 6px; background: rgba(245, 158, 11, 0.1); color: #b45309;">
                                                            <i class="fas fa-star me-1"></i><?php echo $repo['stargazers_count']; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($repo['description']): ?>
                                                    <p class="text-muted small mt-1 mb-1" style="font-size: 0.75rem;">
                                                        <?php echo substr(sanitizeOutput($repo['description']), 0, 60); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if ($repo['language']): ?>
                                                    <span class="badge fw-normal px-2 py-1" style="font-size: 0.6rem; border-radius: 6px; background: white; color: #475569; border: 1px solid #e2e8f0;">
                                                        <?php echo sanitizeOutput($repo['language']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<!-- Unlink Confirmation Modal -->
<div class="modal fade" id="unlinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <div class="modal-body text-center p-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                     style="width: 64px; height: 64px; background: rgba(239, 68, 68, 0.1);">
                    <i class="fas fa-unlink" style="font-size: 1.5rem; color: #ef4444;"></i>
                </div>
                <h5 class="fw-bold mb-2" style="color: #1e293b;">Disconnect GitHub Account?</h5>
                <p class="text-muted small mb-4" style="font-size: 0.85rem;">
                    Are you sure you want to disconnect your GitHub account? 
                    Your GitHub profile data and repositories will be removed from your DevTrack account.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal" 
                            style="border-radius: 10px; font-size: 0.85rem; font-weight: 500; border: 1.5px solid #e2e8f0; color: #64748b;">
                        Cancel
                    </button>
                    <a href="?unlink=1" class="btn btn-danger px-4 py-2" 
                       style="border-radius: 10px; font-size: 0.85rem; font-weight: 500; background: #ef4444; border: none;">
                        <i class="fas fa-unlink me-2"></i>Yes, Disconnect
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmUnlink() {
    var modal = new bootstrap.Modal(document.getElementById('unlinkModal'));
    modal.show();
}
</script>

<style>
/* Repo card hover effect */
.card.border-0[style*="background: #f8fafc"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    border-color: #e2e8f0 !important;
}
</style>

<?php include_once '../includes/footer.php'; ?>
