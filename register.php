<?php
require_once __DIR__ . '/config.php';

// Redirect if already logged in
if (isAuthenticated()) {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit();
}

$page_title = 'Create Account - ' . APP_NAME;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $full_name = sanitizeInput($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validations
        if (!validateUsername($username)) {
            $error = 'Invalid username. Use 3-20 characters, letters, numbers, underscore.';
        } elseif (!validateEmail($email)) {
            $error = 'Invalid email address.';
        } elseif (!validatePassword($password)) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (getUserByUsername($username)) {
            $error = 'Username already taken.';
        } elseif (getUserByEmail($email)) {
            $error = 'Email already registered.';
        } else {
            // Register user
            $result = registerUser($username, $email, $password, $full_name);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Account created successfully! Please login.';
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

<div class="row justify-content-center align-items-center min-vh-100" style="background: #f8fafc;">
    <div class="col-md-8 col-lg-6 col-xl-5 py-5">
        
        <!-- Logo & Title -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                 style="width: 64px; height: 64px; background: linear-gradient(135deg, #6366f1, #a855f7);">
                <i class="fas fa-code text-white" style="font-size: 1.5rem;"></i>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0f172a;">Create Account</h3>
            <p class="text-muted small mb-0">Start your <?php echo APP_NAME; ?> journey today</p>
        </div>
        
        <!-- Register Card -->
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
                    
                    <!-- Full Name -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Full Name</label>
                        <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                            <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                                <i class="fas fa-user text-muted"></i>
                            </span>
                            <input type="text" name="full_name" class="form-control border-start-0 ps-0" 
                                   placeholder="e.g., John Doe"
                                   value="<?php echo sanitizeOutput($_POST['full_name'] ?? ''); ?>"
                                   style="background: #f8fafc; border: none; font-size: 0.85rem; padding: 10px 12px;">
                        </div>
                    </div>
                    
                    <!-- Username -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Username *</label>
                        <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                            <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                                <i class="fas fa-user-circle text-muted"></i>
                            </span>
                            <input type="text" name="username" class="form-control border-start-0 ps-0" 
                                   placeholder="Choose a username"
                                   value="<?php echo sanitizeOutput($_POST['username'] ?? ''); ?>"
                                   required
                                   style="background: #f8fafc; border: none; font-size: 0.85rem; padding: 10px 12px;">
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">3-20 characters, letters, numbers, underscore</small>
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Email Address *</label>
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
                    
                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Password *</label>
                        <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                            <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" 
                                   placeholder="Create a password"
                                   required
                                   style="background: #f8fafc; border: none; font-size: 0.85rem; padding: 10px 12px;">
                            <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePassword('password', 'passwordIcon')" 
                                    style="border: none; background: #f8fafc; color: #94a3b8; padding: 0 12px;">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">Minimum 8 characters</small>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Confirm Password *</label>
                        <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                            <span class="input-group-text bg-transparent border-end-0" style="background: #f8fafc !important;">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control border-start-0 ps-0" 
                                   placeholder="Confirm your password"
                                   required
                                   style="background: #f8fafc; border: none; font-size: 0.85rem; padding: 10px 12px;">
                            <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePassword('confirm_password', 'confirmPasswordIcon')" 
                                    style="border: none; background: #f8fafc; color: #94a3b8; padding: 0 12px;">
                                <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3" 
                            style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: #6366f1; border: none; transition: all 0.2s ease;">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                    
                    <!-- Terms -->
                    <p class="text-center text-muted mb-0" style="font-size: 0.7rem;">
                        By creating an account, you agree to our 
                        <a href="#" style="color: #6366f1; text-decoration: none;">Terms of Service</a> and 
                        <a href="#" style="color: #6366f1; text-decoration: none;">Privacy Policy</a>
                    </p>
                </form>
            </div>
        </div>
        
        <!-- Login Link -->
        <div class="text-center mt-3">
            <p class="text-muted small mb-0">
                Already have an account? 
                <a href="<?php echo BASE_URL; ?>login.php" class="fw-semibold" style="color: #6366f1; text-decoration: none;">
                    Login here
                </a>
            </p>
        </div>
        
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
