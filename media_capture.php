<?php
session_start();
include('db_config.php'); // Include your database configuration

// Set default user_id to 0 if not logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Define upload directory with proper path handling
$target_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';

// Create uploads directory if it doesn't exist
if (!file_exists($target_dir)) {
    if (!mkdir($target_dir, 0755, true)) {
        die("Failed to create upload directory. Please check permissions.");
    }
}

// Ensure upload directory is writable
if (!is_writable($target_dir)) {
    die("Upload directory is not writable. Please check permissions.");
}

// Generate a unique ID for the video
$video_id = uniqid('vid_');

// Get the file extension
$file_extension = strtolower(pathinfo($_FILES["media"]["name"], PATHINFO_EXTENSION));

// Create a temporary filename with just the ID (tags will be added later)
$temp_filename = $video_id . "." . $file_extension;
$target_file = $target_dir . DIRECTORY_SEPARATOR . $temp_filename;
$uploadOk = 1;

// Check if file was actually uploaded
if (!isset($_FILES["media"]) || $_FILES["media"]["error"] !== UPLOAD_ERR_OK) {
    $error = isset($_FILES["media"]) ? $_FILES["media"]["error"] : "No file uploaded";
    die("Upload failed: " . $error);
}

// Check if file is a video
$file_type = mime_content_type($_FILES["media"]["tmp_name"]);
if(!strstr($file_type, "video/")) {
    die("File is not a video.");
}

// Check file size (limit to 50MB)
if ($_FILES["media"]["size"] > 50000000) {
    die("Sorry, your file is too large.");
}

// Allow certain video formats
if($file_extension != "mp4" && $file_extension != "avi" && $file_extension != "mov") {
    die("Sorry, only MP4, AVI & MOV files are allowed.");
}

try {
    // Attempt to move the uploaded file
    if (!move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
        throw new Exception("Failed to move uploaded file. Check directory permissions.");
    }

    // Set proper file permissions
    if (!chmod($target_file, 0644)) {
        throw new Exception("Failed to set file permissions.");
    }

    // First check if video_id column exists
    $check_column = "SHOW COLUMNS FROM user_media LIKE 'video_id'";
    $column_exists = $conn->query($check_column)->num_rows > 0;

    // Add video_id column if it doesn't exist
    if (!$column_exists) {
        $alter_query = "ALTER TABLE user_media ADD COLUMN video_id VARCHAR(255)";
        if (!$conn->query($alter_query)) {
            throw new Exception("Error adding video_id column: " . $conn->error);
        }
    }

    // Get relative path for database storage
    $db_file_path = 'uploads' . DIRECTORY_SEPARATOR . $temp_filename;

    // Insert the initial record with the temporary filename
    $stmt = $conn->prepare("INSERT INTO user_media (user_id, file_path, media_type, video_id) VALUES (?, ?, 'video', ?)");
    $stmt->bind_param("iss", $user_id, $db_file_path, $video_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to save to database: " . $stmt->error);
    }
    
    $stmt->close();
    
    // Redirect to video details page
    header("Location: video_details.php?video=" . urlencode($db_file_path) . "&video_id=" . urlencode($video_id));
    exit();

} catch (Exception $e) {
    // Clean up the uploaded file if it exists
    if (file_exists($target_file)) {
        unlink($target_file);
    }
    die("Error: " . $e->getMessage());
}

$conn->close();
?>
