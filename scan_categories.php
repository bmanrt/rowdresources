<?php
include('db_config.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get distinct categories
    $query = "SELECT DISTINCT category FROM user_media WHERE media_type = 'video' AND category IS NOT NULL";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    echo "=== Current Video Categories ===\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['category'] . "\n";
    }
    
    // Get video count per category
    echo "\n=== Videos per Category ===\n";
    $query = "SELECT category, COUNT(*) as count 
             FROM user_media 
             WHERE media_type = 'video' 
             GROUP BY category";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        echo $row['category'] . ": " . $row['count'] . " videos\n";
    }
    
    // Get all video entries with their details
    echo "\n=== Video Details ===\n";
    $query = "SELECT id, file_path, category, description, tags, created_at 
             FROM user_media 
             WHERE media_type = 'video' 
             ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        echo "\nID: " . $row['id'] . "\n";
        echo "Category: " . ($row['category'] ?? 'Uncategorized') . "\n";
        echo "Description: " . ($row['description'] ?? 'No description') . "\n";
        echo "Tags: " . ($row['tags'] ?? 'No tags') . "\n";
        echo "Created: " . $row['created_at'] . "\n";
        echo "File: " . $row['file_path'] . "\n";
        echo "----------------------------------------\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
