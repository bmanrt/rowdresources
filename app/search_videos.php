<?php
header('Content-Type: application/json');
require_once('../db_config.php');

// Get search parameters
$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';
$searchDescription = isset($_GET['description']) ? $_GET['description'] === 'true' : true;
$searchTags = isset($_GET['tags']) ? $_GET['tags'] === 'true' : true;
$searchCategory = isset($_GET['category']) ? $_GET['category'] === 'true' : true;

if (empty($searchTerm)) {
    echo json_encode(['videos' => [], 'message' => 'No search term provided']);
    exit;
}

// Build the search query
$conditions = [];
$params = [];
$types = '';

if ($searchDescription) {
    $conditions[] = "description LIKE ?";
    $params[] = "%$searchTerm%";
    $types .= 's';
}

if ($searchTags) {
    $conditions[] = "tags LIKE ?";
    $params[] = "%$searchTerm%";
    $types .= 's';
}

if ($searchCategory) {
    $conditions[] = "category LIKE ?";
    $params[] = "%$searchTerm%";
    $types .= 's';
}

if (empty($conditions)) {
    echo json_encode(['videos' => [], 'message' => 'No search criteria selected']);
    exit;
}

$sql = "SELECT * FROM user_media WHERE media_type = 'video' AND (" . implode(' OR ', $conditions) . ") ORDER BY created_at DESC LIMIT 20";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare statement: ' . $conn->error]);
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to execute query: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$videos = [];

while ($row = $result->fetch_assoc()) {
    // Clean up file path
    $filePath = str_replace('\\', '/', $row['file_path']);
    if (!str_starts_with($filePath, '/')) {
        $filePath = '/' . $filePath;
    }
    
    // Parse tags
    $tags = !empty($row['tags']) ? json_decode($row['tags'], true) : [];
    if (json_last_error() !== JSON_ERROR_NONE) {
        $tags = !empty($row['tags']) ? explode(',', $row['tags']) : [];
    }
    $tags = array_map('trim', $tags);

    $videoPath = '/rowd/' . $filePath;
    $physicalPath = __DIR__ . '/../' . $filePath;

    $videos[] = [
        'id' => $row['id'],
        'path' => $videoPath,
        'description' => $row['description'] ?? 'No description',
        'category' => $row['category'] ?? 'Uncategorized',
        'created_at' => date('M d, Y', strtotime($row['created_at'])),
        'size' => file_exists($physicalPath) ? filesize($physicalPath) : 0,
        'tags' => $tags
    ];
}

// Return the results
echo json_encode([
    'videos' => $videos,
    'count' => count($videos),
    'search_term' => $searchTerm
]);

$stmt->close();
$conn->close();
?>
