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

// Get fresh profile data after sync
$githubProfile = fetchOne("SELECT * FROM github_profiles WHERE user_id = ?", [$userId]);

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">GitHub Settings</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label">GitHub Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fab fa-github"></i></span>
                            <input type="text" name="github_username" class="form-control" 
                                   value="<?php echo sanitizeOutput($githubUsername); ?>" 
                                   placeholder="Enter your GitHub username">
                        </div>
                    </div>
                    <button type="submit" name="save_username" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-2"></i>Save Username
                    </button>
                </form>
                
                <?php if ($githubUsername): ?>
                    <a href="?sync=1" class="btn btn-success w-100">
                        <i class="fas fa-sync me-2"></i>Sync GitHub Data
                    </a>
                    <?php if ($githubProfile && $githubProfile['last_fetched']): ?>
                        <small class="text-muted d-block mt-2">
                            Last synced: <?php echo timeAgo($githubProfile['last_fetched']); ?>
                        </small>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">GitHub Profile</h5>
            </div>
            <div class="card-body">
                <?php if (!$githubUsername): ?>
                    <div class="text-center py-5">
                        <i class="fab fa-github fa-4x text-muted mb-3"></i>
                        <h4>No GitHub Account Linked</h4>
                        <p class="text-muted">Enter your GitHub username and sync to display your profile.</p>
                    </div>
                <?php elseif (!$githubProfile): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-sync fa-4x text-muted mb-3"></i>
                        <h4>Sync Your GitHub Data</h4>
                        <p class="text-muted">Click "Sync GitHub Data" to fetch your public profile.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex align-items-start">
                        <?php if ($githubProfile['avatar_url']): ?>
                            <img src="<?php echo sanitizeOutput($githubProfile['avatar_url']); ?>" 
                                 alt="GitHub Avatar" class="rounded-circle me-3" style="width: 80px; height: 80px;">
                        <?php endif; ?>
                        <div>
                            <h4><?php echo sanitizeOutput($githubProfile['profile_name'] ?: $githubProfile['github_username']); ?></h4>
                            <p class="text-muted">@<?php echo sanitizeOutput($githubProfile['github_username']); ?></p>
                            <?php if ($githubProfile['bio']): ?>
                                <p><?php echo sanitizeOutput($githubProfile['bio']); ?></p>
                            <?php endif; ?>
                            <div class="d-flex gap-3">
                                <span><i class="fas fa-users me-1"></i> <?php echo $githubProfile['followers_count']; ?> followers</span>
                                <span><i class="fas fa-user-friends me-1"></i> <?php echo $githubProfile['following_count']; ?> following</span>
                                <span><i class="fas fa-folder me-1"></i> <?php echo $githubProfile['public_repos']; ?> repos</span>
                            </div>
                            <?php if ($githubProfile['company']): ?>
                                <p class="mt-2"><i class="fas fa-building me-1"></i> <?php echo sanitizeOutput($githubProfile['company']); ?></p>
                            <?php endif; ?>
                            <?php if ($githubProfile['location']): ?>
                                <p><i class="fas fa-map-marker-alt me-1"></i> <?php echo sanitizeOutput($githubProfile['location']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($githubProfile['repo_data']): ?>
                        <hr>
                        <h6 class="mt-3">Recent Repositories</h6>
                        <?php 
                            $repos = json_decode($githubProfile['repo_data'], true);
                            if ($repos && is_array($repos)):
                        ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($repos, 0, 5) as $repo): ?>
                                    <a href="<?php echo sanitizeOutput($repo['html_url']); ?>" target="_blank" 
                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold"><?php echo sanitizeOutput($repo['name']); ?></div>
                                            <?php if ($repo['description']): ?>
                                                <small class="text-muted"><?php echo substr(sanitizeOutput($repo['description']), 0, 80); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <?php if ($repo['stargazers_count'] > 0): ?>
                                                <span class="badge bg-warning me-1">
                                                    <i class="fas fa-star"></i> <?php echo $repo['stargazers_count']; ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($repo['language']): ?>
                                                <span class="badge bg-info"><?php echo sanitizeOutput($repo['language']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>