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

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// XSS Prevention
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Input Validation
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

function validatePassword($password) {
    return strlen($password) >= 8;
}

function validateURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
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

function requireAuth() {
    if (!isAuthenticated()) {
        $_SESSION['error'] = 'Please login to access this page.';
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: ' . BASE_URL . 'dashboard/');
        exit();
    }
}

// Rate Limiting
function checkRateLimit($key, $limit = 5, $window = 300) {
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

// Sanitize File Upload
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
    return time() . '_' . $filename;
}

// Validate File Upload
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Upload failed with error code: ' . $file['error']];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'error' => 'File size exceeds maximum limit of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['valid' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)];
    }
    
    return ['valid' => true];
}

// Generate random string
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>