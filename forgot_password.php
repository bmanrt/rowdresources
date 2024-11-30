<?php
include('db_config.php');
include('send_email.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    if (!empty($email)) {
        // Check if the email exists in the database
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email exists, proceed with generating and sending the reset token
            $token = bin2hex(random_bytes(50));
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour')); // Set expiry to 1 hour from now

            // Update the reset token and expiry in the database
            $update_sql = "UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sss", $token, $expiry, $email);

            if ($update_stmt->execute()) {
                // Send reset email with the new token
                sendResetEmail($email, $token);
                echo "A reset link has been sent to your email.";
            } else {
                echo "Error: " . $update_stmt->error;
            }

            $update_stmt->close();
        } else {
            echo "No account found with that email address.";
        }

        $stmt->close();
    } else {
        echo "Email field is empty.";
    }

    $conn->close();
}
?>
