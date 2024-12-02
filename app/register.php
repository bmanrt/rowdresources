<?php
session_start();
require_once('../db_config.php');
require_once('includes/SessionManager.php');

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM app_users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO app_users (username, email, password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $userId = $conn->insert_id;
                $userData = [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email,
                    'role' => 'user'
                ];
                $session = SessionManager::getInstance();
                $session->setUserSession($userData);
                header('Location: index.php');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('components/head_common.php'); ?>
    <title>Sign Up - Media Resource Portal</title>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <img src="assets/images/logo.webp" alt="ReachOut World Day">
            </div>
            <h1 class="auth-title">Create Account</h1>
            
            <?php if ($error): ?>
                <div class="auth-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="auth-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-user"></i>
                        Full Name
                    </label>
                    <input type="text" id="full_name" name="full_name" required 
                           placeholder="Enter your full name"
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-at"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Choose a username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your email address"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Choose a password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i>
                        Confirm Password
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirm your password">
                </div>

                <button type="submit" class="auth-button">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>

                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Sign In</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
