<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}

include('db_config.php'); // Include your database configuration

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT file_path, media_type FROM user_media WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$media_list = [];
while ($row = $result->fetch_assoc()) {
    $media_list[] = [
        'path' => $row['file_path'],
        'type' => $row['media_type']
    ];
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($media_list);
?>
