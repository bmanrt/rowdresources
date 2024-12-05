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
$domain_path = "http://154.113.83.252/rowdresources";
$upload_dir = '/var/www/html/rowdresources/uploads';

// Debug information
error_log("Upload attempt started");
error_log("Upload URL: " . $domain_path . "/uploads");
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

// Validate file type
$allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
$file_type = $_FILES['media']['type'];
if (!in_array($file_type, $allowed_types)) {
    error_log("Invalid file type: " . $file_type);
    echo json_encode([
        'error' => 'Invalid file type',
        'details' => 'Allowed types: MP4, WebM, OGG'
    ]);
    exit;
}

// Validate file size (1024MB max)
$max_size = 1024 * 1024 * 1024; // 1GB in bytes
if ($_FILES['media']['size'] > $max_size) {
    error_log("File too large: " . $_FILES['media']['size'] . " bytes");
    echo json_encode([
        'error' => 'File too large',
        'details' => 'Maximum file size is 1024MB'
    ]);
    exit;
}

// Create upload directory structure if it doesn't exist
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

try {
    // Generate video ID and filename
    $video_id = uniqid('vid_');
    $file_extension = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
    
    // Sanitize filename
    $filename = $video_id . '.' . $file_extension;
    $upload_file = $upload_dir . '/' . $filename;
    
    // Ensure the upload directory is clean
    $relative_path = 'uploads/' . $filename;
    
    // Attempt to save uploaded file
    if (!move_uploaded_file($_FILES['media']['tmp_name'], $upload_file)) {
        throw new Exception("Failed to save uploaded file");
    }
    
    // Set proper file permissions
    chmod($upload_file, 0644);
    
    // Log success
    error_log("File uploaded successfully: " . $relative_path);
    
    // Return success response with correct paths
    echo json_encode([
        'success' => true,
        'file' => $domain_path . '/uploads/' . $filename,
        'url' => $domain_path . '/uploads/' . $filename,
        'video_id' => $video_id,
        'redirect' => 'video_details.php?video=' . urlencode($relative_path) . '&video_id=' . $video_id
    ]);

} catch (Exception $e) {
    error_log("Upload error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Upload failed',
        'details' => $e->getMessage()
    ]);
    
    // Clean up failed upload if file exists
    if (isset($upload_file) && file_exists($upload_file)) {
        unlink($upload_file);
    }
}

$conn->close();
error_log("Upload handling completed");
?>
