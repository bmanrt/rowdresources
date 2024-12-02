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

// Remove domain path from temp_video_path if it exists
$temp_video_path = str_replace($domain_path, '', $temp_video_path);

// Normalize paths for cross-platform compatibility
$temp_full_path = $base_dir . str_replace('/', DIRECTORY_SEPARATOR, $temp_video_path);
if (!file_exists($temp_full_path)) {
    error_log("Video file not found at: " . $temp_full_path);
    echo json_encode(['error' => 'Video file not found']);
    exit;
}

// Create permanent filename with video ID
$file_extension = pathinfo($temp_full_path, PATHINFO_EXTENSION);
$permanent_filename = $video_id . "." . $file_extension;
$uploads_dir = $base_dir . DIRECTORY_SEPARATOR . 'rowdresources' . DIRECTORY_SEPARATOR . 'uploads';
$permanent_path = $uploads_dir . DIRECTORY_SEPARATOR . $permanent_filename;

// Ensure uploads directory exists with proper permissions
if (!file_exists($uploads_dir)) {
    if (!mkdir($uploads_dir, 0755, true)) {
        error_log("Failed to create directory: " . $uploads_dir);
        echo json_encode(['error' => 'Failed to create uploads directory']);
        exit;
    }
}

// Set the URL path for database storage
$relative_permanent_path = "/rowdresources/uploads/" . $permanent_filename;

// Move the file from temp to permanent location
if (!rename($temp_full_path, $permanent_path)) {
    $move_error = error_get_last();
    error_log("Failed to move video file from " . $temp_full_path . " to " . $permanent_path);
    error_log("Error: " . ($move_error ? $move_error['message'] : 'Unknown error'));
    echo json_encode(['error' => 'Failed to move video file']);
    exit;
}

// Set proper file permissions
chmod($permanent_path, 0644);

// First, check if this video ID already exists
$check_stmt = $conn->prepare("SELECT id FROM user_media WHERE video_id = ? AND user_id = ?");
$check_stmt->bind_param("si", $video_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing record
    $stmt = $conn->prepare("UPDATE user_media SET 
        description = ?, 
        category = ?, 
        tags = ?,
        file_path = ?,
        status = 'completed'
        WHERE video_id = ? AND user_id = ?");
    $stmt->bind_param("sssssi", $description, $category, $tags, $relative_permanent_path, $video_id, $user_id);
} else {
    // Insert new record
    $stmt = $conn->prepare("INSERT INTO user_media (user_id, video_id, description, category, tags, file_path, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'completed')");
    $stmt->bind_param("isssss", $user_id, $video_id, $description, $category, $tags, $relative_permanent_path);
}

if ($stmt->execute()) {
    header('Location: player.php?video=' . urlencode($relative_permanent_path) . '&video_id=' . urlencode($video_id));
    exit;
} else {
    error_log("Database error: " . $stmt->error);
    echo json_encode(['error' => 'Failed to save video details']);
    // If database update fails, move the file back to temp
    rename($permanent_path, $temp_full_path);
}

$check_stmt->close();
$stmt->close();
$conn->close();
