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
                    $mail->Host = 'mail.lwpl.org';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'webadmin@lwpl.org';
                    $mail->Password = 'Newuser2024$';
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
                    $mail->setFrom('webadmin@lwpl.org', 'Reachout World Day Media Repository');
                    $mail->addAddress($email);

                    // Content
                    $reset_link = "http://{$_SERVER['HTTP_HOST']}/rowdresources/app/reset_password.php?token=" . $token;
                    $mail->isHTML(true);
                    $mail->Subject = 'Reachout World Day Media Repository - Password Reset Request';
                    
                    // Create a nicely formatted HTML email
                    $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <img src='http://media.lwpl.org/rowdresources/app/assets/images/logo.webp' alt='Reachout World Day Logo' style='max-width: 200px;'>
                        </div>
                        
                        <div style='background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                            <h2 style='color: #333; margin-bottom: 20px; text-align: center;'>Password Reset Request</h2>
                            
                            <p style='color: #666; font-size: 16px; line-height: 1.5; margin-bottom: 20px;'>Hello {$user['username']},</p>
                            
                            <p style='color: #666; font-size: 16px; line-height: 1.5; margin-bottom: 20px;'>We received a request to reset your password for your Reachout World Day Media Repository account. Click the button below to reset it:</p>
                            
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='$reset_link' style='display: inline-block; background-color: #ffcc00; color: #333; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: bold;'>Reset Password</a>
                            </div>
                            
                            <p style='color: #666; font-size: 14px; margin-bottom: 10px;'>This link will expire in 1 hour for security reasons.</p>
                            
                            <p style='color: #666; font-size: 14px; margin-bottom: 20px;'>If you did not request this reset, please ignore this email or contact support if you have concerns.</p>
                            
                            <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                            
                            <p style='color: #999; font-size: 12px; text-align: center;'>Reachout World Day Media Repository<br>Your trusted platform for media resources</p>
                        </div>
                    </div>";

                    // Plain text version for email clients that don't support HTML
                    $mail->AltBody = "Hello {$user['username']},\n\n" .
                                   "You have requested to reset your password for your Reachout World Day Media Repository account. Click the link below to reset it:\n\n" .
                                   "$reset_link\n\n" .
                                   "This link will expire in 1 hour for security reasons.\n\n" .
                                   "If you did not request this reset, please ignore this email or contact support if you have concerns.\n\n" .
                                   "Best regards,\nReachout World Day Team";
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
    <title>Forgot Password - Reachout World Day Media Repository</title>
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
