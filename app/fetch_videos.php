<?php
header('Content-Type: application/json');
require_once('../db_config.php');

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : 'videos';

if ($action === 'categories') {
    // Load all categories from JSON file for the header dropdown
    $categoriesJson = file_get_contents(__DIR__ . '/../data/categories.json');
    $categoriesData = json_decode($categoriesJson, true);
    
    if (json_last_error() === JSON_ERROR_NONE && isset($categoriesData['categories'])) {
        $categories = $categoriesData['categories'];
        sort($categories); // Sort alphabetically
        echo json_encode(['categories' => $categories]);
    } else {
        // Fallback to database if JSON fails
        $sql = "SELECT DISTINCT category FROM user_media 
                WHERE category IS NOT NULL 
                AND category != '' 
                ORDER BY category";
        $result = $conn->query($sql);
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        
        echo json_encode(['categories' => $categories]);
    }
    exit;
}

if ($action === 'categories_with_videos') {
    // Get all categories that have videos from the database
    $categoriesSql = "SELECT DISTINCT category FROM user_media 
                      WHERE media_type = 'video' 
                      AND category IS NOT NULL 
                      AND category != '' 
                      ORDER BY category";
    $categoriesResult = $conn->query($categoriesSql);
    
    $categories = [];
    while ($categoryRow = $categoriesResult->fetch_assoc()) {
        $categoryName = $categoryRow['category'];
        
        // Get video count for this category
        $countSql = "SELECT COUNT(*) as count FROM user_media WHERE media_type = 'video' AND category = ?";
        $stmt = $conn->prepare($countSql);
        $stmt->bind_param('s', $categoryName);
        $stmt->execute();
        $countResult = $stmt->get_result();
        $countRow = $countResult->fetch_assoc();
        $videoCount = $countRow['count'];
        
        if ($videoCount > 0) {
            // Get videos for this category
            $videosSql = "SELECT * FROM user_media 
                         WHERE media_type = 'video' 
                         AND category = ? 
                         ORDER BY created_at DESC 
                         LIMIT 10";
                         
            $stmt = $conn->prepare($videosSql);
            $stmt->bind_param('s', $categoryName);
            $stmt->execute();
            $videosResult = $stmt->get_result();
            
            $videos = [];
            while ($video = $videosResult->fetch_assoc()) {
                // Clean up file path
                $filePath = str_replace(['\\', '//'], '/', $video['file_path']);
                $filePath = ltrim($filePath, '/');
                
                // Parse tags
                $tags = !empty($video['tags']) ? json_decode($video['tags'], true) : [];
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $tags = !empty($video['tags']) ? explode(',', $video['tags']) : [];
                }
                $tags = array_map('trim', $tags);

                // Use the correct domain path for videos
                $videoPath = 'http://154.113.83.252/rowdresources/' . $filePath;

                $videos[] = [
                    'id' => $video['id'],
                    'path' => $videoPath,
                    'description' => $video['description'] ?? 'No description',
                    'category' => $video['category'] ?? 'Uncategorized',
                    'created_at' => date('M d, Y', strtotime($video['created_at'])),
                    'tags' => $tags
                ];
            }
            
            $categories[] = [
                'name' => $categoryName,
                'video_count' => $videoCount,
                'videos' => $videos
            ];
        }
    }
    
    echo json_encode(['categories' => $categories]);
    exit;
}

// Original video fetching logic for a specific category or all videos
$category = isset($_GET['category']) ? $_GET['category'] : '';

error_log("Fetching videos - Category: " . ($category ?: 'All'));

// Use prepared statement for better security
$sql = "SELECT * FROM user_media WHERE media_type = 'video'";
$params = [];
$types = "";

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}
$sql .= " ORDER BY created_at DESC LIMIT 8";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    die(json_encode(['error' => 'Failed to prepare query']));
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    error_log("Query execution failed: " . $stmt->error);
    die(json_encode(['error' => 'Failed to execute query']));
}

$result = $stmt->get_result();
$videos = [];

while ($row = $result->fetch_assoc()) {
    // Clean up file path using the same logic as player.php
    $filePath = str_replace(['\\', '//'], '/', $row['file_path']);
    $filePath = ltrim($filePath, '/');
    
    // Build video paths with correct domain
    $videoPath = 'http://154.113.83.252/rowdresources/' . $filePath;
    
    error_log("Processing video - ID: {$row['id']}, Path: {$filePath}");
    
    // Parse tags
    $tags = !empty($row['tags']) ? json_decode($row['tags'], true) : [];
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Warning: Invalid JSON in tags for video ID {$row['id']}");
        $tags = !empty($row['tags']) ? explode(',', $row['tags']) : [];
    }
    $tags = array_map('trim', $tags);

    $videos[] = [
        'id' => $row['id'],
        'path' => $videoPath,
        'description' => $row['description'] ?? 'No description',
        'category' => $row['category'] ?? 'Uncategorized',
        'created_at' => date('M d, Y', strtotime($row['created_at'])),
        'tags' => $tags
    ];
}

error_log("Fetched " . count($videos) . " videos");

echo json_encode(['videos' => $videos]);
$conn->close();
?>
