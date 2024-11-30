<?php
session_start();

// Clear app-specific session variables
unset($_SESSION['app_user_id']);
unset($_SESSION['app_username']);
unset($_SESSION['app_email']);

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>
