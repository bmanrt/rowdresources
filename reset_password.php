<?php
include('db_config.php');

$token = $_GET['token'] ?? '';

if (!empty($token)) {
    // Validate token
    $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Token is valid, redirect to update password page
        header("Location: update_password.php?token=" . $token);
        exit();
    } else {
        echo "Invalid or expired token.";
    }

    $stmt->close();
} else {
    echo "Token is missing.";
}

$conn->close();
?>
