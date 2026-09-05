<?php
require_once __DIR__ . '/config.php';

// Redirect if already logged in
if (isAuthenticated()) {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit();
}

$page_title = 'Login - ' . APP_NAME;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Please enter email and password.';
        } else {
            $result = loginUser($email, $password);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Login successful! Welcome back.';
                header('Location: ' . BASE_URL . 'dashboard/');
                exit();
            } else {
                $error = $result['error'];
            }
        }
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<!-- HTML FORM -->
<div class="row justify-content-center align-items-center min-vh-100" style="background: #f8fafc;">
    <div class="col-md-8 col-lg-5 col-xl-4 py-5">
        
        <!-- Logo & Title -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                 style="width: 64px; height: 64px; background: linear-gradient(135deg, #6366f1, #a855f7);">
                <i class="fas fa-code text-white" style="font-size: 1.5rem;"></i>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0f172a;">Welcome Back</h3>
            <p class="text-muted small mb-0">Login to your <?php echo APP_NAME; ?> account</p>
        </div>
        
        <!-- Login Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success py-2 px-3 mb-3" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" novalidate>
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Email Address</label>
                        <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                            <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                                <i class="fas fa-envelope text-muted"></i>
                            </span>
                            <input type="email" name="email" class="form-control border-start-0 ps-0" 
                                   placeholder="Enter your email"
                                   value="<?php echo sanitizeOutput($_POST['email'] ?? ''); ?>"
                                   required
                                   style="background: #f8fafc; border: none; font-size: 0.85rem; padding: 10px 12px;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Password</label>
                        <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                            <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" name="password" class="form-control border-start-0 ps-0" 
                                   placeholder="Enter your password"
                                   required
                                   style="background: #f8fafc; border: none; font-size: 0.85rem; padding: 10px 12px;">
                            <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePassword()" 
                                    style="border: none; background: #f8fafc; color: #94a3b8; padding: 0 12px;">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label small" for="rememberMe" style="font-size: 0.8rem;">Remember Me</label>
                        </div>
                        <a href="<?php echo BASE_URL; ?>forgot-password.php" class="small fw-semibold" style="color: #6366f1; text-decoration: none; font-size: 0.8rem;">
                            Forgot Password?
                        </a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3" 
                            style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: #6366f1; border: none;">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
                
            </div>
        </div>
        
        <!-- Register Link -->
        <div class="text-center mt-3">
            <p class="text-muted small mb-0">
                Don't have an account? 
                <a href="<?php echo BASE_URL; ?>register.php" class="fw-semibold" style="color: #6366f1; text-decoration: none;">
                    Create Account
                </a>
            </p>
        </div>
        
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.querySelector('input[name="password"]');
    const icon = document.getElementById('passwordIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>