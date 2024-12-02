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
$temp_dir = $base_dir . DIRECTORY_SEPARATOR . 'temp_uploads';
$upload_dir = $base_dir . DIRECTORY_SEPARATOR . 'uploads';
$upload_url = "http://154.113.83.252/rowdresources/uploads";

// Debug information
error_log("Upload attempt started");
error_log("Upload URL: " . $upload_url);
error_log("Temp dir: " . $temp_dir);
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

// Create temp directory if it doesn't exist
if (!file_exists($temp_dir)) {
    if (!mkdir($temp_dir, 0755, true)) {
        error_log("Failed to create temp directory: " . $temp_dir);
        echo json_encode([
            'error' => 'Server configuration error',
            'details' => 'Could not create temporary directory'
        ]);
        exit;
    }
}

// Generate unique filename
$temp_filename = uniqid() . '_' . basename($_FILES['media']['name']);
$temp_file = $temp_dir . DIRECTORY_SEPARATOR . $temp_filename;

// Attempt to move uploaded file
if (!move_uploaded_file($_FILES['media']['tmp_name'], $temp_file)) {
    error_log("Failed to move uploaded file to: " . $temp_file);
    echo json_encode([
        'error' => 'File processing failed',
        'details' => 'Could not save uploaded file'
    ]);
    exit;
}

// Set proper file permissions
chmod($temp_file, 0644);

// File URL for external access
$file_url = $upload_url . '/' . $temp_filename;

// External URL path
$external_url = $domain_path . '/uploads/' . $temp_filename;

// Return success response with external URL
echo json_encode([
    'success' => true,
    'file' => $external_url,
    'url' => $file_url,
    'redirect' => 'video_details.php?video=' . urlencode($external_url)
]);

$conn->close();
error_log("Upload handling completed");
?>
