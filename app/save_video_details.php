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
$video_path = $_POST['video'];

// Remove domain path from video_path if it exists
$video_path = str_replace($domain_path, '', $video_path);

// The video should already be in the uploads directory
$relative_permanent_path = $video_path;

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
        status = 'completed'
        WHERE video_id = ? AND user_id = ?");
    $stmt->bind_param("ssssi", $description, $category, $tags, $video_id, $user_id);
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
}

$check_stmt->close();
$stmt->close();
$conn->close();
