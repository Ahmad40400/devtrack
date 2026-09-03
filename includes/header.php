<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo $page_title ?? 'Dashboard'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    
    <!-- Dark Mode CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/dark-mode.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* ===== NAVBAR STYLES ===== */
        .navbar-custom {
            background: #ffffff !important;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.6rem 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        
        .navbar-custom .navbar-brand {
            font-weight: 700;
            font-size: 1.1rem;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        
        .navbar-custom .navbar-brand i {
            color: #4f46e5;
        }
        
        .navbar-custom .nav-link {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 0.5rem 0.9rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .navbar-custom .nav-link:hover {
            color: #0f172a;
            background: #f8fafc;
        }
        
        .navbar-custom .nav-link.active {
            color: #4f46e5;
            background: #eef2ff;
        }
        
        .navbar-custom .nav-link i {
            margin-right: 6px;
            font-size: 0.85rem;
        }
        
        .navbar-custom .nav-link .badge-nav {
            background: #4f46e5;
            color: white;
            font-size: 0.55rem;
            padding: 1px 6px;
            border-radius: 10px;
            margin-left: 4px;
            font-weight: 600;
        }
        
        /* ===== USER DROPDOWN ===== */
        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px 4px 4px;
            border-radius: 30px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            text-decoration: none;
            color: #0f172a;
            transition: all 0.2s ease;
        }
        
        .user-dropdown .dropdown-toggle:hover {
            background: #f1f5f9;
            border-color: #e2e8f0;
        }
        
        .user-dropdown .dropdown-toggle .avatar-small {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #eef2ff;
        }
        
        .user-dropdown .dropdown-toggle .username-text {
            font-size: 0.8rem;
            font-weight: 500;
            color: #0f172a;
        }
        
        .user-dropdown .dropdown-toggle .chevron {
            font-size: 0.65rem;
            color: #94a3b8;
        }
        
        .user-dropdown .dropdown-menu {
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 6px 0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            min-width: 200px;
        }
        
        .user-dropdown .dropdown-menu .dropdown-item {
            font-size: 0.82rem;
            padding: 8px 18px;
            color: #334155;
            font-weight: 500;
        }
        
        .user-dropdown .dropdown-menu .dropdown-item:hover {
            background: #f8fafc;
            color: #0f172a;
        }
        
        .user-dropdown .dropdown-menu .dropdown-item i {
            width: 18px;
            color: #94a3b8;
            font-size: 0.8rem;
        }
        
        .user-dropdown .dropdown-menu .dropdown-divider {
            border-color: #f1f5f9;
            margin: 4px 0;
        }
        
        .user-dropdown .dropdown-menu .dropdown-item.text-danger i {
            color: #ef4444;
        }
        
        .user-dropdown .dropdown-menu .dropdown-item.text-danger:hover {
            background: #fef2f2;
            color: #dc2626;
        }
        
        /* ===== NOTIFICATION BELL ===== */
        .notification-link {
            color: #64748b;
            font-size: 1.1rem;
            padding: 0.4rem 0.6rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .notification-link:hover {
            color: #0f172a;
            background: #f8fafc;
        }
        
        .notification-link .notif-dot {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .notification-link .notif-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: white;
            font-size: 0.55rem;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 10px;
            border: 2px solid white;
        }
        
        /* ===== MOBILE TOGGLE ===== */
        .navbar-toggler {
            border: none;
            padding: 6px 10px;
            border-radius: 8px;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .navbar-toggler i {
            font-size: 1.2rem;
            color: #0f172a;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            .navbar-custom .navbar-collapse {
                padding-top: 12px;
                border-top: 1px solid #f1f5f9;
                margin-top: 10px;
            }
            
            .navbar-custom .nav-link {
                padding: 0.6rem 0.8rem;
            }
            
            .user-dropdown .dropdown-toggle {
                padding: 4px 8px 4px 4px;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-custom .navbar-brand {
                font-size: 1rem;
            }
            .user-dropdown .dropdown-toggle .username-text {
                display: none;
            }
            .user-dropdown .dropdown-toggle .chevron {
                display: none;
            }
        }
    </style>
</head>
<body>
    
    <?php if (isAuthenticated()): 
        // Get user avatar from session or database
        $userAvatar = $_SESSION['avatar'] ?? 'default-avatar.png';
        
        // If avatar is empty or null, use default
        if (empty($userAvatar) || $userAvatar == '') {
            $userAvatar = 'default-avatar.png';
        }
        
        // Build full avatar path
        $avatarPath = BASE_URL . 'uploads/profile/' . $userAvatar;
        
        // Check if user has custom avatar stored in database
        $user = getUserById($_SESSION['user_id']);
        if ($user && !empty($user['avatar']) && $user['avatar'] !== 'default-avatar.png') {
            $userAvatar = $user['avatar'];
            $avatarPath = BASE_URL . 'uploads/profile/' . $userAvatar;
            // Update session
            $_SESSION['avatar'] = $userAvatar;
        } else {
            $userAvatar = 'default-avatar.png';
            $avatarPath = BASE_URL . 'uploads/profile/default-avatar.png';
            $_SESSION['avatar'] = 'default-avatar.png';
        }
    ?>
    
    <!-- ===== NAVBAR ===== -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-lg-4">
            
            <!-- Brand -->
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>dashboard/">
                <i class="fas fa-code me-2"></i><?php echo APP_NAME; ?>
            </a>
            
            <!-- Toggler -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/dashboard/') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>dashboard/">
                            <i class="fas fa-chart-pie"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/projects/') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>projects/">
                            <i class="fas fa-folder"></i>Projects
                            <span class="badge-nav"><?php echo getProjectsCount($_SESSION['user_id']); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/tasks/') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>tasks/">
                            <i class="fas fa-tasks"></i>Tasks
                            <span class="badge-nav"><?php echo getTasksCount($_SESSION['user_id'], 'pending'); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/skills/') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>skills/">
                            <i class="fas fa-code"></i>Skills
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/learning/') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>learning/">
                            <i class="fas fa-graduation-cap"></i>Learning
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/users/') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>users/">
                            <i class="fas fa-users"></i>Developers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/analytics/') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>analytics/">
                            <i class="fas fa-chart-line"></i>Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/github/') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>github/">
                            <i class="fab fa-github"></i>GitHub
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>admin/dashboard.php">
                            <i class="fas fa-shield-alt"></i>Admin
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Right Side -->
                <ul class="navbar-nav ms-auto align-items-center">
                    
                    <!-- Notifications -->
                    <li class="nav-item">
                        <a class="notification-link" href="<?php echo BASE_URL; ?>notifications.php" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <?php 
                                $unread = getUnreadNotificationsCount($_SESSION['user_id']); 
                                if ($unread > 0): 
                            ?>
                                <span class="notif-count"><?php echo $unread; ?></span>
                            <?php else: ?>
                                <span class="notif-dot"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- User Dropdown -->
                    <li class="nav-item dropdown user-dropdown ms-2">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo $avatarPath; ?>" 
                                 alt="Avatar" 
                                 class="avatar-small"
                                 onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>uploads/profile/default-avatar.png';">
                            <span class="username-text"><?php echo sanitizeOutput($_SESSION['username']); ?></span>
                            <span class="chevron"><i class="fas fa-chevron-down"></i></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>profile/">
                                    <i class="fas fa-user me-2"></i>My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>profile/edit.php">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>profile/change-password.php">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>portfolio/?username=<?php echo $_SESSION['username']; ?>" target="_blank">
                                    <i class="fas fa-globe me-2"></i>My Portfolio
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" id="themeToggle">
                                    <i class="fas fa-moon me-2" id="themeIcon"></i>
                                    <span id="themeText">Dark Mode</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                </ul>
            </div>
        </div>
    </nav>
    
    <?php endif; ?>
    
    <!-- ===== MAIN CONTENT WRAPPER ===== -->
    <div class="container-fluid">
        <div class="row">
            
            <?php if (isAuthenticated()): ?>
            <!-- ===== SIDEBAR ===== -->
            <nav class="col-md-2 d-md-block bg-light sidebar collapse" style="min-height: calc(100vh - 70px); padding-top: 20px;">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], '/dashboard/') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>dashboard/">
                                <i class="fas fa-chart-pie me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/projects/') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>projects/">
                                <i class="fas fa-folder me-2"></i>Projects
                                <span class="badge bg-primary float-end"><?php echo getProjectsCount($_SESSION['user_id']); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/tasks/') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>tasks/">
                                <i class="fas fa-tasks me-2"></i>Tasks
                                <span class="badge bg-warning float-end"><?php echo getTasksCount($_SESSION['user_id'], 'pending'); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/skills/') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>skills/">
                                <i class="fas fa-code me-2"></i>Skills
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/learning/') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>learning/">
                                <i class="fas fa-graduation-cap me-2"></i>Learning
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/users/') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>users/">
                                <i class="fas fa-users me-2"></i>Developers
                                <span class="badge bg-info float-end">Community</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/github/') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>github/">
                                <i class="fab fa-github me-2"></i>GitHub
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/analytics/') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>analytics/">
                                <i class="fas fa-chart-line me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/profile/') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>profile/">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>portfolio/?username=<?php echo $_SESSION['username']; ?>" target="_blank">
                                <i class="fas fa-globe me-2"></i>Portfolio
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>admin/dashboard.php">
                                <i class="fas fa-shield-alt me-2"></i>Admin Panel
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
            
            <!-- ===== MAIN CONTENT ===== -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h4 fw-semibold"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                </div>
                
            <?php else: ?>
            <main class="col-12">
            <?php endif; ?>
            
            <!-- ===== MESSAGES ===== -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
