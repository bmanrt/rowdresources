<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Function to send the reset email
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
        // Email sent successfully
        displayMessage('A reset link has been sent to your email.');
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function displayMessage($message) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #ffcc00;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
            padding: 10px;
            box-sizing: border-box;
        }

        .message-container {
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            margin: 20px auto;
        }
        
        .message-container p {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }

        .back-link {
            display: inline-block;
            padding: 12px 20px;
            background-color: #ffcc00;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background-color: #e6b800;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <h1>Password Reset</h1>
        <p>$message</p>
        <a href="index.html" class="back-link">Return to Homepage</a>
    </div>
</body>
</html>
HTML;
    exit();
}

// Example usage
