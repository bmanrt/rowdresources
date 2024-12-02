<?php
require_once('../db_config.php');
require_once('includes/SessionManager.php');

$session = SessionManager::getInstance();

// Check if user is already logged in
if ($session->isAuthenticated()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? ''; // Can be email or username
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Check if login is email or username
        $stmt = $conn->prepare("SELECT id, password, username, email, role FROM app_users WHERE (email = ? OR username = ?) AND status = 'active'");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session data using SessionManager
                $session->setUserSession($user);
                
                // Update last login timestamp
                $update_stmt = $conn->prepare("UPDATE app_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid credentials';
            }
        } else {
            $error = 'Invalid credentials';
        }
        $stmt->close();
    }
}

// Check for session timeout message
$timeout_message = '';
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $timeout_message = 'Your session has expired. Please log in again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('components/head_common.php'); ?>
    <title>Login - Media Resource Portal</title>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <img src="assets/images/logo.webp" alt="ReachOut World Day">
            </div>
            <h1 class="auth-title">Welcome Back</h1>
            
            <?php if ($error): ?>
                <div class="auth-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($timeout_message): ?>
                <div class="auth-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($timeout_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="login">
                        <i class="fas fa-user"></i>
                        Email or Username
                    </label>
                    <input type="text" id="login" name="login" required 
                           placeholder="Enter your email or username"
                           value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="auth-button">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
                
                <div class="auth-links">
                    <p>Don't have an account? <a href="register.php">Sign Up</a></p>
                    <p><a href="forgot_password.php">Forgot Password?</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
