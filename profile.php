<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}

include('db_config.php');
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $profile_picture = $_FILES['profile_picture'];
    $profile_picture_path = '';

    // Fetch the existing profile picture path from the database
    $sql = "SELECT profile_picture FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $existing_profile_picture = $user['profile_picture'];
    $stmt->close();

    if ($profile_picture['size'] > 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($profile_picture['type'], $allowed_types)) {
            $file_extension = pathinfo($profile_picture['name'], PATHINFO_EXTENSION);
            $profile_picture_path = 'uploads/' . uniqid() . '.' . $file_extension;
            if (move_uploaded_file($profile_picture['tmp_name'], $profile_picture_path)) {
                // If upload successful, delete the old profile picture if it exists
                if (!empty($existing_profile_picture) && file_exists($existing_profile_picture)) {
                    unlink($existing_profile_picture);
                }
            } else {
                echo "Error uploading file.";
                exit();
            }
        } else {
            echo "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            exit();
        }
    } else {
        // Otherwise, retain the existing profile picture
        $profile_picture_path = $existing_profile_picture;
    }

    $sql = "UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $name, $email, $profile_picture_path, $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} elseif ($_GET['action'] == 'get') {
    $sql = "SELECT name, email, profile_picture FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode($user);

    $stmt->close();
    $conn->close();
}
?>
