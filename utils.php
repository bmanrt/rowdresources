<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendResetEmail($email, $token) {
    $reset_link = "http://localhost/user_capture/reset_password.php?token=$token"; // Ensure the URL is correct
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'odunoyemayowa@gmail.com';
        $mail->Password = 'mzxx jpxx wajn xbun'; // Use an app password if 2FA is enabled
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('odunoyemayowa@gmail.com', 'Mayowa Odunoye');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "Click the link below to reset your password:<br><a href='$reset_link'>$reset_link</a>";
        $mail->AltBody = "Click the link below to reset your password:\n$reset_link";

        $mail->send();
        echo 'Reset link has been sent to your email.';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
