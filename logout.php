<?php
// =============================================
// Logout Page
// =============================================

require_once 'config.php';

// Check if user is logged in
if (isAuthenticated()) {
    // Log the logout activity before destroying session
    logActivity($_SESSION['user_id'], 'logout', 'User logged out');
    
    // Store username for message
    $username = $_SESSION['username'] ?? 'User';
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
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

// Destroy the session
session_destroy();

// Regenerate session ID for security (start new session for messages)
session_start();
session_regenerate_id(true);

// Set success message
$_SESSION['logout_success'] = 'You have been logged out successfully.';

// Redirect to login page with success message
header('Location: ' . BASE_URL . 'login.php?logout=success');
exit();
?>