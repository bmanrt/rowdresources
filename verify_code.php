<?php
include('db_config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['reset_code'])) {
        $email = $_POST['email'];
        $reset_code = $_POST['reset_code'];

        // Verify the reset code
        $sql = "SELECT reset_token FROM users WHERE email = ? AND reset_code = ? AND reset_expiry > NOW()";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("ss", $email, $reset_code);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Code is valid, redirect to update password page
            header("Location: update_password.html?token=" . urlencode($reset_code));
        } else {
            echo "Invalid or expired code.";
        }

        $stmt->close();
    } else {
        echo "Required fields missing.";
    }

    $conn->close();
}
?>
