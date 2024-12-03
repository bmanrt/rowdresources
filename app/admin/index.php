<?php
require_once '../../db_config.php';
require_once 'auth.php';

// If admin is already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Otherwise, redirect to login page
header('Location: login.php');
exit();
