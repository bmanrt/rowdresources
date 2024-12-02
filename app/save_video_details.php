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

// Define base paths and URLs
$base_dir = dirname(__DIR__);
$domain_path = "http://154.113.83.252/rowdresources";
$temp_video_path = $_POST['video'];

// Normalize paths for cross-platform compatibility
$temp_full_path = $base_dir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $temp_video_path);
if (!file_exists($temp_full_path)) {
    echo json_encode(['error' => 'Video file not found']);
    exit;
}

// Create permanent filename with video ID
$file_extension = pathinfo($temp_full_path, PATHINFO_EXTENSION);
$permanent_filename = $video_id . "." . $file_extension;
$uploads_dir = $base_dir . DIRECTORY_SEPARATOR . 'uploads';
$permanent_path = $uploads_dir . DIRECTORY_SEPARATOR . $permanent_filename;

// Ensure uploads directory exists with proper permissions
if (!file_exists($uploads_dir)) {
    if (!mkdir($uploads_dir, 0755, true)) {
        echo json_encode(['error' => 'Failed to create uploads directory']);
        exit;
    }
}

// Set the URL path for database storage
$relative_permanent_path = $domain_path . "/uploads/" . $permanent_filename;

// Move the file from temp to permanent location
if (!rename($temp_full_path, $permanent_path)) {
    $move_error = error_get_last();
    error_log("Failed to move video file: " . ($move_error ? $move_error['message'] : 'Unknown error'));
    echo json_encode(['error' => 'Failed to move video file']);
    exit;
}

// Set proper file permissions
chmod($permanent_path, 0644);

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
