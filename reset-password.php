<?php
require_once __DIR__ . '/config.php';

$page_title = 'Reset Password - ' . APP_NAME;
$error = '';
$success = '';

// Get token and email from URL
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

// Verify token
if ($token && $email) {
    if (!verifyPasswordResetToken($token, urldecode($email))) {
        $error = 'Invalid or expired reset link. Please request a new one.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $token = $_POST['token'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (!validatePassword($password)) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            $result = resetPassword($token, $email, $password);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                header('Location: ' . BASE_URL . 'login.php');
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
                 style="width: 64px; height: 64px; background: rgba(99,102,241,0.1);">
                <i class="fas fa-lock" style="font-size: 1.5rem; color: #6366f1;"></i>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0f172a;">Reset Password</h3>
            <p class="text-muted small mb-0">Enter your new password</p>
        </div>
        
        <!-- Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$error): ?>
                    <form method="POST" action="" novalidate>
                        <?php echo csrfField(); ?>
                        
                        <input type="hidden" name="token" value="<?php echo sanitizeOutput($token); ?>">
                        <input type="hidden" name="email" value="<?php echo sanitizeOutput(urldecode($email)); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">New Password</label>
                            <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                                <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" 
                                       placeholder="Enter new password"
                                       required
                                       style="background: #f8fafc; border: none; font-size: 0.85rem; padding: 10px 12px;">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Confirm New Password</label>
                            <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                                <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="confirm_password" class="form-control border-start-0 ps-0" 
                                       placeholder="Confirm new password"
                                       required
                                       style="background: #f8fafc; border: none; font-size: 0.85rem; padding: 10px 12px;">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3" 
                                style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: #6366f1; border: none;">
                            <i class="fas fa-check-circle me-2"></i>Reset Password
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <p class="text-muted small mb-0">
                        <a href="<?php echo BASE_URL; ?>login.php" class="fw-semibold" style="color: #6366f1; text-decoration: none;">
                            Back to Login
                        </a>
                    </p>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>