<?php
require_once 'config.php';

$page_title = 'Register';

// If already logged in, redirect to dashboard
if (isAuthenticated()) {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit();
}

$error = '';
$success = '';
$username = '';
$email = '';
$fullName = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        
        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
            $error = 'Please fill in all fields.';
        } elseif ($password !== $password_confirm) {
            $error = 'Passwords do not match.';
        } else {
            $result = registerUser($username, $email, $password, $fullName);
            if ($result['success']) {
                $success = 'Registration successful! You can now login.';
                $username = $email = $fullName = '';
            } else {
                $error = $result['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-code fa-3x text-primary mb-3"></i>
                            <h2><?php echo APP_NAME; ?></h2>
                            <p class="text-muted">Create your account</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <?php echo csrfField(); ?>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" required>
                                </div>
                                <small class="text-muted">3-20 characters (letters, numbers, underscore)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                    <input type="text" name="full_name" class="form-control" value="<?php echo $fullName; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password_confirm" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus me-2"></i>Register
                                </button>
                            </div>
                            <div class="text-center">
                                <p class="mb-0">Already have an account? <a href="login.php">Sign In</a></p>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3 text-muted">
                    <small>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</small>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>