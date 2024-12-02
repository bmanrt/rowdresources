<?php
session_start();
require_once('../db_config.php');

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_id = null;

// Verify token
if ($token) {
    $stmt = $conn->prepare("SELECT id FROM app_users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $valid_token = true;
        $user_id = $user['id'];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } else {
        // Update password and clear reset token
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE app_users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            $success = 'Your password has been successfully reset. You can now login with your new password.';
        } else {
            $error = 'Failed to reset password. Please try again.';
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('components/head_common.php'); ?>
    <title>Reset Password - Media Resource Portal</title>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <img src="assets/images/logo.webp" alt="Logo">
            </div>
            <h1 class="auth-title">Reset Password</h1>
            
            <?php if (!$valid_token && !$success): ?>
                <div class="auth-error">
                    <i class="fas fa-exclamation-circle"></i>
                    Invalid or expired reset link. Please request a new password reset.
                </div>
                <div class="auth-links">
                    <p><a href="forgot_password.php">Request New Reset Link</a></p>
                    <p><a href="login.php">Back to Login</a></p>
                </div>
            <?php else: ?>
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
                    <div class="auth-links">
                        <p><a href="login.php">Proceed to Login</a></p>
                    </div>
                <?php else: ?>
                    <form method="POST" class="auth-form">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" required 
                                   placeholder="Enter your new password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   placeholder="Confirm your new password">
                        </div>
                        
                        <button type="submit" class="auth-button">
                            <i class="fas fa-key"></i>
                            Reset Password
                        </button>
                    </form>
                    
                    <div class="auth-links">
                        <p><a href="login.php">Back to Login</a></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
