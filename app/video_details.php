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
        /* Previous styles remain unchanged */
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
                    <source src="<?php echo htmlspecialchars($display_path); ?>" type="<?php echo $video_mime_type; ?>">
                    <source src="<?php echo htmlspecialchars($display_path); ?>" type="video/mp4">
                    <source src="<?php echo htmlspecialchars($display_path); ?>" type="video/webm">
                    <source src="<?php echo htmlspecialchars($display_path); ?>" type="video/ogg">
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
                console.log('Video source:', video.querySelector('source').src);
                console.log('Video type:', video.querySelector('source').type);
                // Try alternative video types
                const sources = video.getElementsByTagName('source');
                let currentSource = 0;
                video.addEventListener('error', function(e) {
                    currentSource++;
                    if (currentSource < sources.length) {
                        video.src = sources[currentSource].src;
                        video.load();
                    }
                });
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
.upload-container {
            padding-top: calc(var(--header-height) + 2rem);
            max-width: 800px;
            margin: 0 auto;
            padding-left: 2rem;
            padding-right: 2rem;
        }

        .upload-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .upload-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .upload-form {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--white);
            font-weight: 500;
        }

        .form-group textarea,
        .form-group select,
        .select2-container--default .select2-selection--multiple {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: var(--white);
            transition: all 0.3s ease;
        }

        .form-group textarea:focus,
        .form-group select:focus,
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            outline: none;
        }

        .video-preview {
            margin-bottom: 2rem;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.2);
        }

        .video-preview video {
            width: 100%;
            max-height: 400px;
            display: block;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Select2 Dark Theme Customization */
        .select2-container--default .select2-selection--multiple {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--primary);
            border: none;
            color: var(--white);
            border-radius: 4px;
            padding: 2px 8px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: var(--white);
            margin-right: 5px;
        }

        .select2-dropdown {
            background-color: var(--dark-bg);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .select2-container--default .select2-results__option {
            color: var(--white);
            padding: 8px 12px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary);
        }

        .select2-container--default .select2-search--inline .select2-search__field {
            color: var(--white);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 0 8px;
        }

        .select2-container--default .select2-search--inline .select2-search__field::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        @media (max-width: 768px) {
            .upload-container {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .upload-header h1 {
                font-size: 2rem;
            }

            .upload-form {
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
        
        <div class="upload-form">
            <div class="video-preview">
                <video id="videoPreview" controls>
                    <source src="<?php echo htmlspecialchars($display_path); ?>" type="video/mp4">
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
