<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure user is authenticated
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data received']);
    exit();
}

try {
    $video_id = $data['video_id'];
    $description = $data['description'];
    $category = $data['category'];
    $tags = $data['tags'];
    $videoPath = $data['videoPath'];

    // Convert tags array to string for filename
    $tags_string = implode('-', array_slice($tags, 0, 3)); // Use up to 3 tags in filename
    $tags_json = json_encode($tags); // Store all tags as JSON in database

    // Get file extension from current path
    $file_extension = pathinfo($videoPath, PATHINFO_EXTENSION);
    
    // Create new filename with video ID, category, and tags
    $new_filename = sprintf(
        'vid_%s_%s_%s.%s',
        $video_id,
        strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $category)),
        strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '', $tags_string)),
        $file_extension
    );

    // Server paths
    $uploads_dir = "/var/www/html/rowdresources/uploads/";
    $current_file = $uploads_dir . basename($videoPath);
    $new_file = $uploads_dir . $new_filename;
    
    // Web accessible paths
    $web_path = "/rowdresources/uploads/";
    $public_url = $web_path . $new_filename;

    // Log paths for debugging
    error_log("Current file: " . $current_file);
    error_log("New file: " . $new_file);
    error_log("Public URL: " . $public_url);

    // Check if source file exists
    if (!file_exists($current_file)) {
        throw new Exception("Source file not found: " . basename($videoPath));
    }

    // Check if target directory is writable
    if (!is_writable($uploads_dir)) {
        throw new Exception("Upload directory is not writable");
    }

    // Rename the file
    if (!rename($current_file, $new_file)) {
        $error = error_get_last();
        throw new Exception("Error renaming file: " . ($error['message'] ?? 'Unknown error'));
    }

    // Update database
    $stmt = $conn->prepare("UPDATE user_media SET description = ?, category = ?, tags = ?, file_path = ? WHERE video_id = ? AND user_id = ?");
    $stmt->bind_param("sssssi", $description, $category, $tags_json, $public_url, $video_id, $_SESSION['user_id']);

    if (!$stmt->execute()) {
        // If database update fails, try to rename file back
        @rename($new_file, $current_file);
        throw new Exception("Database update failed: " . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'new_path' => $public_url
    ]);

} catch (Exception $e) {
    error_log("Error in save_video_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
