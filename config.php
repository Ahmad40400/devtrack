<?php
// =============================================
// Main Configuration File (Localhost XAMPP)
// =============================================

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

// Application constants
define('APP_NAME', 'DevTrack');
define('APP_VERSION', '1.0.0');

// =============================================
// BASE_URL - LOCALHOST (XAMPP) KE LIYE
// =============================================
// ✅ ABHI LOCALHOST KE LIYE YE URL USE HO RAHA HAI
define('BASE_URL', 'http://localhost/devtrack/');

// =============================================
// LIVE URL - JAB ACTUAL SITE PAR HOST KARO
// =============================================
// ⚠️ LIVE KE LIYE YE URL USE KARO:
// define('BASE_URL', 'https://devtracker.free.nf/'); // LIVE KE LIYE YE UNCOMMENT KARO

define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('SITE_NAME', 'DevTrack');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set timezone
date_default_timezone_set('Asia/Karachi');
?>