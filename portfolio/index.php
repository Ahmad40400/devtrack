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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        body {
            background: #ffffff;
            color: #1e293b;
        }
        
        /* Navbar */
        .portfolio-nav {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #f1f5f9;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 10px rgba(0,0,0,0.03);
        }
        
        .portfolio-nav .nav-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .portfolio-nav .nav-brand {
            font-weight: 800;
            font-size: 1.2rem;
            color: #4f46e5;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        
        .portfolio-nav .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .portfolio-nav .nav-link {
            color: #64748b;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            transition: color 0.2s ease;
            position: relative;
        }
        
        .portfolio-nav .nav-link:hover {
            color: #4f46e5;
        }
        
        .portfolio-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 0;
            height: 2px;
            background: #4f46e5;
            transition: width 0.3s ease;
        }
        
        .portfolio-nav .nav-link:hover::after {
            width: 100%;
        }
        
        .portfolio-nav .nav-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #64748b;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .portfolio-nav .nav-links {
                display: none;
                position: absolute;
                top: 60px;
                left: 0;
                right: 0;
                background: white;
                padding: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                flex-direction: column;
                gap: 10px;
            }
            
            .portfolio-nav .nav-links.active {
                display: flex;
            }
            
            .portfolio-nav .nav-toggle {
                display: block;
            }
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
            color: white;
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        
        .hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.03);
            border-radius: 50%;
        }
        
        .hero .container {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .hero-avatar {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.2);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        
        .hero-name {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-top: 20px;
            margin-bottom: 5px;
        }
        
        .hero-username {
            color: rgba(255,255,255,0.6);
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .hero-bio {
            color: rgba(255,255,255,0.85);
            max-width: 600px;
            margin: 20px auto 0;
            font-size: 1.05rem;
            line-height: 1.8;
        }
        
        .hero-default-bio {
            color: rgba(255,255,255,0.7);
            max-width: 600px;
            margin: 20px auto 0;
            font-size: 1rem;
            line-height: 1.7;
            font-style: italic;
        }
        
        .hero-social a {
            color: rgba(255,255,255,0.7);
            font-size: 1.3rem;
            margin: 0 10px;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
        }
        
        .hero-social a:hover {
            color: white;
            transform: translateY(-3px);
        }
        
        /* Section */
        .section {
            padding: 70px 0;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.2rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .section-divider {
            width: 60px;
            height: 4px;
            background: #4f46e5;
            border-radius: 10px;
            margin: 15px auto 40px;
        }
        
        /* Skill Cards */
        .skill-card {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .skill-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-color: #e2e8f0;
        }
        
        .skill-card .skill-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
        }
        
        .skill-card h5 {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 8px;
        }
        
        .skill-card .skill-level {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .skill-card .skill-category {
            color: #64748b;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }
        
        .skill-card .progress {
            height: 6px;
            border-radius: 10px;
            background: #f1f5f9;
        }
        
        .skill-card .progress-bar {
            border-radius: 10px;
        }
        
        /* Project Cards */
        .project-card {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .project-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }
        
        .project-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .project-card .card-body {
            padding: 20px;
        }
        
        .project-card .card-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .project-card .card-text {
            color: #64748b;
            font-size: 0.88rem;
            line-height: 1.6;
        }
        
        .project-card .tech-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 15px;
        }
        
        .project-card .tech-tag {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .project-card .card-footer {
            background: transparent;
            border-top: 1px solid #f1f5f9;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .project-card .status-badge {
            font-size: 0.7rem;
            padding: 4px 12px;
            border-radius: 20px;
        }
        
        /* GitHub Stats */
        .github-stats {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .github-stat {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 20px 30px;
            text-align: center;
            min-width: 120px;
        }
        
        .github-stat .number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e293b;
        }
        
        .github-stat .label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        /* Learning Badges */
        .learning-badge {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 5px;
        }
        
        /* Footer */
        .footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 40px 0;
            text-align: center;
        }
        
        .footer a {
            color: #6366f1;
            text-decoration: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-name {
                font-size: 2rem;
            }
            .hero-avatar {
                width: 90px;
                height: 90px;
            }
            .project-card .card-img-top {
                height: 160px;
            }
            .section-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="portfolio-nav">
    <div class="nav-container">
        <span class="nav-brand">PORTFOLIO</span>
        <button class="nav-toggle" onclick="toggleNav()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="nav-links" id="navLinks">
            <a href="#home" class="nav-link">Home</a>
            <a href="#skills" class="nav-link">Skills</a>
            <a href="#projects" class="nav-link">Projects</a>
            <a href="#contact" class="nav-link">Contact</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero text-center" id="home">
    <div class="container">
        <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo $user['avatar'] ?? 'default-avatar.png'; ?>" 
             alt="Avatar" class="hero-avatar">
        <h1 class="hero-name"><?php echo sanitizeOutput($user['full_name'] ?: $user['username']); ?></h1>
        <p class="hero-username">@<?php echo sanitizeOutput($user['username']); ?></p>
        
        <?php if ($user['bio']): ?>
            <p class="hero-bio"><?php echo nl2br(sanitizeOutput($user['bio'])); ?></p>
        <?php else: ?>
            <p class="hero-default-bio">
                👋 Hi! I'm <?php echo sanitizeOutput($user['full_name'] ?: $user['username']); ?>, a passionate developer.
                I love building creative solutions and exploring new technologies.
                Check out my projects below! 🚀
            </p>
        <?php endif; ?>
        
        <div class="hero-social mt-4">
            <?php if ($user['github_username']): ?>
                <a href="https://github.com/<?php echo sanitizeOutput($user['github_username']); ?>" target="_blank" title="GitHub">
                    <i class="fab fa-github"></i>
                </a>
            <?php endif; ?>
            <?php if ($user['website']): ?>
                <a href="<?php echo sanitizeOutput($user['website']); ?>" target="_blank" title="Website">
                    <i class="fas fa-globe"></i>
                </a>
            <?php endif; ?>
            <?php if ($user['linkedin']): ?>
                <a href="<?php echo sanitizeOutput($user['linkedin']); ?>" target="_blank" title="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            <?php endif; ?>
            <?php if ($user['twitter']): ?>
                <a href="<?php echo sanitizeOutput($user['twitter']); ?>" target="_blank" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Skills Section -->
<section class="section" id="skills">
    <div class="container">
        <h2 class="section-title">My Skills</h2>
        <div class="section-divider"></div>
        
        <?php if (!empty($skills)): ?>
            <div class="row g-4">
                <?php foreach ($skills as $skill): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="skill-card">
                            <div class="skill-icon" style="background: rgba(99,102,241,0.1);">
                                <?php if ($skill['icon']): ?>
                                    <i class="<?php echo $skill['icon']; ?>" style="color: #6366f1;"></i>
                                <?php else: ?>
                                    <i class="fas fa-code" style="color: #6366f1;"></i>
                                <?php endif; ?>
                            </div>
                            <h5><?php echo sanitizeOutput($skill['name']); ?></h5>
                            <div class="skill-level" style="color: <?php echo $skill['proficiency'] >= 80 ? '#10b981' : ($skill['proficiency'] >= 50 ? '#f59e0b' : '#ef4444'); ?>;">
                                <?php echo $skill['proficiency']; ?>%
                            </div>
                            <div class="skill-category"><?php echo ucfirst($skill['experience_level']); ?></div>
                            <div class="progress">
                                <div class="progress-bar" 
                                     style="width: <?php echo $skill['proficiency']; ?>%; background: linear-gradient(90deg, #6366f1, #a855f7);">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No skills added yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Projects Section -->
<section class="section" id="projects" style="background: #f8fafc;">
    <div class="container">
        <h2 class="section-title">My Projects</h2>
        <div class="section-divider"></div>
        
        <?php if (!empty($projects)): ?>
            <div class="row g-4">
                <?php foreach ($projects as $project): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="project-card">
                            <?php if ($project['image']): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/projects/<?php echo $project['image']; ?>" 
                                     class="card-img-top" alt="<?php echo sanitizeOutput($project['title']); ?>">
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center" style="background: #f1f5f9;">
                                    <i class="fas fa-folder-open" style="font-size: 3rem; color: #cbd5e1;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo sanitizeOutput($project['title']); ?></h5>
                                <?php if ($project['description']): ?>
                                    <p class="card-text"><?php echo substr(sanitizeOutput($project['description']), 0, 100); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($project['technologies']): ?>
                                    <div class="tech-tags">
                                        <?php 
                                            $techs = explode(',', $project['technologies']);
                                            foreach (array_slice($techs, 0, 4) as $tech):
                                        ?>
                                            <span class="tech-tag"><?php echo trim(sanitizeOutput($tech)); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer">
                                <span class="status-badge bg-<?php echo getStatusBadge($project['status']); ?> text-white">
                                    <?php echo str_replace('-', ' ', $project['status']); ?>
                                </span>
                                <div class="d-flex gap-2">
                                    <?php if ($project['github_url']): ?>
                                        <a href="<?php echo sanitizeOutput($project['github_url']); ?>" target="_blank" class="btn btn-sm btn-dark">
                                            <i class="fab fa-github"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($project['demo_url']): ?>
                                        <a href="<?php echo sanitizeOutput($project['demo_url']); ?>" target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($project['file_path'] && $project['allow_download']): ?>
                                        <a href="<?php echo BASE_URL; ?>projects/download.php?project=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No projects yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- GitHub Section -->
<?php if ($github): ?>
<section class="section" id="github">
    <div class="container">
        <h2 class="section-title">GitHub Activity</h2>
        <div class="section-divider"></div>
        
        <div class="github-stats mb-4">
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
    </div>
</section>
<?php endif; ?>

<!-- Learning Section -->
<?php if (!empty($goals)): ?>
<section class="section bg-light" style="background: #f8fafc;">
    <div class="container">
        <h2 class="section-title">Learning Journey</h2>
        <div class="section-divider"></div>
        <div class="text-center">
            <?php foreach ($goals as $goal): ?>
                <span class="learning-badge">
                    <i class="fas fa-check-circle"></i>
                    <?php echo sanitizeOutput($goal['title']); ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Footer -->
<div class="footer" id="contact">
    <div class="container">
        <p>
            &copy; <?php echo date('Y'); ?> <?php echo sanitizeOutput($user['full_name'] ?: $user['username']); ?> |
            Built with <i class="fas fa-heart" style="color: #ef4444;"></i> using <a href="<?php echo BASE_URL; ?>">DevTrack</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleNav() {
    document.getElementById('navLinks').classList.toggle('active');
}
</script>
</body>
</html>