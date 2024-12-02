<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

if (!isAuthenticated()) {
    header("Location: ../login.html");
    exit();
}

// Load categories and tags from JSON files
$categories_json = file_get_contents('../data/categories.json');
$tags_json = file_get_contents('../data/tags.json');

$categories = json_decode($categories_json, true)['categories'];
$tags_data = json_decode($tags_json, true)['tags'];

// Flatten tags array
$all_tags = [];
foreach ($tags_data as $category => $tags) {
    foreach ($tags as $tag) {
        $all_tags[] = $tag;
    }
}

// Get video information from URL parameters
$video_path = isset($_GET['video']) ? $_GET['video'] : '';
$video_id = isset($_GET['video_id']) ? $_GET['video_id'] : '';

if (empty($video_path) || empty($video_id)) {
    header("Location: index.php");
    exit();
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

        /* Select2 Custom Styling */
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
            background-color: var(--background);
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
    </style>
</head>
<body>
    <?php include('components/header.php'); ?>
    
    <div class="upload-container">
        <div class="upload-header">
            <h1>Video Details</h1>
        </div>
        
        <div class="upload-form">
            <div class="video-preview">
                <video id="videoPreview" controls>
                    <source src="../<?php echo htmlspecialchars($video_path); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <form id="videoDetailsForm" action="save_video_details.php" method="POST">
                <input type="hidden" name="video" value="<?php echo htmlspecialchars($video_path); ?>">
                <input type="hidden" name="videoId" value="<?php echo htmlspecialchars($video_id); ?>">
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="">Select a category</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>">
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tags">Tags:</label>
                    <select id="tags" name="tags[]" multiple="multiple" required>
                        <?php foreach($all_tags as $tag): ?>
                            <option value="<?php echo htmlspecialchars($tag); ?>">
                                <?php echo htmlspecialchars($tag); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Save Details
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tags').select2({
                placeholder: 'Select tags',
                allowClear: true,
                theme: 'default'
            });
        });
    </script>
</body>
</html>
