<?php
session_start();
require_once('auth_check.php');

// Ensure user is authenticated
if (!isAuthenticated()) {
    redirectToLogin();
    exit();
}

require_once('../db_config.php');

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data received']);
    exit;
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
    
    // Check and add necessary columns if they don't exist
    $required_columns = ['description', 'category', 'tags', 'video_id'];
    foreach ($required_columns as $column) {
        $check_column = "SHOW COLUMNS FROM user_media LIKE '$column'";
        $column_exists = $conn->query($check_column)->num_rows > 0;
        
        if (!$column_exists) {
            $type = ($column === 'description' || $column === 'tags') ? 'TEXT' : 'VARCHAR(255)';
            $alter_query = "ALTER TABLE user_media ADD COLUMN $column $type";
            $conn->query($alter_query);
        }
    }

    // Create new filename with video ID, category, and tags
    $new_filename = sprintf(
        'vid_%s_%s_%s.%s',
        $video_id,
        strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $category)),
        strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '', $tags_string)),
        $file_extension
    );
    
    // Use absolute path for file operations
    $uploads_dir = "/var/www/html/rowdresources/uploads/";
    $current_file = $uploads_dir . basename($videoPath);
    $new_path = $uploads_dir . $new_filename;
    $public_url = "/rowdresources/uploads/" . $new_filename;

    // Debug logging
    error_log("Current file path: " . $current_file);
    error_log("New file path: " . $new_path);
    error_log("Original video path: " . $videoPath);
    
    // Check if source file exists
    if (!file_exists($current_file)) {
        error_log("Source file does not exist: " . $current_file);
        throw new Exception("Source video file not found");
    }
    
    // Check directory permissions
    if (!is_writable($uploads_dir)) {
        error_log("Upload directory is not writable: " . $uploads_dir);
        throw new Exception("Upload directory is not writable");
    }

    // Try to rename the file
    if (!rename($current_file, $new_path)) {
        $error = error_get_last();
        error_log("Rename error details: " . print_r($error, true));
        throw new Exception("Error renaming video file: " . ($error['message'] ?? 'Unknown error'));
    }

    // Update the video details in database
    $stmt = $conn->prepare("UPDATE user_media SET description = ?, category = ?, tags = ?, file_path = ? WHERE video_id = ? AND user_id = ?");
    $stmt->bind_param("sssssi", $description, $category, $tags_json, $public_url, $video_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'new_path' => $public_url
        ]);
    } else {
        // If database update fails, try to rename the file back
        rename($new_path, $current_file);
        throw new Exception("Error updating video details");
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
