<?php
include('db_config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (!empty($token) && !empty($new_password)) {
        // Validate token
        $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_expiry > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // Token is valid, update the password
            $user = $result->fetch_assoc();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password and clear token
            $update_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $hashed_password, $token);

            if ($update_stmt->execute()) {
                // Redirect to a success page
                header("Location: success.html");
                exit();
            } else {
                echo "Error updating password: " . $update_stmt->error;
            }

            $update_stmt->close();
        } else {
            echo "Invalid or expired token.";
        }

        $stmt->close();
    } else {
        echo "Required fields missing.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Update Password</h1>
        <form action="update_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            <input type="submit" value="Update Password">
        </form>
    </div>
</body>
</html>
