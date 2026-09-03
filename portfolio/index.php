<?php
require_once '../config.php';

// Get username from URL
$username = $_GET['username'] ?? '';

// If no username provided, check if user is logged in
if (empty($username)) {
    if (isAuthenticated()) {
        // Redirect to logged-in user's portfolio
        $user = getUserById($_SESSION['user_id']);
        if ($user) {
            header('Location: ' . BASE_URL . 'portfolio/?username=' . urlencode($user['username']));
            exit();
        }
    } else {
        // If not logged in, show a nice message or redirect to login
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Portfolio - DevTrack</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            <style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .error-card {
                    background: white;
                    border-radius: 20px;
                    padding: 50px;
                    max-width: 500px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    text-align: center;
                }
                .error-card i {
                    font-size: 4rem;
                    color: #667eea;
                    margin-bottom: 20px;
                }
                .error-card h2 {
                    color: #333;
                    margin-bottom: 15px;
                }
                .error-card p {
                    color: #666;
                    margin-bottom: 25px;
                }
                .btn-primary {
                    background: #667eea;
                    border: none;
                    padding: 10px 30px;
                    border-radius: 50px;
                }
                .btn-primary:hover {
                    background: #5a6fd6;
                    transform: translateY(-2px);
                }
            </style>
        </head>
        <body>
            <div class="error-card">
                <i class="fas fa-user-circle"></i>
                <h2>Portfolio Viewer</h2>
                <p>Please specify a username to view their portfolio, or <a href="<?php echo BASE_URL; ?>login.php">login</a> to view your own.</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </a>
                </div>
                <hr class="my-4">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="username" class="form-control" placeholder="Enter username..." required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        exit();
    }
}

$user = null;
$projects = [];
$skills = [];
$goals = [];
$github = null;

if ($username) {
    $user = getUserByUsername($username);
    if ($user) {
        $userId = $user['id'];
        // Get only public projects
        $projects = fetchAll("
            SELECT * FROM projects 
            WHERE user_id = ? AND (is_public = 1 OR is_public IS NULL) 
            ORDER BY created_at DESC
        ", [$userId]);
        $skills = getUserSkills($userId);
        $goals = fetchAll("
            SELECT * FROM learning_goals 
            WHERE user_id = ? AND status = 'completed' 
            ORDER BY completed_at DESC LIMIT 5
        ", [$userId]);
        
        // Get GitHub profile
        $github = fetchOne("SELECT * FROM github_profiles WHERE user_id = ?", [$userId]);
    }
}

// If no user found, show error
if (!$user) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Not Found - DevTrack</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-card {
                background: white;
                border-radius: 20px;
                padding: 50px;
                max-width: 500px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
            }
            .error-card i {
                font-size: 4rem;
                color: #dc3545;
                margin-bottom: 20px;
            }
            .error-card h2 {
                color: #333;
                margin-bottom: 15px;
            }
            .error-card p {
                color: #666;
                margin-bottom: 25px;
            }
            .btn-primary {
                background: #667eea;
                border: none;
                padding: 10px 30px;
                border-radius: 50px;
            }
            .btn-primary:hover {
                background: #5a6fd6;
                transform: translateY(-2px);
            }
            .btn-secondary {
                border-radius: 50px;
                padding: 10px 30px;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <i class="fas fa-user-slash"></i>
            <h2>User Not Found</h2>
            <p>The username "<strong><?php echo sanitizeOutput($username); ?></strong>" does not exist in our system.</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Home
                </a>
                <a href="<?php echo BASE_URL; ?>users/" class="btn btn-secondary">
                    <i class="fas fa-users me-2"></i>Find Developers
                </a>
            </div>
            <hr class="my-4">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="text" name="username" class="form-control" placeholder="Try another username..." required>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
}

$page_title = ($user['full_name'] ?? $user['username']) . ' - Portfolio';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitizeOutput($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        
        .portfolio-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .portfolio-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        
        .portfolio-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.03);
            border-radius: 50%;
        }
        
        .portfolio-hero .avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        
        .portfolio-hero .avatar:hover {
            transform: scale(1.05);
        }
        
        .section-title {
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 30px;
            display: inline-block;
        }
        
        .social-links a {
            color: white;
            margin: 0 10px;
            font-size: 1.5rem;
            transition: transform 0.3s, color 0.3s;
            display: inline-block;
        }
        
        .social-links a:hover {
            transform: scale(1.2) translateY(-3px);
            color: #fff;
        }
        
        .project-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .skill-bar {
            margin-bottom: 15px;
        }
        
        .skill-bar .progress {
            height: 8px;
            border-radius: 10px;
        }
        
        .github-card {
            background: #f6f8fa;
            border: 1px solid #e1e4e8;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: background 0.3s;
        }
        
        .github-card:hover {
            background: #f3f4f6;
        }
        
        .github-stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .github-stats .stat-item {
            background: #f6f8fa;
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            min-width: 80px;
        }
        
        .github-stats .stat-item .number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .file-badge {
            background: #e3f2fd;
            color: #0d6efd;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
        }
        
        .download-btn {
            padding: 2px 10px;
            font-size: 0.75rem;
        }
        
        @media (max-width: 768px) {
            .portfolio-hero {
                padding: 50px 0;
            }
            .portfolio-hero .avatar {
                width: 100px;
                height: 100px;
            }
            .github-stats .stat-item {
                min-width: 60px;
                padding: 8px 12px;
            }
        }
        
        /* Dark mode support for portfolio */
        @media (prefers-color-scheme: dark) {
            body:not(.light-mode) .github-card {
                background: #1a1a2e;
                border-color: #2d2d44;
            }
            body:not(.light-mode) .github-stats .stat-item {
                background: #1a1a2e;
                border: 1px solid #2d2d44;
            }
            body:not(.light-mode) .file-badge {
                background: #1a1a2e;
                color: #6edff6;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="portfolio-hero text-center">
        <div class="container position-relative">
            <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo $user['avatar'] ?? 'default-avatar.png'; ?>" 
                 alt="Avatar" class="avatar rounded-circle mb-3">
            <h1 class="display-4"><?php echo sanitizeOutput($user['full_name'] ?: $user['username']); ?></h1>
            <p class="lead">@<?php echo sanitizeOutput($user['username']); ?></p>
            <?php if ($user['bio']): ?>
                <p class="mb-4" style="max-width: 600px; margin-left: auto; margin-right: auto;">
                    <?php echo nl2br(sanitizeOutput($user['bio'])); ?>
                </p>
            <?php endif; ?>
            
            <div class="social-links">
                <?php if ($user['github_username']): ?>
                    <a href="https://github.com/<?php echo sanitizeOutput($user['github_username']); ?>" target="_blank" title="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                <?php endif; ?>
                <?php if ($user['linkedin']): ?>
                    <a href="<?php echo sanitizeOutput($user['linkedin']); ?>" target="_blank" title="LinkedIn">
                        <i class="fab fa-linkedin"></i>
                    </a>
                <?php endif; ?>
                <?php if ($user['twitter']): ?>
                    <a href="<?php echo sanitizeOutput($user['twitter']); ?>" target="_blank" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                <?php endif; ?>
                <?php if ($user['website']): ?>
                    <a href="<?php echo sanitizeOutput($user['website']); ?>" target="_blank" title="Website">
                        <i class="fas fa-globe"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Skills Section -->
        <?php if (!empty($skills)): ?>
        <section class="mb-5">
            <h3 class="section-title"><i class="fas fa-code me-2"></i>Skills</h3>
            <div class="row">
                <?php foreach ($skills as $skill): ?>
                    <div class="col-md-6 skill-bar">
                        <div class="d-flex justify-content-between">
                            <span>
                                <?php if ($skill['icon']): ?>
                                    <i class="<?php echo $skill['icon']; ?> me-1"></i>
                                <?php endif; ?>
                                <?php echo sanitizeOutput($skill['name']); ?>
                            </span>
                            <span><?php echo $skill['proficiency']; ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-<?php echo getProgressColor($skill['proficiency']); ?>" 
                                 style="width: <?php echo $skill['proficiency']; ?>%">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Projects Section -->
        <?php if (!empty($projects)): ?>
        <section class="mb-5">
            <h3 class="section-title"><i class="fas fa-folder me-2"></i>Projects (<?php echo count($projects); ?>)</h3>
            <div class="row g-4">
                <?php foreach ($projects as $project): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card project-card h-100">
                            <?php if ($project['image']): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/projects/<?php echo $project['image']; ?>" 
                                     class="card-img-top" alt="<?php echo sanitizeOutput($project['title']); ?>" 
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-folder fa-5x text-white-50"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo sanitizeOutput($project['title']); ?></h5>
                                <?php if ($project['description']): ?>
                                    <p class="card-text text-muted small">
                                        <?php echo substr(sanitizeOutput($project['description']), 0, 120); ?>
                                        <?php if (strlen($project['description']) > 120): ?>...<?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($project['technologies']): ?>
                                    <div class="mb-2">
                                        <?php 
                                            $techs = explode(',', $project['technologies']);
                                            $displayTechs = array_slice($techs, 0, 3);
                                            foreach ($displayTechs as $tech):
                                        ?>
                                            <span class="badge bg-info"><?php echo trim(sanitizeOutput($tech)); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($techs) > 3): ?>
                                            <span class="badge bg-secondary">+<?php echo count($techs) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- File Badge -->
                                <?php if ($project['file_path']): ?>
                                    <div class="mt-2">
                                        <span class="file-badge">
                                            <i class="fas fa-file-archive me-1"></i> Files Available
                                        </span>
                                        <?php if ($project['allow_download']): ?>
                                            <a href="<?php echo BASE_URL; ?>projects/download.php?project=<?php echo $project['id']; ?>" 
                                               class="btn btn-sm btn-success download-btn" target="_blank">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                                <span class="badge bg-<?php echo getStatusBadge($project['status']); ?>">
                                    <?php echo str_replace('-', ' ', $project['status']); ?>
                                </span>
                                <?php if ($project['github_url'] || $project['demo_url']): ?>
                                    <div>
                                        <?php if ($project['github_url']): ?>
                                            <a href="<?php echo sanitizeOutput($project['github_url']); ?>" target="_blank" class="btn btn-dark btn-sm">
                                                <i class="fab fa-github"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($project['demo_url']): ?>
                                            <a href="<?php echo sanitizeOutput($project['demo_url']); ?>" target="_blank" class="btn btn-success btn-sm">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- GitHub Section -->
        <?php if ($github && $github['repo_data']): ?>
        <section class="mb-5">
            <h3 class="section-title"><i class="fab fa-github me-2"></i>GitHub Activity</h3>
            
            <!-- GitHub Stats -->
            <div class="github-stats mb-4">
                <div class="stat-item">
                    <div class="number"><?php echo $github['followers_count']; ?></div>
                    <small class="text-muted">Followers</small>
                </div>
                <div class="stat-item">
                    <div class="number"><?php echo $github['following_count']; ?></div>
                    <small class="text-muted">Following</small>
                </div>
                <div class="stat-item">
                    <div class="number"><?php echo $github['public_repos']; ?></div>
                    <small class="text-muted">Repositories</small>
                </div>
            </div>
            
            <?php 
                $repos = json_decode($github['repo_data'], true);
                if ($repos && is_array($repos)):
            ?>
                <div class="row">
                    <?php foreach (array_slice($repos, 0, 6) as $repo): ?>
                        <div class="col-md-6">
                            <a href="<?php echo sanitizeOutput($repo['html_url']); ?>" target="_blank" class="text-decoration-none">
                                <div class="github-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?php echo sanitizeOutput($repo['name']); ?></strong>
                                            <?php if ($repo['description']): ?>
                                                <br>
                                                <small class="text-muted"><?php echo substr(sanitizeOutput($repo['description']), 0, 60); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($repo['stargazers_count'] > 0): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-star"></i> <?php echo $repo['stargazers_count']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($repo['language']): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-secondary"><?php echo sanitizeOutput($repo['language']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <!-- Learning Journey -->
        <?php if (!empty($goals)): ?>
        <section class="mb-5">
            <h3 class="section-title"><i class="fas fa-graduation-cap me-2"></i>Learning Journey</h3>
            <div class="row g-3">
                <?php foreach ($goals as $goal): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo sanitizeOutput($goal['title']); ?></h6>
                                <?php if ($goal['description']): ?>
                                    <p class="card-text small text-muted">
                                        <?php echo substr(sanitizeOutput($goal['description']), 0, 80); ?>
                                    </p>
                                <?php endif; ?>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: 100%">
                                        <i class="fas fa-check me-1"></i> Completed
                                    </div>
                                </div>
                                <?php if ($goal['completed_at']): ?>
                                    <small class="text-muted">Completed: <?php echo formatDate($goal['completed_at']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Footer -->
        <footer class="text-center text-muted py-4 border-top">
            <p class="mb-0">
                &copy; <?php echo date('Y'); ?> <?php echo sanitizeOutput($user['full_name'] ?: $user['username']); ?>. 
                Built with <i class="fas fa-heart text-danger"></i> using <a href="<?php echo BASE_URL; ?>" class="text-decoration-none">DevTrack</a>
            </p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Apply dark mode based on system preference -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if user has theme preference in localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        } else if (savedTheme === 'light') {
            document.body.classList.add('light-mode');
        } else {
            // Check system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.body.classList.add('dark-mode');
            }
        }
    });
    </script>
</body>
</html>