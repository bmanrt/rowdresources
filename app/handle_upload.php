<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

header('Content-Type: application/json');

// Enable error reporting and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'upload_errors.log');

if (!isAuthenticated()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = getCurrentUser()['id'];

// Define upload directories with proper path handling
$base_dir = dirname(__DIR__);
$domain_path = "http://154.113.83.252/rowdresources";
$upload_dir = $base_dir . DIRECTORY_SEPARATOR . 'rowdresources' . DIRECTORY_SEPARATOR . 'uploads';
$upload_url = "http://154.113.83.252/rowdresources/uploads";

// Debug information
error_log("Upload attempt started");
error_log("Upload URL: " . $upload_url);
error_log("Upload dir: " . $upload_dir);

// Verify file upload
if (!isset($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
    $upload_error = isset($_FILES['media']) ? $_FILES['media']['error'] : 'No file uploaded';
    error_log("File upload error: " . $upload_error);
    echo json_encode([
        'error' => 'File upload failed',
        'details' => 'Error code: ' . $upload_error
    ]);
    exit;
}

// Create uploads directory if it doesn't exist
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        error_log("Failed to create uploads directory: " . $upload_dir);
        echo json_encode([
            'error' => 'Server configuration error',
            'details' => 'Could not create uploads directory'
        ]);
        exit;
    }
}

// Generate video ID and filename
$video_id = uniqid('vid_');
$file_extension = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
$filename = $video_id . '.' . $file_extension;
$upload_file = $upload_dir . DIRECTORY_SEPARATOR . $filename;

// Attempt to move uploaded file
if (!move_uploaded_file($_FILES['media']['tmp_name'], $upload_file)) {
    error_log("Failed to move uploaded file to: " . $upload_file);
    echo json_encode([
        'error' => 'File processing failed',
        'details' => 'Could not save uploaded file'
    ]);
    exit;
}

// Set proper file permissions
chmod($upload_file, 0644);

// Return success response with correct paths
echo json_encode([
    'success' => true,
    'file' => $domain_path . '/uploads/' . $filename,
    'url' => $domain_path . '/uploads/' . $filename,
    'video_id' => $video_id,
    'redirect' => 'video_details.php?video=' . urlencode('/rowdresources/uploads/' . $filename) . '&video_id=' . $video_id
]);

$conn->close();
error_log("Upload handling completed");
?>
