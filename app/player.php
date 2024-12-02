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

// Clean up video path for database lookup
$videoPath = str_replace(['\\', '//'], '/', $videoPath);
$dbPath = ltrim($videoPath, '/');  // Remove leading slash for DB comparison

error_log("Player.php - Original video path: " . $videoPath);
error_log("Player.php - DB path for lookup: " . $dbPath);

// Get video details from database
$stmt = $conn->prepare("SELECT * FROM user_media WHERE file_path = ? AND media_type = 'video' LIMIT 1");
$stmt->bind_param("s", $dbPath);
$stmt->execute();
$result = $stmt->get_result();
$video = $result->fetch_assoc();

if (!$video) {
    error_log("Player.php - Video not found in database: " . $dbPath);
    header('Location: index.php');
    exit();
}

// Format video path for frontend display
$displayPath = '/rowd/' . ltrim($dbPath, '/');
error_log("Player.php - Display path: " . $displayPath);

// Verify file exists
$physical_path = $_SERVER['DOCUMENT_ROOT'] . $displayPath;
error_log("Player.php - Physical path: " . $physical_path);
error_log("Player.php - File exists: " . (file_exists($physical_path) ? 'Yes' : 'No'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('components/head_common.php'); ?>
    <title><?php echo htmlspecialchars($video['description'] ?? 'Video Player'); ?> - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
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
            background: #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .plyr {
            --plyr-color-main: var(--primary);
            --plyr-video-background: #1a1a1a;
            --plyr-menu-background: #1a1a1a;
            --plyr-menu-color: #fff;
            --plyr-menu-border-color: rgba(255, 255, 255, 0.15);
            width: 100%;
            aspect-ratio: 16/9;
        }

        .video-info {
            margin-top: 2rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .video-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--white);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .video-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .video-meta span {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.75rem;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }

        .download-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            color: var(--primary);
            background: rgba(255, 255, 255, 0.05);
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: calc(var(--header-height) + 1rem);
            right: 1rem;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: #fff;
            font-weight: 500;
            transform: translateX(150%);
            transition: transform 0.3s ease;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .notification.success {
            background: var(--success, #28a745);
        }

        .notification.show {
            transform: translateX(0);
        }

        @media (max-width: 768px) {
            .player-container {
                padding: 1rem;
                padding-top: calc(var(--header-height) + 1rem);
            }

            .video-info {
                padding: 1.5rem;
            }

            .video-title {
                font-size: 1.5rem;
            }

            .video-meta {
                gap: 1rem;
            }

            .video-meta span {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
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
                id="player"
                playsinline
                controls
                data-poster="/path/to/poster.jpg"
            >
                <source src="<?php echo htmlspecialchars($displayPath); ?>" type="video/mp4" />
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
                <?php if (!empty($video['tags'])): ?>
                <span>
                    <i class="fas fa-tags"></i>
                    <?php 
                        $tags = json_decode($video['tags'], true);
                        echo htmlspecialchars(implode(', ', $tags ?? []));
                    ?>
                </span>
                <?php endif; ?>
            </div>
            <a href="<?php echo htmlspecialchars($videoPath); ?>" download class="download-btn">
                <i class="fas fa-download"></i>
                <span>Download Video</span>
            </a>
        </div>
    </div>

    <script src="shared.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const player = new Plyr('#player', {
                controls: [
                    'play-large',
                    'play',
                    'progress',
                    'current-time',
                    'mute',
                    'volume',
                    'captions',
                    'settings',
                    'pip',
                    'airplay',
                    'fullscreen'
                ]
            });

            // Error handling
            const video = document.querySelector('#player');
            
            video.addEventListener('error', function(e) {
                console.error('Video error:', e);
                console.error('Error code:', video.error ? video.error.code : 'N/A');
                console.error('Error message:', video.error ? video.error.message : 'N/A');
                console.log('Video source:', video.querySelector('source').src);
            });

            video.addEventListener('loadstart', function() {
                console.log('Video load started');
            });

            video.addEventListener('loadedmetadata', function() {
                console.log('Video metadata loaded');
                console.log('Duration:', video.duration);
                console.log('Dimensions:', video.videoWidth, 'x', video.videoHeight);
            });

            player.on('ready', () => {
                console.log('Player is ready');
            });

            player.on('error', (error) => {
                console.error('Plyr error:', error);
            });
        });
    </script>
</body>
</html>
