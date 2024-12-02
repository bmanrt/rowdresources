<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Check if all required fields are present
if (!isset($_POST['videoId'], $_POST['description'], $_POST['category'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$user_id = getCurrentUser()['id'];
$video_id = $_POST['videoId'];
$description = trim($_POST['description']);
$category = trim($_POST['category']);
$tags = isset($_POST['tags']) ? implode(',', $_POST['tags']) : '';
$video_path = $_POST['video']; // Get the video path from form

// Update the video details in the database
$stmt = $conn->prepare("UPDATE user_media SET 
    description = ?, 
    category = ?, 
    tags = ?,
    status = 'completed'
    WHERE video_id = ? AND user_id = ?");

$stmt->bind_param("ssssi", $description, $category, $tags, $video_id, $user_id);

if ($stmt->execute()) {
    header('Location: player.php?video=' . urlencode($video_path) . '&video_id=' . urlencode($video_id));
    exit;
} else {
    error_log("Database error: " . $stmt->error);
    echo json_encode(['error' => 'Failed to save video details']);
}

$stmt->close();
$conn->close();
