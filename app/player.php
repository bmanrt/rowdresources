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

// Get video path from URL
$videoPath = $_GET['video'] ?? '';
if (empty($videoPath)) {
    header('Location: index.php');
    exit();
}

// Clean up video path
$videoPath = str_replace('\\', '/', $videoPath);
if (!str_starts_with($videoPath, '/')) {
    $videoPath = '/' . $videoPath;
}

// Get video details from database
$stmt = $conn->prepare("SELECT * FROM user_media WHERE file_path = ? AND media_type = 'video' LIMIT 1");
$dbPath = ltrim($videoPath, '/rowd/');
$stmt->bind_param("s", $dbPath);
$stmt->execute();
$result = $stmt->get_result();
$video = $result->fetch_assoc();

if (!$video) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['description'] ?? 'Video Player'); ?> - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://vjs.zencdn.net/8.5.2/video-js.css" rel="stylesheet" />
    <style>
        .player-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            padding-top: calc(var(--header-height) + 2rem);
        }
        .video-container {
            position: relative;
            width: 100%;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .video-js {
            width: 100%;
            aspect-ratio: 16/9;
        }
        .video-info {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .video-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        .video-meta {
            display: flex;
            gap: 1.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .video-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }
        .download-btn:hover {
            background: var(--primary-dark);
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
        }
        .back-btn:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <?php include('components/header.php'); ?>
    
    <div class="player-container">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Back</span>
        </a>
        
        <div class="video-container">
            <video 
                id="videoPlayer" 
                class="video-js vjs-default-skin vjs-big-play-centered"
                controls
                preload="auto"
                data-setup='{"fluid": true, "playbackRates": [0.5, 1, 1.25, 1.5, 2]}'
            >
                <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
                <p class="vjs-no-js">
                    To view this video please enable JavaScript, and consider upgrading to a
                    web browser that <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                </p>
            </video>
        </div>
        
        <div class="video-info">
            <h1 class="video-title"><?php echo htmlspecialchars($video['description'] ?? 'Untitled Video'); ?></h1>
            <div class="video-meta">
                <span>
                    <i class="fas fa-folder"></i>
                    <?php echo htmlspecialchars($video['category'] ?? 'Uncategorized'); ?>
                </span>
                <span>
                    <i class="fas fa-calendar"></i>
                    <?php echo date('M d, Y', strtotime($video['created_at'])); ?>
                </span>
            </div>
            <a href="<?php echo htmlspecialchars($videoPath); ?>" download class="download-btn">
                <i class="fas fa-download"></i>
                <span>Download Video</span>
            </a>
        </div>
    </div>

    <script src="https://vjs.zencdn.net/8.5.2/video.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var player = videojs('videoPlayer', {
                controls: true,
                fluid: true,
                html5: {
                    vhs: {
                        overrideNative: true
                    }
                }
            });

            // Add quality selector if needed in the future
            player.on('loadedmetadata', function() {
                // You can add quality selection logic here
            });
        });
    </script>
</body>
</html>
