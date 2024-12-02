<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure user is authenticated
if (!isAuthenticated()) {
    redirectToLogin();
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    error_log("No data received or invalid JSON");
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data received']);
    exit;
}

try {
    $video_id = $data['video_id'];
    $description = $data['description'];
    $category = $data['category'];
    $tags = $data['tags'];
    $videoPath = $data['videoPath'];

    // Log received data
    error_log("Received data: " . print_r($data, true));

    // Convert tags array to string for filename
    $tags_string = implode('-', array_slice($tags, 0, 3));
    $tags_json = json_encode($tags);

    // Update the video details in database first
    $stmt = $conn->prepare("UPDATE user_media SET description = ?, category = ?, tags = ? WHERE video_id = ? AND user_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ssssi", $description, $category, $tags_json, $video_id, $_SESSION['user_id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        error_log("No rows updated. video_id: $video_id, user_id: {$_SESSION['user_id']}");
        throw new Exception("No video record found to update");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Video details updated successfully'
    ]);

    $stmt->close();

} catch (Exception $e) {
    error_log("Error in save_video_details: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
