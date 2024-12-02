<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

if (!isAuthenticated()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Check if all required fields are present
if (!isset($_POST['videoId'], $_POST['description'], $_POST['category'], $_POST['video'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$user_id = getCurrentUser()['id'];
$video_id = trim($_POST['videoId']);
$description = trim($_POST['description']);
$category = trim($_POST['category']);
$tags = isset($_POST['tags']) ? json_encode($_POST['tags']) : '[]';

// Clean up video path
$video_path = trim($_POST['video']);
$domain_path = "http://154.113.83.252/rowdresources";
$rowd_prefix = "/rowd/";

// Add debug logging
error_log("Original video path: " . $video_path);

// Remove domain path and rowd prefix if they exist
$video_path = str_replace(['\\', '//'], '/', $video_path);
$video_path = str_replace($domain_path, '', $video_path);
$video_path = str_replace($rowd_prefix, '', $video_path);
$video_path = ltrim($video_path, '/');

error_log("Cleaned video path: " . $video_path);

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if video exists
    $check_stmt = $conn->prepare("SELECT id FROM user_media WHERE video_id = ? AND user_id = ?");
    $check_stmt->bind_param("si", $video_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE user_media SET 
            description = ?, 
            category = ?, 
            tags = ?,
            file_path = ?
            WHERE video_id = ? AND user_id = ?");
        $stmt->bind_param("sssss", $description, $category, $tags, $video_path, $video_id, $user_id);
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO user_media 
            (user_id, video_id, description, category, tags, file_path, media_type) 
            VALUES (?, ?, ?, ?, ?, ?, 'video')");
        $stmt->bind_param("isssss", $user_id, $video_id, $description, $category, $tags, $video_path);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to save video details: " . $stmt->error);
    }

    // Commit transaction
    $conn->commit();
    
    // Return success response with redirect URL
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Video details saved successfully',
        'redirect' => 'player.php?video=' . urlencode($video_path),
        'video_path' => $video_path,
        'video_id' => $video_id
    ]);
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Error saving video details: " . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Failed to save video details: ' . $e->getMessage()
    ]);
    exit;
} finally {
    // Close all statements
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
