<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isAuthenticated()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = getCurrentUser()['id'];
$temp_dir = "../temp_uploads/";

// Create temp directory if it doesn't exist
if (!file_exists($temp_dir)) {
    if (!mkdir($temp_dir, 0777, true)) {
        error_log("Failed to create temp directory: " . error_get_last()['message']);
        echo json_encode(['error' => 'Failed to create temp directory']);
        exit;
    }
    chmod($temp_dir, 0777);
}

// Check if file was uploaded
if (!isset($_FILES["media"])) {
    error_log("No file uploaded");
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

if ($_FILES["media"]["error"] !== UPLOAD_ERR_OK) {
    $upload_errors = array(
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in form',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    );
    $error_message = isset($upload_errors[$_FILES["media"]["error"]]) 
        ? $upload_errors[$_FILES["media"]["error"]] 
        : 'Unknown upload error';
    error_log("Upload error: " . $error_message);
    echo json_encode(['error' => $error_message]);
    exit;
}

// Generate a unique ID for the video
$video_id = uniqid('vid_');

// Get the file extension
$file_extension = strtolower(pathinfo($_FILES["media"]["name"], PATHINFO_EXTENSION));

// Create a temporary filename with just the ID
$temp_filename = $video_id . "_temp." . $file_extension;
$temp_file = $temp_dir . $temp_filename;

// Check if file is a video
$file_type = mime_content_type($_FILES["media"]["tmp_name"]);
if(!strstr($file_type, "video/")) {
    error_log("Invalid file type: " . $file_type);
    echo json_encode(['error' => 'File is not a video']);
    exit;
}

// Check file size (limit to 50MB)
if ($_FILES["media"]["size"] > 50000000) {
    error_log("File too large: " . $_FILES["media"]["size"] . " bytes");
    echo json_encode(['error' => 'Sorry, your file is too large (max 50MB)']);
    exit;
}

// Allow certain video formats
if($file_extension != "mp4" && $file_extension != "avi" && $file_extension != "mov") {
    error_log("Invalid file extension: " . $file_extension);
    echo json_encode(['error' => 'Sorry, only MP4, AVI & MOV files are allowed']);
    exit;
}

// Try to upload file to temporary location
if (move_uploaded_file($_FILES["media"]["tmp_name"], $temp_file)) {
    // Add video_id column if it doesn't exist
    $alter_query = "ALTER TABLE user_media ADD COLUMN IF NOT EXISTS video_id VARCHAR(255), 
                   ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'pending'";
    if (!$conn->query($alter_query)) {
        error_log("Database error: " . $conn->error);
        unlink($temp_file); // Delete the temp file if database error occurs
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }

    // Insert the initial record with the temporary filename and pending status
    $stmt = $conn->prepare("INSERT INTO user_media (user_id, file_path, media_type, video_id, status) VALUES (?, ?, 'video', ?, 'pending')");
    $relative_path = "temp_uploads/" . $temp_filename;
    $stmt->bind_param("iss", $user_id, $relative_path, $video_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'redirect' => 'video_details.php?video=' . urlencode($relative_path) . '&video_id=' . urlencode($video_id)
        ]);
    } else {
        error_log("Database insert error: " . $stmt->error);
        unlink($temp_file); // Delete the temp file if database insert fails
        echo json_encode(['error' => 'Failed to save video information: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    $php_error = error_get_last();
    error_log("Move uploaded file failed: " . ($php_error ? $php_error['message'] : 'Unknown error'));
    echo json_encode(['error' => 'Failed to upload file. Please try again.']);
}

$conn->close();
?>
