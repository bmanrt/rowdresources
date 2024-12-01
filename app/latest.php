<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

// Ensure user is authenticated
if (!isAuthenticated()) {
    redirectToLogin();
    exit();
}

// Get current user data
$currentUser = getCurrentUser();

// Get all videos ordered by date
$sql = "SELECT * FROM user_media 
        WHERE media_type = 'video' 
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latest Videos - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
</head>
<body>
    <?php include('components/header.php'); ?>

    <div class="container" style="padding-top: calc(var(--header-height) + 2rem);">
        <div class="section-header">
            <div class="header-left">
                <h2>Latest Videos</h2>
            </div>
        </div>

        <div class="videos-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($video = $result->fetch_assoc()) {
                    // Clean up file path
                    $filePath = str_replace('\\', '/', $video['file_path']);
                    if (!str_starts_with($filePath, '/')) {
                        $filePath = '/' . $filePath;
                    }
                    
                    $videoPath = '/rowd' . $filePath;
                    ?>
                    <div class="video-card" onclick="openVideoModal('<?php echo htmlspecialchars($videoPath); ?>', '<?php echo htmlspecialchars($video['description'] ?? 'No description'); ?>', '<?php echo htmlspecialchars($video['category'] ?? 'Uncategorized'); ?>', '<?php echo htmlspecialchars(date('M d, Y', strtotime($video['created_at']))); ?>')">
                        <div class="video-thumbnail">
                            <video src="<?php echo htmlspecialchars($videoPath); ?>" preload="metadata"></video>
                        </div>
                        <div class="video-info">
                            <h3 class="video-title"><?php echo htmlspecialchars($video['description'] ?? 'No description'); ?></h3>
                            <div class="video-meta">
                                <span><i class="fas fa-folder"></i><?php echo htmlspecialchars($video['category'] ?? 'Uncategorized'); ?></span>
                                <span><i class="fas fa-calendar"></i><?php echo date('M d, Y', strtotime($video['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-videos">No videos available</p>';
            }
            ?>
        </div>
    </div>

    <script src="script.js"></script>
    <script src="shared.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initializeDropdowns();
            loadCategories();
        });
    </script>
</body>
</html>
