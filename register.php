<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config/mail.php';

// Redirect if already logged in
if (isAuthenticated()) {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit();
}

$page_title = 'Create Account - ' . APP_NAME;
$error = '';
$success = '';
$showOTPForm = false;
$pendingEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        // Step 1: User submits registration form
        if (isset($_POST['register'])) {
            $username = sanitizeInput($_POST['username'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $full_name = sanitizeInput($_POST['full_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validations
            if (!validateUsername($username)) {
                $error = 'Invalid username. Use 3-20 characters.';
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
                // Generate OTP
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $otpExpires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                // Store pending registration data in session
                $_SESSION['pending_registration'] = [
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $full_name,
                    'password' => $password,
                    'otp' => $otp,
                    'otp_expires' => $otpExpires
                ];
                
                // Send OTP email
                $mailSent = sendOTPEmail($email, $otp);
                
                if ($mailSent) {
                    $showOTPForm = true;
                    $pendingEmail = $email;
                    $success = 'OTP sent to your email! Please check your inbox.';
                } else {
                    $error = 'Failed to send OTP email. Please try again.';
                }
            }
        }
        
        // Step 2: User submits OTP
        elseif (isset($_POST['verify_otp'])) {
            $enteredOTP = trim($_POST['otp'] ?? '');
            
            if (!isset($_SESSION['pending_registration'])) {
                $error = 'No pending registration found. Please register again.';
            } else {
                $pending = $_SESSION['pending_registration'];
                $storedOTP = $pending['otp'];
                $otpExpires = $pending['otp_expires'];
                
                // Check OTP validity
                if ($enteredOTP !== $storedOTP) {
                    $error = 'Invalid OTP. Please try again.';
                } elseif (strtotime($otpExpires) < time()) {
                    $error = 'OTP has expired. Please register again.';
                } else {
                    // OTP verified - Create account
                    $hashedPassword = hashPassword($pending['password']);
                    
                    $userId = insert(
                        "INSERT INTO users (username, email, password, full_name, role, is_active, is_verified) VALUES (?, ?, ?, ?, 'user', 1, 1)",
                        [$pending['username'], $pending['email'], $hashedPassword, $pending['full_name']]
                    );
                    
                    // Log activity
                    logActivity($userId, 'register', 'User registered with OTP verification');
                    
                    // Send welcome email
                    sendWelcomeEmail($pending['email'], $pending['username']);
                    
                    // Clear pending registration
                    unset($_SESSION['pending_registration']);
                    
                    // Redirect to login
                    $_SESSION['success'] = 'Account created successfully! Please login.';
                    header('Location: ' . BASE_URL . 'login.php');
                    exit();
                }
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
            <h3 class="fw-bold mb-1" style="color: #0f172a;">
                <?php echo $showOTPForm ? 'Verify Your Email' : 'Create Account'; ?>
            </h3>
            <p class="text-muted small mb-0">
                <?php echo $showOTPForm ? 'Enter the OTP sent to ' . sanitizeOutput($pendingEmail) : 'Start your ' . APP_NAME . ' journey today'; ?>
            </p>
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

                <?php if (!$showOTPForm): ?>
                    <!-- Registration Form -->
                    <form method="POST" action="" novalidate>
                        <?php echo csrfField(); ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Full Name</label>
                            <input type="text" name="full_name" class="form-control" 
                                   placeholder="e.g., John Doe"
                                   value="<?php echo sanitizeOutput($_POST['full_name'] ?? ''); ?>"
                                   style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; padding: 10px 12px;">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Username *</label>
                            <input type="text" name="username" class="form-control" 
                                   placeholder="Choose a username"
                                   value="<?php echo sanitizeOutput($_POST['username'] ?? ''); ?>"
                                   required
                                   style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; padding: 10px 12px;">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Email Address *</label>
                            <input type="email" name="email" class="form-control" 
                                   placeholder="Enter your email"
                                   value="<?php echo sanitizeOutput($_POST['email'] ?? ''); ?>"
                                   required
                                   style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; padding: 10px 12px;">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Password *</label>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Create a password"
                                   required
                                   style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; padding: 10px 12px;">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" 
                                   placeholder="Confirm your password"
                                   required
                                   style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; padding: 10px 12px;">
                        </div>
                        
                        <button type="submit" name="register" class="btn btn-primary w-100 py-2 mb-3" 
                                style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: #6366f1; border: none;">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </form>
                <?php else: ?>
                    <!-- OTP Verification Form -->
                    <form method="POST" action="" novalidate>
                        <?php echo csrfField(); ?>
                        
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                                 style="width: 72px; height: 72px; background: rgba(99,102,241,0.1);">
                                <i class="fas fa-envelope-open-text" style="font-size: 1.5rem; color: #6366f1;"></i>
                            </div>
                            <p class="text-muted small mb-0" style="font-size: 0.85rem;">
                                We've sent a 6-digit verification code to:<br>
                                <strong style="color: #334155;"><?php echo sanitizeOutput($pendingEmail); ?></strong>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #334155;">Enter OTP Code</label>
                            <input type="text" name="otp" class="form-control text-center" 
                                   placeholder="••••••"
                                   maxlength="6"
                                   required
                                   style="border-radius: 10px; font-size: 1.5rem; border: 1px solid #e2e8f0; padding: 15px; letter-spacing: 15px; font-weight: bold;">
                        </div>
                        
                        <button type="submit" name="verify_otp" class="btn btn-primary w-100 py-2 mb-3" 
                                style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: #6366f1; border: none;">
                            <i class="fas fa-check-circle me-2"></i>Verify Email
                        </button>
                        
                        <div class="text-center">
                            <p class="text-muted small mb-0">OTP valid for 10 minutes</p>
                        </div>
                    </form>
                <?php endif; ?>
                
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

<?php include_once __DIR__ . '/includes/footer.php'; ?>