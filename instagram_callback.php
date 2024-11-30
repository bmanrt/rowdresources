<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include('db_config.php');

$client_id = '488831790421244';
$client_secret = 'ba314fe2ae7ce2ba1dbff979650f322b';
$redirect_uri = 'http://localhost/user_capture/instagram_callback.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $token_url = "https://api.instagram.com/oauth/access_token";
    
    $data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirect_uri,
        'code' => $code,
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    
    $context  = stream_context_create($options);
    $response = file_get_contents($token_url, false, $context);
    
    if ($response === FALSE) {
        die('Error');
    }
    
    $responseData = json_decode($response, true);
    $access_token = $responseData['access_token'];
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE users SET instagram_access_token = ? WHERE id = ?");
    $stmt->bind_param("si", $access_token, $user_id);
    $stmt->execute();
    $stmt->close();
    
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Instagram Linked</title>
        <link rel='stylesheet' href='styles.css'>
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                font-family: 'Roboto', sans-serif;
            }
            .container {
                background-color: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            .back-button {
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #ffcc00;
                color: #fff;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
                font-size: 16px;
                transition: background-color 0.3s ease;
            }
            .back-button:hover {
                background-color: #e6b800;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Instagram Account Linked</h1>
            <p>Your Instagram account has been successfully linked.</p>
            <a class='back-button' href='account_settings.html'>Back to Account Settings</a>
        </div>
    </body>
    </html>";
} else {
    echo "Error: Instagram OAuth code not found.";
}

$conn->close();
?>
