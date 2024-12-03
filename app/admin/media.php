<?php
require_once '../../db_config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch media content with error handling
$stmt = $conn->prepare("
    SELECT m.*, u.username 
    FROM user_media m 
    JOIN app_users u ON m.user_id = u.id 
    ORDER BY m.created_at DESC
");

if (!$stmt) {
    $error = "Failed to prepare statement: " . $conn->error;
} else {
    if (!$stmt->execute()) {
        $error = "Failed to execute query: " . $stmt->error;
    } else {
        $media = $stmt->get_result();
        if (!$media) {
            $error = "Failed to get results: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Management - Rowd Resources</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #2c3e50;
            color: white;
            padding-top: 1rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.8rem 1.5rem;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .media-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .media-thumbnail {
            position: relative;
            padding-top: 56.25%;
            background: #f8f9fa;
        }
        .media-thumbnail img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .media-info {
            padding: 1rem;
        }
        .media-info h5 {
            margin: 0 0 0.5rem 0;
            color: #2c3e50;
        }
        .media-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        .media-actions {
            padding: 0.5rem 1rem;
            border-top: 1px solid #eee;
            display: flex;
            gap: 0.5rem;
        }
        .logo-section {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }
        .logo-section h4 {
            color: white;
            margin: 0;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .empty-state i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .error-state {
            text-align: center;
            padding: 2rem;
            background: #fff3f3;
            border-radius: 10px;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-section">
            <h4>Rowd Resources</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.php">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="media.php">
                    <i class="bi bi-collection-play"></i> Media
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Media Management</h4>
        </div>

        <!-- Media Grid -->
        <div class="card">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="error-state">
                        <i class="bi bi-exclamation-triangle fs-1 mb-3"></i>
                        <h5>Error Loading Media</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php else: ?>
                    <?php if ($media->num_rows > 0): ?>
                        <div class="media-grid">
                            <?php while ($item = $media->fetch_assoc()): ?>
                            <div class="media-card">
                                <div class="media-thumbnail">
                                    <img src="<?php echo htmlspecialchars($item['thumbnail_url'] ?? '/assets/img/default-thumbnail.jpg'); ?>" 
                                         alt="Media Thumbnail"
                                         onerror="this.src='/assets/img/default-thumbnail.jpg'">
                                </div>
                                <div class="media-info">
                                    <h5><?php echo htmlspecialchars($item['media_name']); ?></h5>
                                    <p>Uploaded by: <?php echo htmlspecialchars($item['username']); ?></p>
                                    <p>Upload date: <?php echo date('M j, Y', strtotime($item['created_at'])); ?></p>
                                </div>
                                <div class="media-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewMedia(<?php echo $item['id']; ?>)">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteMedia(<?php echo $item['id']; ?>)">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-collection-play"></i>
                            <h5>No Media Found</h5>
                            <p class="text-muted mb-0">No media files have been uploaded yet.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewMedia(mediaId) {
            // Implement view media functionality
            console.log('View media:', mediaId);
        }

        function deleteMedia(mediaId) {
            if (confirm('Are you sure you want to delete this media item?')) {
                // Implement delete media functionality
                console.log('Delete media:', mediaId);
            }
        }
    </script>
</body>
</html>
