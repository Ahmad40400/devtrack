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
</head>
<body>
    <?php if (isAuthenticated()): ?>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>dashboard/">
                <i class="fas fa-code me-2"></i><?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>dashboard/">
                            <i class="fas fa-chart-pie me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>projects/">
                            <i class="fas fa-folder me-1"></i>Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>tasks/">
                            <i class="fas fa-tasks me-1"></i>Tasks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>skills/">
                            <i class="fas fa-code me-1"></i>Skills
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>learning/">
                            <i class="fas fa-graduation-cap me-1"></i>Learning
                        </a>
                    </li>
                    <li class="nav-item">
    <a class="nav-link" href="<?php echo BASE_URL; ?>users/">
        <i class="fas fa-users me-1"></i>Developers
    </a>
</li>
                    <li class="nav-item">
    <a class="nav-link" href="<?php echo BASE_URL; ?>analytics/">
        <i class="fas fa-chart-line me-1"></i>Analytics
    </a>
</li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>github/">
                            <i class="fab fa-github me-1"></i>GitHub
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                            <i class="fas fa-shield-alt me-1"></i>Admin
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?php echo BASE_URL; ?>notifications.php">
                            <i class="fas fa-bell"></i>
                            <?php $unread = getUnreadNotificationsCount($_SESSION['user_id']); ?>
                            <?php if ($unread > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                <?php echo $unread; ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo $_SESSION['avatar'] ?? 'default-avatar.png'; ?>" 
                                 alt="Avatar" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
                            <?php echo sanitizeOutput($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile/">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile/edit.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" id="themeToggle">
                                    <i class="fas fa-moon me-2" id="themeIcon"></i>
                                    <span id="themeText">Dark Mode</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php if (isAuthenticated()): ?>
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-light sidebar collapse" style="min-height: 100vh; padding-top: 20px;">
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
                            <a class="nav-link" href="<?php echo BASE_URL; ?>portfolio/">
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
            
            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                </div>
            <?php else: ?>
            <main class="col-12">
            <?php endif; ?>
            
            <!-- Display messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>