<?php
session_start();
require 'autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include('db_config.php');

$consumerKey = 'gMIXm1Ypnkiz3KMWCwKsP2RJI';
$consumerSecret = 'gDL57MbUOEOgDh4f0UEo6dCqn4hxIzr9J3i7ywi8tMvAhUGXRj';
$oauthCallback = 'http://localhost/user_capture/twitter_callback.php';

if (isset($_GET['oauth_verifier']) && isset($_SESSION['oauth_token']) && isset($_SESSION['oauth_token_secret'])) {
    $request_token = [];
    $request_token['oauth_token'] = $_SESSION['oauth_token'];
    $request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];
    
    // Create a TwitterOAuth object with the request token
    $connection = new TwitterOAuth($consumerKey, $consumerSecret, $request_token['oauth_token'], $request_token['oauth_token_secret']);
    $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_GET['oauth_verifier']]);
    
    // Get the user ID and save the access token in the database
    $user_id = $_SESSION['user_id'];
    $token = $access_token['oauth_token'];
    $token_secret = $access_token['oauth_token_secret'];
    
    $stmt = $conn->prepare("UPDATE users SET twitter_oauth_token = ?, twitter_oauth_token_secret = ? WHERE id = ?");
    $stmt->bind_param("ssi", $token, $token_secret, $user_id);
    $stmt->execute();
    $stmt->close();
    
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Twitter Linked</title>
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
            <h1>Twitter Account Linked</h1>
            <p>Your Twitter account has been successfully linked.</p>
            <a class='back-button' href='account_settings.html'>Back to Account Settings</a>
        </div>
    </body>
    </html>";
} else {
    echo "Error: Twitter OAuth verifier not found.";
}

$conn->close();
?>
