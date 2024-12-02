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
if (!str_starts_with($display_path, '/rowd/')) {
    $display_path = '/rowd/' . ltrim($display_path, '/');
}

error_log("Final display_path: " . $display_path);

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
error_log("Document root: " . $_SERVER['DOCUMENT_ROOT']);
error_log("File exists check: " . (file_exists($physical_path) ? 'true' : 'false'));
error_log("File readable check: " . (is_readable($physical_path) ? 'true' : 'false'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('components/head_common.php'); ?>
    <title>Video Details - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .upload-container {
            padding-top: calc(var(--header-height) + 2rem);
            max-width: 800px;
            margin: 0 auto;
            padding-left: 2rem;
            padding-right: 2rem;
            min-height: calc(100vh - var(--header-height));
            display: flex;
            flex-direction: column;
        }

        .video-form {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex: 1;
        }

        .video-preview {
            width: 100%;
            border-radius: 12px;
            margin-bottom: 2rem;
            background: rgba(0, 0, 0, 0.2);
            position: relative;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            overflow: hidden;
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--white);
            font-weight: 500;
            font-size: 0.9rem;
        }

        textarea, select, .select2-container--default .select2-selection--multiple {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.2);
            color: var(--white);
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 1rem) center;
            padding-right: 2.5rem;
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 100px;
            background: rgba(0, 0, 0, 0.2) !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: var(--primary) !important;
            border: none !important;
            border-radius: 4px !important;
            color: var(--white) !important;
            padding: 4px 8px !important;
            margin: 4px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: var(--white) !important;
            margin-right: 5px !important;
            border-right: none !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 4px !important;
        }

        .select2-dropdown {
            background: var(--background) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--primary) !important;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: auto;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-primary i {
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .upload-container {
                padding: calc(var(--header-height) + 1rem) 1rem 2rem;
            }

            .video-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include('components/header.php'); ?>
    
    <div class="upload-container">
        <div class="upload-header">
            <h1><?php echo $is_new_upload ? 'Add Video Details' : 'Edit Video Details'; ?></h1>
        </div>
        
        <div class="video-form">
            <div class="video-preview">
                <video id="videoPreview" controls preload="metadata" controlsList="nodownload">
                    <source src="<?php echo htmlspecialchars($display_path); ?>" type="<?php echo $video_mime_type; ?>">
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

                <button type="submit" class="btn-primary">
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

            // Video error handling with detailed logging
            const video = document.getElementById('videoPreview');
            
            video.addEventListener('error', function(e) {
                console.error('Video error:', e);
                console.error('Error code:', video.error ? video.error.code : 'N/A');
                console.error('Error message:', video.error ? video.error.message : 'N/A');
                console.log('Video source:', video.querySelector('source').src);
                console.log('Video type:', video.querySelector('source').type);
            });

            video.addEventListener('loadstart', function() {
                console.log('Video load started');
            });

            video.addEventListener('loadedmetadata', function() {
                console.log('Video metadata loaded');
                console.log('Duration:', video.duration);
                console.log('Dimensions:', video.videoWidth, 'x', video.videoHeight);
            });

            video.addEventListener('canplay', function() {
                console.log('Video can start playing');
            });

            // Form submission with video path
            $('#videoDetailsForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                console.log('Submitting form with video path:', formData.get('video'));
                
                $.ajax({
                    url: 'save_video_details.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Save response:', response);
                        if (response.success) {
                            window.location.href = response.redirect;
                        } else {
                            alert(response.error || 'Failed to save video details');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Save error:', error);
                        alert('Failed to save video details: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html>
