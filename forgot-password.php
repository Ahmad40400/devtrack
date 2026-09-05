<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config/mail.php';

$page_title = 'Forgot Password - ' . APP_NAME;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = 'Please enter your email address.';
        } elseif (!validateEmail($email)) {
            $error = 'Invalid email address.';
        } else {
            $result = requestPasswordReset($email);
            
            if ($result['success']) {
                $success = $result['message'];
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
                <i class="fas fa-key" style="font-size: 1.5rem; color: #6366f1;"></i>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0f172a;">Forgot Password</h3>
            <p class="text-muted small mb-0">Enter your email to receive a reset link</p>
        </div>
        
        <!-- Card -->
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
                
                <?php if (!$success): ?>
                    <form method="POST" action="" novalidate>
                        <?php echo csrfField(); ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Email Address</label>
                            <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                                <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0" 
                                       placeholder="Enter your registered email"
                                       value="<?php echo sanitizeOutput($_POST['email'] ?? ''); ?>"
                                       required
                                       style="background: #f8fafc; border: none; font-size: 0.85rem; padding: 10px 12px;">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3" 
                                style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: #6366f1; border: none;">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <p class="text-muted small mb-0">
                        Remember your password? 
                        <a href="<?php echo BASE_URL; ?>login.php" class="fw-semibold" style="color: #6366f1; text-decoration: none;">
                            Login here
                        </a>
                    </p>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>