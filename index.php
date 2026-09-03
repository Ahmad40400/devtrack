<?php
require_once __DIR__ . '/config.php';

// Redirect to dashboard or login
if (isAuthenticated()) {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit();
}

// ============================================
// GET REAL STATS FROM DATABASE
// ============================================
$totalDevelopers = fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'user'");
$totalProjects = fetchColumn("SELECT COUNT(*) FROM projects");
$totalTasks = fetchColumn("SELECT COUNT(*) FROM tasks WHERE status = 'completed'");

$page_title = APP_NAME . ' - Developer Portfolio & Management Platform';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitizeOutput($page_title); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ===== RESET & BASE ===== */
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #ffffff;
            color: #1e293b;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        
        /* ===== NAVBAR ===== */
        .navbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid #f1f5f9;
            padding: 0.8rem 0;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
        }
        
        .navbar-brand .brand-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .navbar-brand .brand-icon i {
            color: white;
            font-size: 0.85rem;
        }
        
        .navbar .nav-link {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 0.5rem 0.8rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .navbar .nav-link:hover {
            color: #1e293b;
            background: #f8fafc;
        }
        
        .navbar .btn-login {
            padding: 0.45rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.85rem;
            background: transparent;
            border: 1.5px solid #e2e8f0;
            color: #475569;
            transition: all 0.2s ease;
        }
        
        .navbar .btn-login:hover {
            border-color: #6366f1;
            color: #6366f1;
            background: rgba(99, 102, 241, 0.05);
        }
        
        .navbar .btn-register {
            padding: 0.45rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.85rem;
            background: #6366f1;
            border: none;
            color: white;
            transition: all 0.2s ease;
        }
        
        .navbar .btn-register:hover {
            background: #4f46e5;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }
        
        /* ===== HERO SECTION ===== */
        .hero {
            padding: 100px 0 60px;
            background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%);
            text-align: center;
        }
        
        .hero .badge-hero {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f0ff;
            color: #6366f1;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            margin-bottom: 24px;
        }
        
        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            margin-bottom: 16px;
            color: #0f172a;
        }
        
        .hero h1 .highlight {
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero p {
            font-size: clamp(0.95rem, 2vw, 1.1rem);
            color: #64748b;
            max-width: 540px;
            margin: 0 auto 32px;
            line-height: 1.7;
        }
        
        .hero .btn-group-hero {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }
        
        .hero .btn-primary {
            padding: 0.7rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            background: #6366f1;
            border: none;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
            transition: all 0.3s ease;
        }
        
        .hero .btn-primary:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }
        
        .hero .btn-outline {
            padding: 0.7rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            background: transparent;
            border: 1.5px solid #e2e8f0;
            color: #475569;
            transition: all 0.3s ease;
        }
        
        .hero .btn-outline:hover {
            border-color: #6366f1;
            color: #6366f1;
        }
        
        /* Stats */
        .hero .stats-row {
            display: flex;
            justify-content: center;
            gap: clamp(20px, 5vw, 60px);
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .hero .stat-item {
            text-align: center;
        }
        
        .hero .stat-item .stat-number {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }
        
        .hero .stat-item .stat-label {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 2px;
            font-weight: 500;
        }
        
        /* ===== FEATURES SECTION ===== */
        .features {
            padding: 70px 0;
            background: #ffffff;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 48px;
        }
        
        .section-header .badge-section {
            display: inline-block;
            background: #f0f0ff;
            color: #6366f1;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .section-header h2 {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }
        
        .section-header p {
            color: #64748b;
            font-size: 0.95rem;
            max-width: 450px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 14px;
            padding: 24px;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            border-color: #e2e8f0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .feature-card .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }
        
        .feature-card h5 {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 6px;
            color: #0f172a;
        }
        
        .feature-card p {
            color: #64748b;
            font-size: 0.85rem;
            line-height: 1.6;
            margin-bottom: 0;
        }
        
        /* ===== CTA SECTION ===== */
        .cta-section {
            padding: 60px 0;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800;
            color: white;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }
        
        .cta-section p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
            margin-bottom: 28px;
            max-width: 450px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-section .btn-white {
            padding: 0.7rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            background: white;
            color: #6366f1;
            border: none;
            transition: all 0.3s ease;
        }
        
        .cta-section .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        
        .cta-section .btn-outline-white {
            padding: 0.7rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            background: transparent;
            border: 1.5px solid rgba(255, 255, 255, 0.4);
            color: white;
            transition: all 0.3s ease;
        }
        
        .cta-section .btn-outline-white:hover {
            border-color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* ===== FOOTER ===== */
        .footer {
            padding: 32px 0;
            background: #ffffff;
            border-top: 1px solid #f1f5f9;
            color: #94a3b8;
        }
        
        .footer .footer-brand {
            color: #0f172a;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .footer .footer-brand .brand-icon {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .footer .footer-brand .brand-icon i {
            color: white;
            font-size: 0.75rem;
        }
        
        .footer p {
            font-size: 0.8rem;
            margin-bottom: 0;
        }
        
        .footer .footer-links a {
            color: #94a3b8;
            font-size: 0.8rem;
            text-decoration: none;
            margin-right: 16px;
            transition: color 0.2s ease;
        }
        
        .footer .footer-links a:hover {
            color: #6366f1;
        }

        /* ===== FLOATING HELP BUTTON ===== */
        .float-help-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: white;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
            transition: all 0.3s ease;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .float-help-btn:hover {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 12px 40px rgba(99, 102, 241, 0.5);
        }
        
        .float-help-btn:active {
            transform: scale(0.95);
        }
        
        .float-help-btn .fa-question {
            font-size: 1.2rem;
        }
        
        .float-help-btn .pulse-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.3);
            animation: pulse 2s ease-out infinite;
            z-index: -1;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }
            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }
        
        /* ===== TUTORIAL MODAL ===== */
        .tutorial-modal .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        
        .tutorial-modal .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 20px 24px;
        }
        
        .tutorial-modal .modal-header .modal-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #0f172a;
        }
        
        .tutorial-modal .modal-header .btn-close {
            font-size: 0.8rem;
        }
        
        .tutorial-modal .modal-body {
            padding: 24px;
        }
        
        .tutorial-modal .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 16px 24px;
        }
        
        .tutorial-tabs {
            display: flex;
            gap: 4px;
            background: #f8fafc;
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 20px;
        }
        
        .tutorial-tabs .tab-btn {
            flex: 1;
            padding: 8px 12px;
            border-radius: 8px;
            border: none;
            background: transparent;
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }
        
        .tutorial-tabs .tab-btn.active {
            background: white;
            color: #6366f1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        
        .tutorial-tabs .tab-btn:hover:not(.active) {
            color: #1e293b;
        }
        
        .tutorial-content {
            display: none;
        }
        
        .tutorial-content.active {
            display: block;
        }
        
        .tutorial-content h6 {
            font-weight: 700;
            font-size: 0.95rem;
            color: #0f172a;
            margin-bottom: 12px;
        }
        
        .tutorial-content .step-item {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .tutorial-content .step-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .tutorial-content .step-text {
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.6;
        }
        
        .tutorial-content .step-text strong {
            color: #0f172a;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .feature-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 8px 0;
            font-size: 0.85rem;
            color: #475569;
            border-bottom: 1px solid #f8fafc;
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-list li i {
            color: #10b981;
            font-size: 0.8rem;
            margin-top: 3px;
            flex-shrink: 0;
        }
        
        .feature-list li strong {
            color: #0f172a;
        }
        
        .info-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 14px;
            margin-top: 16px;
        }
        
        .info-box i {
            color: #6366f1;
            font-size: 0.9rem;
            margin-right: 6px;
        }
        
        .info-box p {
            font-size: 0.8rem;
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .navbar .btn-login, .navbar .btn-register {
                padding: 0.4rem 1rem;
                font-size: 0.8rem;
            }
            
            .hero {
                padding: 70px 0 40px;
            }
            
            .hero .btn-group-hero {
                flex-direction: column;
                align-items: center;
            }
            
            .hero .btn-primary, .hero .btn-outline {
                width: 100%;
                max-width: 280px;
                text-align: center;
            }
            
            .feature-card {
                padding: 20px;
            }
            
            .cta-section .btn-white, .cta-section .btn-outline-white {
                width: 100%;
                max-width: 280px;
                text-align: center;
            }
            
            .float-help-btn {
                bottom: 16px;
                right: 16px;
                width: 50px;
                height: 50px;
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar .nav-link {
                font-size: 0.8rem;
            }
            
            .hero .stats-row {
                gap: 24px;
            }
        }
        
        /* Mobile Menu Styles */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: white;
                border-radius: 12px;
                padding: 16px;
                margin-top: 8px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            }
            
            .navbar .btn-login, .navbar .btn-register {
                width: 100%;
                text-align: center;
                margin-bottom: 8px;
            }
        }
    </style>
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <div class="brand-icon">
                    <i class="fas fa-code"></i>
                </div>
                <span><?php echo APP_NAME; ?></span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border: none; padding: 4px 8px;">
                <i class="fas fa-bars" style="color: #1e293b; font-size: 1.2rem;"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="features.php">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                
                <div class="d-flex gap-2 flex-column flex-lg-row">
                    <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-register">
                        <i class="fas fa-user-plus me-2"></i>Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ===== HERO SECTION ===== -->
    <section class="hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="badge-hero">
                        <i class="fas fa-rocket"></i> All-in-One Developer Platform
                    </div>
                    
                    <h1>
                        Build. Track.<br>
                        <span class="highlight">Showcase.</span>
                    </h1>
                    
                    <p>
                        DevTrack helps you manage projects, track tasks, develop skills, 
                        and showcase your work in one beautiful platform.
                    </p>
                    
                    <div class="btn-group-hero">
                        <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-primary">
                            <i class="fas fa-rocket me-2"></i>Start Free
                        </a>
                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </div>
                    
                    <!-- REAL STATS FROM DATABASE -->
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo number_format($totalDevelopers); ?>+</div>
                            <div class="stat-label">Developers</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo number_format($totalProjects); ?>+</div>
                            <div class="stat-label">Projects</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo number_format($totalTasks); ?>+</div>
                            <div class="stat-label">Tasks Done</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FEATURES SECTION ===== -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <div class="badge-section">
                    <i class="fas fa-star"></i> Features
                </div>
                <h2>Everything You Need</h2>
                <p>Powerful tools to help you manage your development journey.</p>
            </div>
            
            <div class="row g-3">
                <div class="col-6 col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: rgba(99, 102, 241, 0.1);">
                            <i class="fas fa-folder-open" style="color: #6366f1; font-size: 1.1rem;"></i>
                        </div>
                        <h5>Projects</h5>
                        <p>Manage all your projects in one place.</p>
                    </div>
                </div>
                
                <div class="col-6 col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: rgba(16, 185, 129, 0.1);">
                            <i class="fas fa-list-check" style="color: #10b981; font-size: 1.1rem;"></i>
                        </div>
                        <h5>Tasks</h5>
                        <p>Track tasks with priorities and deadlines.</p>
                    </div>
                </div>
                
                <div class="col-6 col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: rgba(168, 85, 247, 0.1);">
                            <i class="fas fa-code" style="color: #a855f7; font-size: 1.1rem;"></i>
                        </div>
                        <h5>Skills</h5>
                        <p>Track your skills and proficiency levels.</p>
                    </div>
                </div>
                
                <div class="col-6 col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: rgba(245, 158, 11, 0.1);">
                            <i class="fas fa-graduation-cap" style="color: #f59e0b; font-size: 1.1rem;"></i>
                        </div>
                        <h5>Learning</h5>
                        <p>Set goals and track your progress.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CTA SECTION ===== -->
    <section class="cta-section" id="about">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <h2>Ready to Start?</h2>
                    <p>Join thousands of developers who use DevTrack to manage their work.</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-white">
                            <i class="fas fa-rocket me-2"></i>Create Account
                        </a>
                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline-white">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start">
                    <div class="footer-brand justify-content-center justify-content-md-start">
                        <div class="brand-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <span><?php echo APP_NAME; ?></span>
                    </div>
                </div>
                
                <div class="col-md-4 text-center my-3 my-md-0">
                    <div class="footer-links">
                        <a href="#">Privacy</a>
                        <a href="#">Terms</a>
                        <a href="#">Support</a>
                    </div>
                </div>
                
                <div class="col-md-4 text-center text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- ===== FLOATING HELP BUTTON ===== -->
    <button class="float-help-btn" onclick="openTutorialModal()" title="How to use DevTrack?">
        <span class="pulse-ring"></span>
        <i class="fas fa-question"></i>
    </button>

    <!-- ===== TUTORIAL MODAL ===== -->
    <div class="modal fade tutorial-modal" id="tutorialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-graduation-cap me-2" style="color: #6366f1;"></i>
                        Welcome to <?php echo APP_NAME; ?> - Quick Tutorial
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <!-- Tabs -->
                    <div class="tutorial-tabs">
                        <button class="tab-btn active" onclick="showTab('tab-overview')">
                            <i class="fas fa-home me-1"></i>Overview
                        </button>
                        <button class="tab-btn" onclick="showTab('tab-features')">
                            <i class="fas fa-star me-1"></i>Features
                        </button>
                        <button class="tab-btn" onclick="showTab('tab-steps')">
                            <i class="fas fa-list-ol me-1"></i>How It Works
                        </button>
                        <button class="tab-btn" onclick="showTab('tab-faq')">
                            <i class="fas fa-question-circle me-1"></i>FAQ
                        </button>
                    </div>
                    
                    <!-- TAB 1: OVERVIEW -->
                    <div class="tutorial-content active" id="tab-overview">
                        <h6><i class="fas fa-info-circle me-2" style="color: #6366f1;"></i>What is <?php echo APP_NAME; ?>?</h6>
                        <p style="font-size: 0.85rem; color: #475569; line-height: 1.7; margin-bottom: 20px;">
                            <?php echo APP_NAME; ?> is an all-in-one developer platform that helps you:
                        </p>
                        
                        <ul class="feature-list">
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><strong>Manage Projects</strong> - Create, organize, and track your development projects.</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><strong>Track Tasks</strong> - Stay on top of your to-do list with priorities and deadlines.</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><strong>Develop Skills</strong> - Record your technical skills and proficiency levels.</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><strong>Learning Goals</strong> - Set achievable learning goals and track progress.</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><strong>Showcase Work</strong> - Create a beautiful portfolio to showcase your projects.</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><strong>Connect</strong> - View other developers' profiles and portfolios.</span>
                            </li>
                        </ul>
                        
                        <div class="info-box">
                            <i class="fas fa-lightbulb"></i>
                            <p><strong>Tip:</strong> Start by creating your account, then add your skills, projects, and learning goals to get the most out of <?php echo APP_NAME; ?>!</p>
                        </div>
                    </div>
                    
                    <!-- TAB 2: FEATURES -->
                    <div class="tutorial-content" id="tab-features">
                        <h6><i class="fas fa-star me-2" style="color: #f59e0b;"></i>Key Features</h6>
                        
                        <ul class="feature-list">
                            <li>
                                <i class="fas fa-folder-open"></i>
                                <span><strong>Project Management</strong> - Add projects, set status (Planning, In Progress, Completed), add tech tags, GitHub/Demo links, and upload files.</span>
                            </li>
                            <li>
                                <i class="fas fa-list-check"></i>
                                <span><strong>Task Tracking</strong> - Create tasks with priority (Low, Medium, High), category, due dates, and link them to projects.</span>
                            </li>
                            <li>
                                <i class="fas fa-code"></i>
                                <span><strong>Skills Portfolio</strong> - Add your programming skills with proficiency percentage (0-100%) and experience level (Beginner to Expert).</span>
                            </li>
                            <li>
                                <i class="fas fa-graduation-cap"></i>
                                <span><strong>Learning Goals</strong> - Set learning targets with target dates and track progress percentage.</span>
                            </li>
                            <li>
                                <i class="fas fa-chart-line"></i>
                                <span><strong>Analytics Dashboard</strong> - View charts and statistics of your tasks, projects, skills, and productivity.</span>
                            </li>
                            <li>
                                <i class="fab fa-github"></i>
                                <span><strong>GitHub Integration</strong> - Link your GitHub account to showcase your repositories and contributions.</span>
                            </li>
                            <li>
                                <i class="fas fa-globe"></i>
                                <span><strong>Public Portfolio</strong> - Your work is accessible to other developers via a public portfolio URL.</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- TAB 3: HOW IT WORKS -->
                    <div class="tutorial-content" id="tab-steps">
                        <h6><i class="fas fa-list-ol me-2" style="color: #10b981;"></i>How To Get Started</h6>
                        
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-text">
                                <strong>Create Your Account</strong><br>
                                Register with your email and password to create your free account. It takes less than 2 minutes.
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-text">
                                <strong>Complete Your Profile</strong><br>
                                Add your full name, bio, profile picture, GitHub username, and social links to make your profile stand out.
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-text">
                                <strong>Add Your Skills</strong><br>
                                Go to the "Skills" section and add your programming languages, frameworks, and tools with proficiency levels.
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">4</div>
                            <div class="step-text">
                                <strong>Create Projects</strong><br>
                                Add your projects with descriptions, tech tags, GitHub links, demo links, and optional files for download.
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">5</div>
                            <div class="step-text">
                                <strong>Track Tasks</strong><br>
                                Break down your projects into tasks. Set priorities, due dates, and track their completion status.
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">6</div>
                            <div class="step-text">
                                <strong>Set Learning Goals</strong><br>
                                Plan your learning journey with clear goals and milestones.
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">7</div>
                            <div class="step-text">
                                <strong>Monitor Analytics</strong><br>
                                Use the dashboard to see your productivity, project progress, and skill development over time.
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">8</div>
                            <div class="step-text">
                                <strong>Share Your Portfolio</strong><br>
                                Share your public portfolio URL with recruiters, clients, or fellow developers to showcase your work.
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB 4: FAQ -->
                    <div class="tutorial-content" id="tab-faq">
                        <h6><i class="fas fa-question-circle me-2" style="color: #ef4444;"></i>Frequently Asked Questions</h6>
                        
                        <div class="mb-3">
                            <p style="font-size: 0.85rem; font-weight: 600; color: #0f172a; margin-bottom: 4px;">
                                <i class="fas fa-chevron-right me-1" style="font-size: 0.7rem; color: #6366f1;"></i>
                                Is <?php echo APP_NAME; ?> free?
                            </p>
                            <p style="font-size: 0.82rem; color: #64748b; margin-left: 16px;">
                                Yes! <?php echo APP_NAME; ?> is completely free for developers. No hidden charges.
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <p style="font-size: 0.85rem; font-weight: 600; color: #0f172a; margin-bottom: 4px;">
                                <i class="fas fa-chevron-right me-1" style="font-size: 0.7rem; color: #6366f1;"></i>
                                Can I make my projects private?
                            </p>
                            <p style="font-size: 0.82rem; color: #64748b; margin-left: 16px;">
                                Yes! When creating a project, you can uncheck "Make project visible to other developers" to keep it private.
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <p style="font-size: 0.85rem; font-weight: 600; color: #0f172a; margin-bottom: 4px;">
                                <i class="fas fa-chevron-right me-1" style="font-size: 0.7rem; color: #6366f1;"></i>
                                Can other developers download my files?
                            </p>
                            <p style="font-size: 0.82rem; color: #64748b; margin-left: 16px;">
                                You control this. When uploading files with a project, you can enable/disable "Allow others to download".
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <p style="font-size: 0.85rem; font-weight: 600; color: #0f172a; margin-bottom: 4px;">
                                <i class="fas fa-chevron-right me-1" style="font-size: 0.7rem; color: #6366f1;"></i>
                                Can I edit or delete my data?
                            </p>
                            <p style="font-size: 0.82rem; color: #64748b; margin-left: 16px;">
                                Absolutely! You can edit or delete your projects, tasks, skills, and learning goals at any time.
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <p style="font-size: 0.85rem; font-weight: 600; color: #0f172a; margin-bottom: 4px;">
                                <i class="fas fa-chevron-right me-1" style="font-size: 0.7rem; color: #6366f1;"></i>
                                How do I get my portfolio URL?
                            </p>
                            <p style="font-size: 0.82rem; color: #64748b; margin-left: 16px;">
                                Your portfolio URL is: <code><?php echo BASE_URL; ?>portfolio/?username=YOUR_USERNAME</code>
                                You can share this link anywhere!
                            </p>
                        </div>
                        
                        <div class="info-box">
                            <i class="fas fa-headset"></i>
                            <p><strong>Need more help?</strong> Contact support from the footer link or email us at support@devtrack.com</p>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 0.85rem; font-weight: 500; padding: 0.5rem 1.2rem; border: 1.5px solid #e2e8f0; color: #64748b;">
                        Close
                    </button>
                    <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-primary" style="border-radius: 8px; font-size: 0.85rem; font-weight: 500; padding: 0.5rem 1.2rem; background: #6366f1; border: none;">
                        <i class="fas fa-rocket me-2"></i>Get Started Now
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ===== OPEN TUTORIAL MODAL =====
        function openTutorialModal() {
            var modal = new bootstrap.Modal(document.getElementById('tutorialModal'));
            modal.show();
        }
        
        // ===== TAB SWITCHING =====
        function showTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tutorial-content').forEach(function(content) {
                content.classList.remove('active');
            });
            
            // Deactivate all tab buttons
            document.querySelectorAll('.tab-btn').forEach(function(btn) {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Activate clicked button
            event.currentTarget.classList.add('active');
        }
        
        // ===== OPEN MODAL AUTOMATICALLY AFTER 3 SECONDS (First time only) =====
        document.addEventListener('DOMContentLoaded', function() {
            // Check if user has seen tutorial before
            if (!localStorage.getItem('devtrack_tutorial_seen')) {
                setTimeout(function() {
                    openTutorialModal();
                    localStorage.setItem('devtrack_tutorial_seen', 'true');
                }, 3000);
            }
        });
    </script>
</body>
</html>
