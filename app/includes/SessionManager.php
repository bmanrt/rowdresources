<?php
class SessionManager {
    private static $instance = null;
    private $sessionTimeout = 1800; // 30 minutes in seconds

    private function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->checkSessionTimeout();
        $this->regenerateSessionId();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function checkSessionTimeout() {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $this->sessionTimeout)) {
            $this->destroySession();
            header('Location: login.php?timeout=1');
            exit();
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    private function regenerateSessionId() {
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } else if (time() - $_SESSION['CREATED'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['CREATED'] = time();
        }
    }

    public function setUserSession($userData) {
        $_SESSION['app_user_id'] = $userData['id'];
        $_SESSION['app_username'] = $userData['username'];
        $_SESSION['app_email'] = $userData['email'];
        $_SESSION['app_role'] = $userData['role'];
        $_SESSION['CREATED'] = time();
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    public function isAuthenticated() {
        return isset($_SESSION['app_user_id']);
    }

    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['app_user_id'],
            'username' => $_SESSION['app_username'],
            'email' => $_SESSION['app_email'],
            'role' => $_SESSION['app_role'] ?? 'user'
        ];
    }

    public function hasRole($role) {
        return isset($_SESSION['app_role']) && $_SESSION['app_role'] === $role;
    }

    public function destroySession() {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
    }
}
?>
