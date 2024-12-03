<?php
session_start();
require_once(__DIR__ . '/../db_config.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// If user is already logged in, redirect to home
if (isset($_SESSION['app_user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email exists in app_users table
        $stmt = $conn->prepare("SELECT id, username FROM app_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store reset token in database
            $update_stmt = $conn->prepare("UPDATE app_users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $token, $expires, $user['id']);
            
            if ($update_stmt->execute()) {
                // Send reset email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Disable debug output
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'odunoyemayowa@gmail.com';
                    $mail->Password = 'mzxx jpxx wajn xbun';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    
                    // Additional SMTP settings
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->Timeout = 30;

                    // Recipients
                    $mail->setFrom('odunoyemayowa@gmail.com', 'Media Resource Portal');
                    $mail->addAddress($email);

                    // Content
                    $reset_link = "http://{$_SERVER['HTTP_HOST']}/rowdresources/app/reset_password.php?token=" . $token;
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $mail->Body = "Hello {$user['username']},<br><br>";
                    $mail->Body .= "You have requested to reset your password. Click the link below to reset it:<br><br>";
                    $mail->Body .= "<a href='$reset_link'>$reset_link</a><br><br>";
                    $mail->Body .= "This link will expire in 1 hour.<br><br>";
                    $mail->Body .= "If you did not request this reset, please ignore this email.<br><br>";
                    $mail->Body .= "Best regards,<br>Media Resource Portal Team";
                    $mail->AltBody = strip_tags(str_replace('<br>', "\n", $mail->Body));

                    $mail->send();
                    $success = 'Password reset instructions have been sent to your email';
                } catch (Exception $e) {
                    $error = 'Failed to send reset email: ' . $mail->ErrorInfo;
                }
            } else {
                $error = 'Failed to process reset request. Please try again later.';
            }
            $update_stmt->close();
        } else {
            // Don't reveal if email exists or not for security
            $success = 'If your email exists in our system, you will receive reset instructions shortly';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('components/head_common.php'); ?>
    <title>Forgot Password - Media Resource Portal</title>
</head>
<body>
   
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <img src="assets/images/logo.webp" alt="Logo">
            </div>
            <h1 class="auth-title">Reset Password</h1>
            
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
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your email address"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <button type="submit" class="auth-button">
                    <i class="fas fa-paper-plane"></i>
                    Send Reset Link
                </button>
            </form>
            
            <div class="auth-links">
                <p>Remember your password? <a href="login.php">Sign In</a></p>
                <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            </div>
        </div>
    </div>
</body>
</html>
