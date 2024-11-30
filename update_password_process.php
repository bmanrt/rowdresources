<?php
include('db_config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['token']) && !empty($_POST['token']) && isset($_POST['new_password']) && !empty($_POST['new_password'])) {
        $token = $_POST['token'];
        $new_password = $_POST['new_password'];

        // Validate the token
        $sql = "SELECT email FROM users WHERE reset_token = ? AND reset_expiry > NOW()";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Token is valid
            $stmt->bind_result($email);
            $stmt->fetch();

            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the password and clear the reset token
            $update_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);

            if ($update_stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }

            $update_stmt->bind_param("ss", $hashed_password, $email);

            if ($update_stmt->execute()) {
                echo "Password has been successfully updated. <a href='index.html'>Go to homepage</a>";
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
} else {
    echo "Invalid request method.";
}
?>
