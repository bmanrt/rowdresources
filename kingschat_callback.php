<?php
include('db_config.php');
session_start();

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $client_id = 'YOUR_CLIENT_ID';
    $client_secret = 'YOUR_CLIENT_SECRET';
    $redirect_uri = 'http://localhost/user_capture/kingschat_callback.php';

    // Exchange authorization code for access token
    $token_url = 'https://auth.kingschat.com/oauth2/token';
    $token_params = [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $token_info = json_decode($response, true);

    if (isset($token_info['access_token'])) {
        $access_token = $token_info['access_token'];

        // Use access token to get user info
        $user_info_url = 'https://auth.kingschat.com/oauth2/userinfo';
        $ch = curl_init($user_info_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $user_info = json_decode($response, true);

        // Process user info and log in or register the user
        if (isset($user_info['id'])) {
            $kingschat_id = $user_info['id'];
            $name = $user_info['name'];
            $email = $user_info['email'];

            // Check if user already exists
            $sql = "SELECT id FROM users WHERE kingschat_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $kingschat_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // User exists, log them in
                $stmt->bind_result($user_id);
                $stmt->fetch();
                $_SESSION['user_id'] = $user_id;
                header("Location: dashboard.php");
            } else {
                // User does not exist, register them
                $sql = "INSERT INTO users (name, email, kingschat_id) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $name, $email, $kingschat_id);
                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $stmt->insert_id;
                    header("Location: dashboard.php");
                } else {
                    echo "Error: " . $stmt->error;
                }
            }

            $stmt->close();
        } else {
            echo "Failed to retrieve user info from KingsChat.";
        }
    } else {
        echo "Failed to retrieve access token from KingsChat.";
    }
} else {
    echo "Authorization code not received.";
}

$conn->close();
?>
