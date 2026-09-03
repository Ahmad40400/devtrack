<?php
require_once __DIR__ . '/config.php';

// Logout user
if (isAuthenticated()) {
    // ✅ FIX: Logout se pehle user exist karta hai ya nahi check karein
    $userId = $_SESSION['user_id'];
    $userExists = fetchOne("SELECT id FROM users WHERE id = ?", [$userId]);
    
    if ($userExists) {
        logActivity($userId, 'logout', 'User logged out');
    }
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

// Redirect to login page
header('Location: ' . BASE_URL . 'login.php');
exit();
