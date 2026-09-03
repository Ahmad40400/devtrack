<?php
// =============================================
// Security Functions
// =============================================

// CSRF Protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// XSS Prevention
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Input Validation
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

// Session Security
function regenerateSession() {
    session_regenerate_id(true);
}

function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Rate Limiting
function checkRateLimit($key, $limit = 5, $window = 300) {
    // Simple rate limiting implementation
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['count' => 1, 'timestamp' => time()];
        return true;
    }
    $data = $_SESSION['rate_limit'][$key];
    if (time() - $data['timestamp'] > $window) {
        $_SESSION['rate_limit'][$key] = ['count' => 1, 'timestamp' => time()];
        return true;
    }
    if ($data['count'] >= $limit) {
        return false;
    }
    $_SESSION['rate_limit'][$key]['count']++;
    return true;
}

// Password Strength Validation
function validatePassword($password) {
    return strlen($password) >= 8;
}

// Sanitize File Upload
function sanitizeFilename($filename) {
    return preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
}
?>