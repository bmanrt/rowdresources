<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

// Ensure user is authenticated
if (!isAuthenticated()) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Authentication required'
    ]);
    exit();
}

// Get current user data
$currentUser = getCurrentUser();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["media"])) {
    header('Content-Type: application/json');
    
    try {
        $target_dir = "var/www/html/rowdresources/uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate a unique ID for the video
        $video_id = uniqid('vid_');
        
        // Get the file extension
        $file_extension = strtolower(pathinfo($_FILES["media"]["name"], PATHINFO_EXTENSION));
        
        // Create a temporary filename with just the ID
        $temp_filename = $video_id . "." . $file_extension;
        $target_file = $target_dir . $temp_filename;
        
        // Check if file is a video
        $file_type = mime_content_type($_FILES["media"]["tmp_name"]);
        if(!strstr($file_type, "video/")) {
            throw new Exception("File is not a video.");
        }
        
        // Check file size (limit to 50MB)
        if ($_FILES["media"]["size"] > 50000000) {
            throw new Exception("Sorry, your file is too large. Maximum size is 50MB.");
        }
        
        // Allow certain video formats
        if($file_extension != "mp4" && $file_extension != "avi" && $file_extension != "mov") {
            throw new Exception("Sorry, only MP4, AVI & MOV files are allowed.");
        }
        
        if (!move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
            throw new Exception("Sorry, there was an error uploading your file.");
        }
        
        // Add video_id column if it doesn't exist
        $alter_query = "ALTER TABLE user_media ADD COLUMN IF NOT EXISTS video_id VARCHAR(255)";
        if (!$conn->query($alter_query)) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        // Insert the initial record with the temporary filename
        $stmt = $conn->prepare("INSERT INTO user_media (user_id, file_path, media_type, video_id) VALUES (?, ?, 'video', ?)");
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        
        $relative_path = "uploads/" . $temp_filename;
        $stmt->bind_param("iss", $currentUser['id'], $relative_path, $video_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Database execute error: " . $stmt->error);
        }
        
        $stmt->close();
        
        echo json_encode([
            "success" => true,
            "redirect" => "video_details.php?video=" . urlencode($relative_path) . "&video_id=" . urlencode($video_id)
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => $e->getMessage()
        ]);
    }
    exit;
} else {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit();
}
?>
