<?php
require_once 'config.php';

$page_title = 'Login';

// If already logged in, redirect to dashboard
if (isAuthenticated()) {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit();
}

$error = '';
$success = '';

// Check for logout success message
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success = 'You have been logged out successfully.';
}

// Check for session logout message
if (isset($_SESSION['logout_success'])) {
    $success = $_SESSION['logout_success'];
    unset($_SESSION['logout_success']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            // Set avatar in session
            $_SESSION['avatar'] = $result['user']['avatar'] ?? 'default-avatar.png';
            header('Location: ' . BASE_URL . 'dashboard/');
            exit();
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 24px;
            padding: 50px;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        .login-card .logo {
            width: 64px;
            height: 64px;
            background: #eef2ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .login-card .logo i {
            font-size: 2rem;
            color: #4f46e5;
        }
        .login-card h2 {
            color: #0f172a;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 4px;
            text-align: center;
        }
        .login-card .subtitle {
            color: #64748b;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 28px;
        }
        .login-card .form-label {
            font-size: 0.82rem;
            font-weight: 500;
            color: #0f172a;
        }
        .login-card .form-control {
            border-radius: 12px;
            padding: 10px 16px;
            border: 2px solid #e5e7eb;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .login-card .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        .login-card .input-group-text {
            background: transparent;
            border: 2px solid #e5e7eb;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #94a3b8;
        }
        .login-card .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        .login-card .btn-primary {
            background: #4f46e5;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .login-card .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
        }
        .login-card .footer-text {
            color: #64748b;
            font-size: 0.85rem;
            text-align: center;
            margin-top: 20px;
        }
        .login-card .footer-text a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
        }
        .login-card .footer-text a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 12px;
            font-size: 0.9rem;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .logout-success {
            animation: slideDown 0.5s ease-out;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 576px) {
            .login-card {
                padding: 30px 24px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <i class="fas fa-code"></i>
        </div>
        <h2><?php echo APP_NAME; ?></h2>
        <p class="subtitle">Sign in to your account</p>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show logout-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php echo csrfField(); ?>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </button>
        </form>
        
        <div class="footer-text">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
