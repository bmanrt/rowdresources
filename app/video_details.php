<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("Starting video_details.php");

if (!isAuthenticated()) {
    header("Location: login.php");
    exit();
}

// Load categories and tags from JSON files
try {
    $categories_json = file_get_contents('../data/categories.json');
    $tags_json = file_get_contents('../data/tags.json');

    $categories = json_decode($categories_json, true)['categories'] ?? [];
    $tags_data = json_decode($tags_json, true)['tags'] ?? [];

    if (empty($categories) || empty($tags_data)) {
        throw new Exception("Failed to load categories or tags data");
    }

    // Flatten tags array
    $all_tags = [];
    foreach ($tags_data as $category => $tags) {
        foreach ($tags as $tag) {
            $all_tags[] = $tag;
        }
    }
} catch (Exception $e) {
    error_log("Error loading JSON data: " . $e->getMessage());
    $categories = [];
    $all_tags = [];
}

// Get video information from URL parameters
$video_path = isset($_GET['video']) ? trim($_GET['video']) : '';
$video_id = isset($_GET['video_id']) ? trim($_GET['video_id']) : '';

error_log("Received video_path: " . $video_path);
error_log("Received video_id: " . $video_id);

// For newly uploaded videos, we only have the video path
if (empty($video_path)) {
    header("Location: index.php");
    exit();
}

// If video_id is not set, this is a new upload
$is_new_upload = empty($video_id);

// Get existing video details if editing
$existing_video = null;
if (!$is_new_upload) {
    $stmt = $conn->prepare("SELECT * FROM user_media WHERE video_id = ? AND user_id = ?");
    $user_id = getCurrentUser()['id'];
    $stmt->bind_param("si", $video_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_video = $result->fetch_assoc();
    $stmt->close();

    if ($existing_video) {
        error_log("Found existing video: " . json_encode($existing_video));
        // If editing, use the file_path from database
        $video_path = $existing_video['file_path'];
    }
}

// Clean up video path for display
$display_path = $video_path;
if (!str_starts_with($display_path, '/')) {
    $display_path = '/' . $display_path;
}

// Build the full video URL for remote server
$video_url = "http://154.113.83.252/rowdresources/uploads/videos" . basename($display_path);

error_log("Original path: " . $video_path);
error_log("Final video URL: " . $video_url);

// Determine video MIME type
$file_extension = strtolower(pathinfo($display_path, PATHINFO_EXTENSION));
$video_mime_type = 'video/mp4'; // Default
switch ($file_extension) {
    case 'webm':
        $video_mime_type = 'video/webm';
        break;
    case 'ogg':
    case 'ogv':
        $video_mime_type = 'video/ogg';
        break;
}

// Check if video file exists
$physical_path = $_SERVER['DOCUMENT_ROOT'] . $display_path;
error_log("Checking physical path: " . $physical_path);
if (!file_exists($physical_path)) {
    error_log("Warning: Video file not found at: " . $physical_path);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('components/head_common.php'); ?>
    <title>Video Details - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .video-preview {
            margin-bottom: 2rem;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.2);
            position: relative;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
        }

        .video-preview video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000;
        }

        /* Video controls customization */
        video::-webkit-media-controls {
            background-color: rgba(0, 0, 0, 0.5);
        }

        video::-webkit-media-controls-panel {
            display: flex !important;
            opacity: 1 !important;
        }
    </style>
</head>
<body>
    <?php include('components/header.php'); ?>
    
    <div class="upload-container">
        <div class="upload-header">
            <h1><?php echo $is_new_upload ? 'Add Video Details' : 'Edit Video Details'; ?></h1>
        </div>
        
        <div class="upload-form">
            <div class="video-preview">
                <video id="videoPreview" controls preload="metadata" controlsList="nodownload">
                    <source src="<?php echo htmlspecialchars($video_url); ?>" type="<?php echo $video_mime_type; ?>">
                    Your browser does not support the video tag.
                </video>
            </div>

            <form id="videoDetailsForm" action="save_video_details.php" method="POST">
                <input type="hidden" name="video" value="<?php echo htmlspecialchars($video_path); ?>">
                <input type="hidden" name="videoId" value="<?php echo htmlspecialchars($video_id); ?>">
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($existing_video['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="">Select a category</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>"
                                <?php echo ($existing_video && $existing_video['category'] === $category) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tags">Tags:</label>
                    <select id="tags" name="tags[]" multiple="multiple" required>
                        <?php 
                        $selected_tags = [];
                        if ($existing_video && !empty($existing_video['tags'])) {
                            $selected_tags = json_decode($existing_video['tags'], true) ?? [];
                        }
                        foreach($all_tags as $tag): 
                        ?>
                            <option value="<?php echo htmlspecialchars($tag); ?>"
                                <?php echo in_array($tag, $selected_tags) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tag); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> 
                    <?php echo $is_new_upload ? 'Save Details' : 'Update Details'; ?>
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#tags').select2({
                placeholder: 'Select tags',
                allowClear: true,
                theme: 'default',
                width: '100%'
            });

            // Video error handling
            const video = document.getElementById('videoPreview');
            video.addEventListener('error', function(e) {
                console.error('Video error:', e);
                console.log('Video URL:', video.querySelector('source').src);
                console.log('Video type:', video.querySelector('source').type);
            });

            // Log when video starts playing
            video.addEventListener('playing', function() {
                console.log('Video started playing');
            });

            // Log if video fails to load
            video.addEventListener('loadeddata', function() {
                console.log('Video loaded successfully');
            });

            // Form validation
            $('#videoDetailsForm').on('submit', function(e) {
                const description = $('#description').val().trim();
                const category = $('#category').val();
                const tags = $('#tags').val();

                if (!description) {
                    e.preventDefault();
                    alert('Please enter a description');
                    return false;
                }

                if (!category) {
                    e.preventDefault();
                    alert('Please select a category');
                    return false;
                }

                if (!tags || tags.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one tag');
                    return false;
                }

                return true;
            });
        });
    </script>
</body>
</html>
