<?php
session_start();
require_once 'db_config.php';

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data received']);
    exit;
}

try {
    $videoPath = $data['videoPath'];
    $description = $data['description'];
    $category = $data['category'];
    $tags = $data['tags'];
    $video_id = $data['video_id'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // Default to 0 if no user is logged in

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
    
    $new_path = dirname($videoPath) . '/' . $new_filename;

    // Rename the file
    if (!rename($videoPath, $new_path)) {
        throw new Exception("Error renaming video file");
    }

    // First, let's add the necessary columns if they don't exist
    $conn->query("ALTER TABLE user_media ADD COLUMN IF NOT EXISTS description TEXT, 
                 ADD COLUMN IF NOT EXISTS category VARCHAR(255), 
                 ADD COLUMN IF NOT EXISTS tags TEXT,
                 ADD COLUMN IF NOT EXISTS video_id VARCHAR(255)");

    // Update the video details in database
    $stmt = $conn->prepare("UPDATE user_media SET description = ?, category = ?, tags = ?, file_path = ? WHERE file_path = ?");
    $stmt->bind_param("sssss", $description, $category, $tags_json, $new_path, $videoPath);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'new_path' => $new_path
        ]);
    } else {
        // If database update fails, try to rename the file back
        rename($new_path, $videoPath);
        throw new Exception("Error updating video details");
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
