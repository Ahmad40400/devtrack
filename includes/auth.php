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

// Login user
function loginUser($email, $password) {
    // Rate limiting
    if (!checkRateLimit('login_' . $_SERVER['REMOTE_ADDR'], 5, 300)) {
        return ['success' => false, 'error' => 'Too many login attempts. Please wait 5 minutes.'];
    }
    
    // Get user by email
    $user = getUserByEmail($email);
    
    if (!$user) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }
    
    // Check if user is active
    if (!$user['is_active']) {
        return ['success' => false, 'error' => 'Account is disabled. Contact administrator.'];
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }
    
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
        header('Location: ' . BASE_URL . 'dashboard/');
        exit();
    }
}
?>