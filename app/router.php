<?php
session_start();
require_once('auth_check.php');

// Define routes
$routes = [
    '/' => 'index.html',
    '/login' => 'login.php',
    '/register' => 'register.php',
    '/forgot-password' => 'forgot_password.php',
    '/reset-password' => 'reset_password.php',
    '/category' => 'category.php',
    '/search' => 'search.php',
    '/profile' => 'profile.php',
    '/logout' => 'logout.php'
];

// Get the current path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/rowd/app', '', $path);
$path = $path ?: '/';

// Route to appropriate file
if (isset($routes[$path])) {
    $file = $routes[$path];
    if (file_exists($file)) {
        require_once($file);
        exit;
    }
}

// 404 if route not found
header("HTTP/1.0 404 Not Found");
echo "404 - Page not found";
exit;
?>
