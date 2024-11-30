<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

include 'db_config.php';

$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT name, email, country, profile_picture FROM users WHERE id = ?");
if (!$query) {
    http_response_code(500);
    echo json_encode(["error" => "Database query preparation failed: " . $conn->error]);
    exit();
}

$query->bind_param('i', $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($user);
} else {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
}
?>
