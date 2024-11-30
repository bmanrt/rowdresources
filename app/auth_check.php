<?php
require_once('includes/SessionManager.php');

$session = SessionManager::getInstance();

// Function to check if user is logged in
function isAuthenticated() {
    global $session;
    return $session->isAuthenticated();
}

// Function to get current user data
function getCurrentUser() {
    global $session;
    return $session->getCurrentUser();
}

// Function to check if user has specific role
function hasRole($role) {
    global $session;
    return $session->hasRole($role);
}

// Function to redirect to login page
function redirectToLogin() {
    header('Location: login.php');
    exit();
}

// Check authentication for all protected pages
if (!isAuthenticated()) {
    // Allow access to login, register pages and assets
    $currentFile = basename($_SERVER['PHP_SELF']);
    $allowedFiles = [
        'login.php',
        'register.php',
        'forgot_password.php',
        'reset_password.php',
        'install_auth.php'
    ];
    $allowedExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'];
    
    // Get file extension
    $fileExtension = pathinfo($currentFile, PATHINFO_EXTENSION);
    
    // Allow access to static assets and auth pages
    if (!in_array($currentFile, $allowedFiles) && !in_array($fileExtension, $allowedExtensions)) {
        redirectToLogin();
    }
}

// Update logout.php to clear app-specific session variables
if (basename($_SERVER['PHP_SELF']) === 'logout.php') {
    unset($_SESSION['app_user_id']);
    unset($_SESSION['app_username']);
    unset($_SESSION['app_email']);
}
?>
