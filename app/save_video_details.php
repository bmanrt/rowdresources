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
$temp_video_path = $_POST['video']; // Get the video path from form

// Move video from temp to permanent storage
$temp_full_path = "../" . $temp_video_path;
if (!file_exists($temp_full_path)) {
    echo json_encode(['error' => 'Video file not found']);
    exit;
}

// Create permanent filename with video ID
$file_extension = pathinfo($temp_full_path, PATHINFO_EXTENSION);
$permanent_filename = $video_id . "." . $file_extension;
$permanent_path = "../uploads/" . $permanent_filename;
$relative_permanent_path = "uploads/" . $permanent_filename;

// Move the file from temp to permanent location
if (!rename($temp_full_path, $permanent_path)) {
    echo json_encode(['error' => 'Failed to move video file']);
    exit;
}

// Update the video details and path in the database
$stmt = $conn->prepare("UPDATE user_media SET 
    description = ?, 
    category = ?, 
    tags = ?,
    file_path = ?,
    status = 'completed'
    WHERE video_id = ? AND user_id = ?");

$stmt->bind_param("sssssi", $description, $category, $tags, $relative_permanent_path, $video_id, $user_id);

if ($stmt->execute()) {
    header('Location: player.php?video=' . urlencode($relative_permanent_path) . '&video_id=' . urlencode($video_id));
    exit;
} else {
    error_log("Database error: " . $stmt->error);
    echo json_encode(['error' => 'Failed to save video details']);
    // If database update fails, move the file back to temp
    rename($permanent_path, $temp_full_path);
}

$stmt->close();
$conn->close();
