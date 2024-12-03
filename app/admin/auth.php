<?php
require_once '../../db_config.php';

class Auth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT id, username, password, full_name FROM admin_users WHERE username = ? AND status = 'active'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($admin = $result->fetch_assoc()) {
            if (password_verify($password, $admin['password'])) {
                // Update last login
                $updateStmt = $this->conn->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $updateStmt->bind_param("i", $admin['id']);
                $updateStmt->execute();

                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['last_activity'] = time();

                // Log admin login
                $this->logActivity($admin['id'], 'login', 'Admin login successful');
                
                return true;
            }
        }
        return false;
    }

    public function logout() {
        if ($this->isLoggedIn()) {
            $this->logActivity($_SESSION['admin_id'], 'login', 'Admin logout');
        }
        
        session_unset();
        session_destroy();
        
        // Start a new session for potential error messages
        session_start();
    }

    public function checkSessionTimeout() {
        $timeout = 30 * 60; // 30 minutes
        if ($this->isLoggedIn()) {
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
                $this->logout();
                $_SESSION['error'] = 'Your session has expired. Please login again.';
                header('Location: login.php');
                exit();
            }
            $_SESSION['last_activity'] = time();
        }
    }

    private function logActivity($adminId, $actionType, $description) {
        $stmt = $this->conn->prepare("INSERT INTO admin_logs (admin_id, action_type, action_description, ip_address) VALUES (?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("isss", $adminId, $actionType, $description, $ip);
        $stmt->execute();
    }
}

// Create global auth instance
$auth = new Auth($conn);

// Check session timeout on every request
$auth->checkSessionTimeout();
