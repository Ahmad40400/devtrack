<?php
// =============================================
// Authentication Functions
// =============================================

// Register new user
function registerUser($username, $email, $password, $fullName = null) {
    // Validate input
    if (!validateUsername($username)) {
        return ['success' => false, 'error' => 'Invalid username. Use 3-20 characters, letters, numbers, underscore.'];
    }
    
    if (!validateEmail($email)) {
        return ['success' => false, 'error' => 'Invalid email address.'];
    }
    
    if (!validatePassword($password)) {
        return ['success' => false, 'error' => 'Password must be at least 8 characters.'];
    }
    
    // Check if username exists
    if (getUserByUsername($username)) {
        return ['success' => false, 'error' => 'Username already taken.'];
    }
    
    // Check if email exists
    if (getUserByEmail($email)) {
        return ['success' => false, 'error' => 'Email already registered.'];
    }
    
    // Hash password
    $hashedPassword = hashPassword($password);
    
    // Insert user
    try {
        $userId = insert(
            "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'user')",
            [$username, $email, $hashedPassword, $fullName]
        );
        
        // Log activity
        logActivity($userId, 'register', 'User registered');
        
        return ['success' => true, 'user_id' => $userId];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Registration failed. Please try again.'];
    }
}

// Login user with rate limiting
function loginUser($email, $password) {
    // Rate limiting - IP based
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rateLimitKey = 'login_' . $ip;
    
    // Check rate limit (5 attempts per 15 minutes)
    if (!checkLoginRateLimit($rateLimitKey, 5, 900)) {
        return ['success' => false, 'error' => 'Too many login attempts. Please try again after 15 minutes.', 'rate_limited' => true];
    }
    
    // Get user by email
    $user = getUserByEmail($email);
    
    if (!$user) {
        incrementLoginAttempts($rateLimitKey);
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }
    
    // Check if user is active
    if (!$user['is_active']) {
        incrementLoginAttempts($rateLimitKey);
        return ['success' => false, 'error' => 'Account is disabled. Contact administrator.'];
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        incrementLoginAttempts($rateLimitKey);
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }
    
    // Reset login attempts on success
    resetLoginAttempts($rateLimitKey);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    
    // Update last login
    update("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
    
    // Log activity
    logActivity($user['id'], 'login', 'User logged in');
    
    regenerateSession();
    
    return ['success' => true, 'user' => $user];
}

// Logout user
function logoutUser() {
    if (isAuthenticated()) {
        logActivity($_SESSION['user_id'], 'logout', 'User logged out');
    }
    
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy session
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    session_destroy();
    return true;
}

// Check if user is logged in and redirect if not
function requireLogin() {
    if (!isAuthenticated()) {
        $_SESSION['error'] = 'Please login to access this page.';
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

// Check if user is admin and redirect if not
function requireAdminRole() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
        exit();
    }
}

// =============================================
// RATE LIMITING FUNCTIONS
// =============================================

// Check login rate limit
function checkLoginRateLimit($key, $maxAttempts = 5, $window = 900) {
    if (!isset($_SESSION['rate_limit'][$key])) {
        return true;
    }
    
    $data = $_SESSION['rate_limit'][$key];
    
    // If window expired, reset
    if (time() - $data['timestamp'] > $window) {
        unset($_SESSION['rate_limit'][$key]);
        return true;
    }
    
    // If max attempts reached
    if ($data['count'] >= $maxAttempts) {
        return false;
    }
    
    return true;
}

// Increment login attempts
function incrementLoginAttempts($key) {
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [
            'count' => 1,
            'timestamp' => time()
        ];
    } else {
        $_SESSION['rate_limit'][$key]['count']++;
    }
}

// Reset login attempts
function resetLoginAttempts($key) {
    unset($_SESSION['rate_limit'][$key]);
}

// =============================================
// FORGOT PASSWORD FUNCTIONS
// =============================================

// Request password reset
function requestPasswordReset($email) {
    $user = getUserByEmail($email);
    
    if (!$user) {
        return ['success' => false, 'error' => 'No account found with this email address.'];
    }
    
    // Generate unique token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    
    // Delete old tokens for this email
    delete("DELETE FROM password_resets WHERE email = ?", [$email]);
    
    // Insert new token
    insert(
        "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)",
        [$email, $token, $expiresAt]
    );
    
    // Send reset email - SITE_URL use karein (Localhost)
    $resetLink = SITE_URL . 'reset-password.php?token=' . $token . '&email=' . urlencode($email);
    $mailSent = sendPasswordResetEmail($email, $resetLink);
    
    return ['success' => $mailSent, 'message' => $mailSent ? 'Password reset link sent to your email!' : 'Failed to send email. Please try again.'];
}

// Verify password reset token
function verifyPasswordResetToken($token, $email) {
    $reset = fetchOne(
        "SELECT * FROM password_resets WHERE token = ? AND email = ?",
        [$token, $email]
    );
    
    if (!$reset) {
        return false;
    }
    
    // Check if token expired
    if (strtotime($reset['expires_at']) < time()) {
        return false;
    }
    
    return true;
}

// Reset password
function resetPassword($token, $email, $newPassword) {
    if (!verifyPasswordResetToken($token, $email)) {
        return ['success' => false, 'error' => 'Invalid or expired reset link.'];
    }
    
    // Hash new password
    $hashedPassword = hashPassword($newPassword);
    
    // Update user password
    update(
        "UPDATE users SET password = ? WHERE email = ?",
        [$hashedPassword, $email]
    );
    
    // Delete used token
    delete("DELETE FROM password_resets WHERE email = ?", [$email]);
    
    return ['success' => true, 'message' => 'Password reset successfully! Please login.'];
}
?>