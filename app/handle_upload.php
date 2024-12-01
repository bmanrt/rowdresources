<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = getCurrentUser()['id'];
$target_dir = "../uploads/";

// Create uploads directory if it doesn't exist
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Generate a unique ID for the video
$video_id = uniqid('vid_');

// Get the file extension
$file_extension = strtolower(pathinfo($_FILES["media"]["name"], PATHINFO_EXTENSION));

// Create a temporary filename with just the ID (tags will be added later)
$temp_filename = $video_id . "." . $file_extension;
$target_file = $target_dir . $temp_filename;

// Check if file is a video
$file_type = mime_content_type($_FILES["media"]["tmp_name"]);
if(!strstr($file_type, "video/")) {
    echo json_encode(['error' => 'File is not a video']);
    exit;
}

// Check file size (limit to 50MB)
if ($_FILES["media"]["size"] > 50000000) {
    echo json_encode(['error' => 'Sorry, your file is too large']);
    exit;
}

// Allow certain video formats
if($file_extension != "mp4" && $file_extension != "avi" && $file_extension != "mov") {
    echo json_encode(['error' => 'Sorry, only MP4, AVI & MOV files are allowed']);
    exit;
}

// Try to upload file
if (move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
    // Add video_id column if it doesn't exist
    $alter_query = "ALTER TABLE user_media ADD COLUMN IF NOT EXISTS video_id VARCHAR(255)";
    if (!$conn->query($alter_query)) {
        echo json_encode(['error' => 'Error adding video_id column: ' . $conn->error]);
        exit;
    }

    // Insert the initial record with the temporary filename
    $stmt = $conn->prepare("INSERT INTO user_media (user_id, file_path, media_type, video_id) VALUES (?, ?, 'video', ?)");
    $relative_path = str_replace('../', '', $target_file); // Store relative path in database
    $stmt->bind_param("iss", $user_id, $relative_path, $video_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'redirect' => 'video_details.php?video=' . urlencode($relative_path) . '&video_id=' . urlencode($video_id)
        ]);
    } else {
        echo json_encode(['error' => 'Failed to save video: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Sorry, there was an error uploading your file']);
}

$conn->close();
?>
