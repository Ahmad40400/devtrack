<?php
require_once '../config.php';

// Get username from URL
$username = $_GET['username'] ?? '';

// If no username provided, check if user is logged in
if (empty($username)) {
    if (isAuthenticated()) {
        $user = getUserById($_SESSION['user_id']);
        if ($user) {
            header('Location: ' . BASE_URL . 'portfolio/?username=' . urlencode($user['username']));
            exit();
        }
    } else {
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
                    background: #f8fafc;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
                }
                .card {
                    background: white;
                    border-radius: 20px;
                    padding: 50px;
                    max-width: 480px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.06);
                    text-align: center;
                }
                .card .icon {
                    width: 72px;
                    height: 72px;
                    background: rgba(99,102,241,0.1);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 20px;
                }
                .card .icon i {
                    font-size: 2rem;
                    color: #6366f1;
                }
                .card h2 {
                    color: #1e293b;
                    font-weight: 700;
                    font-size: 1.5rem;
                    margin-bottom: 8px;
                }
                .card p {
                    color: #64748b;
                    font-size: 0.95rem;
                    margin-bottom: 24px;
                }
                .btn-primary {
                    background: #6366f1;
                    border: none;
                    padding: 10px 28px;
                    border-radius: 10px;
                    font-weight: 600;
                    font-size: 0.9rem;
                }
                .btn-primary:hover {
                    background: #4f46e5;
                    transform: translateY(-1px);
                    box-shadow: 0 8px 25px rgba(99,102,241,0.25);
                }
                .btn-outline {
                    border: 2px solid #e2e8f0;
                    background: transparent;
                    padding: 10px 28px;
                    border-radius: 10px;
                    font-weight: 600;
                    font-size: 0.9rem;
                    color: #475569;
                    text-decoration: none;
                    display: inline-block;
                }
                .btn-outline:hover {
                    background: #f8fafc;
                    border-color: #cbd5e1;
                }
                .search-input {
                    border-radius: 10px;
                    padding: 10px 16px;
                    border: 2px solid #e2e8f0;
                    font-size: 0.9rem;
                }
                .search-input:focus {
                    border-color: #6366f1;
                    box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
                }
                .divider {
                    border: none;
                    border-top: 1px solid #f1f5f9;
                    margin: 24px 0;
                }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="icon">
                    <i class="fas fa-user"></i>
                </div>
                <h2>Developer Portfolio</h2>
                <p>View any developer's work by searching their username</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap mb-3">
                    <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>register.php" class="btn-outline">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </a>
                </div>
                <hr class="divider">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="username" class="form-control search-input" placeholder="Enter username..." required>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-arrow-right"></i>
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
        $projects = fetchAll("
            SELECT * FROM projects 
            WHERE user_id = ? AND (is_public = 1 OR is_public IS NULL) 
            ORDER BY created_at DESC
        ", [$userId]);
        $skills = getUserSkills($userId);
        $goals = fetchAll("
            SELECT * FROM learning_goals 
            WHERE user_id = ? AND status = 'completed' 
            ORDER BY completed_at DESC LIMIT 6
        ", [$userId]);
        $github = fetchOne("SELECT * FROM github_profiles WHERE user_id = ?", [$userId]);
    }
}

if (!$user) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Not Found</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                background: #f8fafc;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            }
            .card {
                background: white;
                border-radius: 20px;
                padding: 50px;
                max-width: 480px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.06);
                text-align: center;
            }
            .card .icon {
                width: 72px;
                height: 72px;
                background: rgba(239,68,68,0.1);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
            }
            .card .icon i {
                font-size: 2rem;
                color: #ef4444;
            }
            .card h2 {
                color: #1e293b;
                font-weight: 700;
                font-size: 1.5rem;
                margin-bottom: 8px;
            }
            .card p {
                color: #64748b;
                font-size: 0.95rem;
                margin-bottom: 24px;
            }
            .btn-primary {
                background: #6366f1;
                border: none;
                padding: 10px 28px;
                border-radius: 10px;
                font-weight: 600;
            }
            .btn-primary:hover {
                background: #4f46e5;
                transform: translateY(-1px);
                box-shadow: 0 8px 25px rgba(99,102,241,0.25);
            }
            .btn-outline {
                border: 2px solid #e2e8f0;
                background: transparent;
                padding: 10px 28px;
                border-radius: 10px;
                font-weight: 600;
                color: #475569;
                text-decoration: none;
                display: inline-block;
            }
            .btn-outline:hover {
                background: #f8fafc;
                border-color: #cbd5e1;
            }
            .search-input {
                border-radius: 10px;
                padding: 10px 16px;
                border: 2px solid #e2e8f0;
                font-size: 0.9rem;
            }
            .search-input:focus {
                border-color: #6366f1;
                box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
            }
            .divider {
                border: none;
                border-top: 1px solid #f1f5f9;
                margin: 24px 0;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <h2>User Not Found</h2>
            <p>The username "<strong><?php echo sanitizeOutput($username); ?></strong>" does not exist.</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap mb-3">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Home
                </a>
                <a href="<?php echo BASE_URL; ?>users/" class="btn-outline">
                    <i class="fas fa-users me-2"></i>Find Developers
                </a>
            </div>
            <hr class="divider">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="text" name="username" class="form-control search-input" placeholder="Try another..." required>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-arrow-right"></i>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #fafafa;
            color: #1e293b;
            line-height: 1.6;
        }
        
        /* Hero Section */
        .hero {
            background: #0f172a;
            color: #ffffff;
            padding: 70px 0 50px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #6366f1, #a855f7, #6366f1);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .hero .container {
            position: relative;
            z-index: 1;
        }
        
        .hero-avatar {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.1);
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        
        .hero .badge-role {
            display: inline-block;
            background: rgba(99,102,241,0.2);
            color: #a5b4fc;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border: 1px solid rgba(99,102,241,0.15);
        }
        
        .hero-name {
            font-size: 2.4rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 6px 0 2px;
            background: linear-gradient(135deg, #ffffff 40%, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-username {
            color: rgba(255,255,255,0.5);
            font-size: 0.95rem;
        }
        
        .hero-bio {
            color: rgba(255,255,255,0.7);
            max-width: 500px;
            margin: 12px auto 0;
            font-size: 0.9rem;
            line-height: 1.7;
            font-weight: 300;
        }
        
        .hero-social a {
            color: rgba(255,255,255,0.4);
            font-size: 1.1rem;
            transition: all 0.25s ease;
            display: inline-block;
        }
        
        .hero-social a:hover {
            color: #ffffff;
            transform: translateY(-3px);
        }
        
        /* Section */
        .section {
            padding: 40px 0;
        }
        
        .section:not(:last-child) {
            border-bottom: 1px solid #f1f5f9;
        }
        
        .section-title {
            font-size: 0.7rem;
            font-weight: 600;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 20px;
        }
        
        .section-title .line {
            display: inline-block;
            width: 30px;
            height: 2px;
            background: #6366f1;
            margin-right: 10px;
            vertical-align: middle;
        }
        
        /* Skills */
        .skill-item {
            margin-bottom: 14px;
        }
        .skill-item .skill-label {
            font-size: 0.78rem;
            font-weight: 500;
            color: #1e293b;
        }
        .skill-item .skill-value {
            font-size: 0.65rem;
            color: #94a3b8;
            font-weight: 500;
        }
        .skill-item .progress {
            height: 4px;
            border-radius: 4px;
            background: #f1f5f9;
            margin-top: 4px;
        }
        .skill-item .progress-bar {
            border-radius: 4px;
            background: linear-gradient(90deg, #6366f1, #a855f7);
        }
        
        /* Projects */
        .project-card {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .project-card:hover {
            transform: translateY(-6px);
            border-color: #e2e8f0;
            box-shadow: 0 20px 50px rgba(0,0,0,0.06);
        }
        
        .project-card .card-img-top {
            height: 170px;
            object-fit: cover;
            background: #f8fafc;
        }
        
        .project-card .card-body {
            padding: 18px 20px 14px;
        }
        
        .project-card .card-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 4px;
        }
        
        .project-card .card-text {
            font-size: 0.75rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        
        .project-card .tech-tag {
            font-size: 0.6rem;
            padding: 3px 12px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #475569;
            font-weight: 500;
        }
        
        .project-card .card-footer {
            background: transparent;
            border-top: 1px solid #f1f5f9;
            padding: 10px 20px 14px;
        }
        
        .project-card .status-badge {
            font-size: 0.6rem;
            padding: 3px 12px;
            border-radius: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .project-card .file-badge {
            font-size: 0.6rem;
            padding: 2px 10px;
            border-radius: 12px;
            background: #eef2ff;
            color: #6366f1;
            font-weight: 500;
        }
        
        .project-card .btn-icon {
            width: 28px;
            height: 28px;
            padding: 0;
            border-radius: 8px;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* GitHub */
        .github-stats {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .github-stat {
            background: white;
            border: 1px solid #f1f5f9;
            padding: 14px 24px;
            border-radius: 12px;
            text-align: center;
            flex: 1;
            min-width: 80px;
        }
        
        .github-stat .number {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
        }
        
        .github-stat .label {
            font-size: 0.6rem;
            color: #94a3b8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        
        .github-repo {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 14px 18px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }
        
        .github-repo:hover {
            border-color: #e2e8f0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        }
        
        .github-repo .repo-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #0f172a;
        }
        
        .github-repo .repo-desc {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 2px;
        }
        
        .github-repo .repo-meta {
            font-size: 0.65rem;
            color: #94a3b8;
        }
        
        .github-repo .repo-lang {
            font-size: 0.6rem;
            padding: 2px 10px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #475569;
            font-weight: 500;
        }
        
        /* Learning */
        .learning-badge {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        /* Footer */
        .footer {
            border-top: 1px solid #f1f5f9;
            padding: 30px 0;
            text-align: center;
            color: #94a3b8;
            font-size: 0.8rem;
        }
        
        .footer a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 50px 0 40px;
            }
            .hero-name {
                font-size: 1.8rem;
            }
            .hero-avatar {
                width: 85px;
                height: 85px;
            }
            .github-stat {
                padding: 10px 16px;
                min-width: 60px;
            }
            .github-stat .number {
                font-size: 1rem;
            }
            .project-card .card-img-top {
                height: 140px;
            }
        }
    </style>
</head>
<body>

<!-- Hero Section -->
<section class="hero text-center">
    <div class="container">
        <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo $user['avatar'] ?? 'default-avatar.png'; ?>" 
             alt="Avatar" class="hero-avatar mb-3">
        
        <div class="badge-role mb-2">Developer Portfolio</div>
        
        <h1 class="hero-name"><?php echo sanitizeOutput($user['full_name'] ?: $user['username']); ?></h1>
        <p class="hero-username">@<?php echo sanitizeOutput($user['username']); ?></p>
        
        <?php if ($user['bio']): ?>
            <p class="hero-bio"><?php echo nl2br(sanitizeOutput($user['bio'])); ?></p>
        <?php endif; ?>
        
        <div class="hero-social mt-3">
            <?php if ($user['github_username']): ?>
                <a href="https://github.com/<?php echo sanitizeOutput($user['github_username']); ?>" target="_blank" class="mx-2" title="GitHub">
                    <i class="fab fa-github"></i>
                </a>
            <?php endif; ?>
            <?php if ($user['linkedin']): ?>
                <a href="<?php echo sanitizeOutput($user['linkedin']); ?>" target="_blank" class="mx-2" title="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            <?php endif; ?>
            <?php if ($user['twitter']): ?>
                <a href="<?php echo sanitizeOutput($user['twitter']); ?>" target="_blank" class="mx-2" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
            <?php endif; ?>
            <?php if ($user['website']): ?>
                <a href="<?php echo sanitizeOutput($user['website']); ?>" target="_blank" class="mx-2" title="Website">
                    <i class="fas fa-globe"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Content -->
<div class="container">

    <!-- Skills -->
    <?php if (!empty($skills)): ?>
    <section class="section">
        <div class="section-title">
            <span class="line"></span>Skills & Expertise
        </div>
        <div class="row">
            <?php foreach ($skills as $skill): ?>
                <div class="col-md-6 skill-item">
                    <div class="d-flex justify-content-between">
                        <span class="skill-label">
                            <?php if ($skill['icon']): ?>
                                <i class="<?php echo $skill['icon']; ?> me-1" style="font-size: 0.75rem; color: #6366f1;"></i>
                            <?php endif; ?>
                            <?php echo sanitizeOutput($skill['name']); ?>
                        </span>
                        <span class="skill-value"><?php echo $skill['proficiency']; ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo $skill['proficiency']; ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Projects -->
    <?php if (!empty($projects)): ?>
    <section class="section">
        <div class="section-title">
            <span class="line"></span>Featured Projects
            <span style="font-size: 0.65rem; color: #94a3b8; font-weight: 400; text-transform: none; letter-spacing: 0;">
                (<?php echo count($projects); ?>)
            </span>
        </div>
        <div class="row g-3">
            <?php foreach ($projects as $project): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="project-card">
                        <?php if ($project['image']): ?>
                            <img src="<?php echo BASE_URL; ?>uploads/projects/<?php echo $project['image']; ?>" 
                                 class="card-img-top" alt="<?php echo sanitizeOutput($project['title']); ?>">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 170px; background: #f8fafc;">
                                <i class="fas fa-folder-open" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h6 class="card-title"><?php echo sanitizeOutput($project['title']); ?></h6>
                            <?php if ($project['description']): ?>
                                <p class="card-text"><?php echo substr(sanitizeOutput($project['description']), 0, 75); ?></p>
                            <?php endif; ?>
                            <?php if ($project['technologies']): ?>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php 
                                        $techs = explode(',', $project['technologies']);
                                        foreach (array_slice($techs, 0, 4) as $tech):
                                    ?>
                                        <span class="tech-tag"><?php echo trim(sanitizeOutput($tech)); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($techs) > 4): ?>
                                        <span class="tech-tag">+<?php echo count($techs) - 4; ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($project['file_path']): ?>
                                <div class="mt-2">
                                    <span class="file-badge">
                                        <i class="fas fa-paperclip me-1"></i> Files
                                    </span>
                                    <?php if ($project['allow_download']): ?>
                                        <a href="<?php echo BASE_URL; ?>projects/download.php?project=<?php echo $project['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary ms-1" style="padding: 0 8px; font-size: 0.6rem; border-radius: 6px;" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <span class="status-badge bg-<?php echo getStatusBadge($project['status']); ?> text-white">
                                <?php echo str_replace('-', ' ', $project['status']); ?>
                            </span>
                            <div class="d-flex gap-1">
                                <?php if ($project['github_url']): ?>
                                    <a href="<?php echo sanitizeOutput($project['github_url']); ?>" target="_blank" 
                                       class="btn btn-sm btn-dark btn-icon">
                                        <i class="fab fa-github"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($project['demo_url']): ?>
                                    <a href="<?php echo sanitizeOutput($project['demo_url']); ?>" target="_blank" 
                                       class="btn btn-sm btn-success btn-icon">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- GitHub -->
    <?php if ($github && $github['repo_data']): 
        $repos = json_decode($github['repo_data'], true);
    ?>
    <section class="section">
        <div class="section-title">
            <span class="line"></span>GitHub Activity
        </div>
        
        <div class="github-stats mb-3">
            <div class="github-stat">
                <div class="number"><?php echo $github['followers_count']; ?></div>
                <div class="label">Followers</div>
            </div>
            <div class="github-stat">
                <div class="number"><?php echo $github['following_count']; ?></div>
                <div class="label">Following</div>
            </div>
            <div class="github-stat">
                <div class="number"><?php echo $github['public_repos']; ?></div>
                <div class="label">Repositories</div>
            </div>
        </div>
        
        <?php if ($repos && is_array($repos)): ?>
            <div class="row">
                <?php foreach (array_slice($repos, 0, 6) as $repo): ?>
                    <div class="col-md-6">
                        <a href="<?php echo sanitizeOutput($repo['html_url']); ?>" target="_blank" class="github-repo">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="repo-name"><?php echo sanitizeOutput($repo['name']); ?></span>
                                    <?php if ($repo['description']): ?>
                                        <div class="repo-desc"><?php echo substr(sanitizeOutput($repo['description']), 0, 60); ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($repo['stargazers_count'] > 0): ?>
                                    <span class="repo-meta">
                                        <i class="fas fa-star" style="color: #f59e0b;"></i> <?php echo $repo['stargazers_count']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($repo['language']): ?>
                                <span class="repo-lang mt-1"><?php echo sanitizeOutput($repo['language']); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- Learning -->
    <?php if (!empty($goals)): ?>
    <section class="section">
        <div class="section-title">
            <span class="line"></span>Learning Journey
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($goals as $goal): ?>
                <span class="learning-badge">
                    <i class="fas fa-check-circle" style="font-size: 0.7rem;"></i>
                    <?php echo sanitizeOutput($goal['title']); ?>
                </span>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<!-- Footer -->
<div class="footer">
    <div class="container">
        <p class="mb-0">
            &copy; <?php echo date('Y'); ?> <?php echo sanitizeOutput($user['full_name'] ?: $user['username']); ?> &middot;
            Built with <i class="fas fa-heart" style="color: #ef4444; font-size: 0.75rem;"></i> using 
            <a href="<?php echo BASE_URL; ?>">DevTrack</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
