<?php
require_once 'config.php';

// Redirect to dashboard or login
if (isAuthenticated()) {
    header('Location: ' . BASE_URL . 'dashboard/');
} else {
    header('Location: ' . BASE_URL . 'login.php');
}
exit();