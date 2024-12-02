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
error_log("Upload attempt - Base dir: " . $base_dir);
error_log("Domain path: " . $domain_path);
error_log("Upload URL: " . $upload_url);
error_log("Temp dir: " . $temp_dir);
error_log("Upload dir: " . $upload_dir);

// Create directories if they don't exist with proper permissions
foreach ([$temp_dir, $upload_dir] as $dir) {
    if (!file_exists($dir)) {
        error_log("Creating directory: " . $dir);
        if (!mkdir($dir, 0755, true)) {
            $error = error_get_last();
            error_log("Failed to create directory {$dir}: " . ($error ? $error['message'] : 'Unknown error'));
            echo json_encode([
                'error' => 'Failed to create required directory',
                'details' => $error ? $error['message'] : 'Unknown error'
            ]);
            exit;
        }
        error_log("Successfully created directory: " . $dir);
    }
    
    // Ensure proper permissions and verify writability
    chmod($dir, 0755);
    if (!is_writable($dir)) {
        error_log("Directory not writable: " . $dir);
        echo json_encode(['error' => 'Upload directory is not writable']);
        exit;
    }
}

// Log upload file information
if (isset($_FILES["media"])) {
    error_log("File upload details: " . json_encode([
        'name' => $_FILES["media"]["name"],
        'type' => $_FILES["media"]["type"],
        'size' => $_FILES["media"]["size"],
        'tmp_name' => $_FILES["media"]["tmp_name"],
        'error' => $_FILES["media"]["error"]
    ]));
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
$temp_file = $temp_dir . DIRECTORY_SEPARATOR . $temp_filename;

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

// Try to upload file to temporary location with proper permissions
if (move_uploaded_file($_FILES["media"]["tmp_name"], $temp_file)) {
    error_log("Successfully moved uploaded file to: " . $temp_file);
    
    // Set proper file permissions
    chmod($temp_file, 0644);
    
    // Verify file exists and is readable
    if (!file_exists($temp_file) || !is_readable($temp_file)) {
        error_log("File exists: " . (file_exists($temp_file) ? 'Yes' : 'No'));
        error_log("File readable: " . (is_readable($temp_file) ? 'Yes' : 'No'));
        echo json_encode(['error' => 'Failed to verify uploaded file']);
        exit;
    }

    // Add video_id column if it doesn't exist
    $alter_query = "ALTER TABLE user_media ADD COLUMN IF NOT EXISTS video_id VARCHAR(255), 
                   ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'pending'";
    if (!$conn->query($alter_query)) {
        error_log("Database alter error: " . $conn->error);
        // Clean up the uploaded file
        if (file_exists($temp_file)) {
            unlink($temp_file);
            error_log("Cleaned up temp file after database error: " . $temp_file);
        }
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    error_log("Database table structure verified successfully");

    // Normalize the relative path for database storage and URL access
    $relative_path = "uploads/" . $temp_filename;
    $file_url = "http://154.113.83.252/rowdresources/uploads/" . $temp_filename;
    error_log("Normalized relative path: " . $relative_path);
    error_log("File URL: " . $file_url);

    // Insert the initial record with the temporary filename and pending status
    $stmt = $conn->prepare("INSERT INTO user_media (user_id, file_path, media_type, video_id, status) VALUES (?, ?, 'video', ?, 'pending')");
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        if (file_exists($temp_file)) {
            unlink($temp_file);
            error_log("Cleaned up temp file after prepare error: " . $temp_file);
        }
        echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("iss", $user_id, $relative_path, $video_id);
    
    if ($stmt->execute()) {
        error_log("Successfully inserted record into database");
        echo json_encode([
            'success' => true,
            'redirect' => 'video_details.php?video=' . urlencode($relative_path) . '&video_id=' . urlencode($video_id),
            'file_url' => $file_url
        ]);
    } else {
        error_log("Database insert error: " . $stmt->error);
        // Clean up the uploaded file
        if (file_exists($temp_file)) {
            unlink($temp_file);
            error_log("Cleaned up temp file after insert error: " . $temp_file);
        }
        echo json_encode(['error' => 'Failed to save video information: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    $php_error = error_get_last();
    $error_msg = $php_error ? $php_error['message'] : 'Unknown error';
    error_log("Move uploaded file failed: " . $error_msg);
    error_log("Source file exists: " . (file_exists($_FILES["media"]["tmp_name"]) ? 'Yes' : 'No'));
    error_log("Source file readable: " . (is_readable($_FILES["media"]["tmp_name"]) ? 'Yes' : 'No'));
    error_log("Target directory writable: " . (is_writable(dirname($temp_file)) ? 'Yes' : 'No'));
    echo json_encode([
        'error' => 'Failed to upload file. Please try again.',
        'details' => $error_msg
    ]);
}

$conn->close();
error_log("Upload handling completed");
?>
