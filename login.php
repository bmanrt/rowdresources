<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include('db_config.php');

    // Get form data
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        echo "Email and password are required!";
    } else {
        // Check if user exists
        $sql = "SELECT id, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Password correct
                session_start();
                $_SESSION['user_id'] = $row['id'];
                header("Location: dashboard.html"); // Redirect to dashboard
            } else {
                // Password incorrect
                echo "Invalid email or password!";
            }
        } else {
            // User not found
            echo "Invalid email or password!";
        }
        $stmt->close();
    }
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
