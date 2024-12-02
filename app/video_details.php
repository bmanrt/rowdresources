<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

// Ensure user is authenticated
if (!isAuthenticated()) {
    redirectToLogin();
    exit();
}

$video = $_GET['video'] ?? '';
$video_id = $_GET['video_id'] ?? '';

if (empty($video) || empty($video_id)) {
    header("Location: index.php");
    exit();
}

// Fetch video details from database
$stmt = $conn->prepare("SELECT * FROM user_media WHERE video_id = ? AND user_id = ?");
$stmt->bind_param("si", $video_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$video_details = $result->fetch_assoc();

// Fetch categories from database
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch tags from database
$tags_query = "SELECT * FROM tags ORDER BY category, name";
$tags_result = $conn->query($tags_query);
$tags_by_category = [];
while ($row = $tags_result->fetch_assoc()) {
    if (!isset($tags_by_category[$row['category']])) {
        $tags_by_category[$row['category']] = [];
    }
    $tags_by_category[$row['category']][] = $row['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Details - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .details-container {
            max-width: 800px;
            margin: 4rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .details-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .details-header h1 {
            font-size: 2rem;
            color: var(--white);
            margin-bottom: 0.5rem;
        }

        .details-header p {
            color: var(--gray-400);
            font-size: 1rem;
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

        .form-group input,
        .form-group textarea,
        .form-group select,
        .select2-container--default .select2-selection--multiple {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--gray-700);
            border-radius: 0.5rem;
            color: var(--white);
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group select option {
            background: var(--gray-800);
            color: var(--white);
            padding: 0.75rem;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Select2 Dark Theme */
        .select2-container--default .select2-selection--multiple {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--gray-700);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.25rem 0.5rem;
            margin: 0.25rem;
            border-radius: 0.25rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 0.5rem;
        }

        .select2-dropdown {
            background-color: var(--gray-800);
            border: 1px solid var(--gray-700);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: var(--gray-700);
            color: var(--white);
            border: 1px solid var(--gray-600);
        }

        .select2-container--default .select2-results__option {
            color: var(--white);
            padding: 0.5rem;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary);
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: var(--primary-dark);
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 768px) {
            .details-container {
                margin: 2rem 1rem;
                padding: 1.5rem;
            }
        }

        .video-preview {
            margin: 2rem 0;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .video-preview video {
            max-height: 400px;
            width: 100%;
            object-fit: contain;
            background: #000;
        }

        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            font-size: 1rem;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .select2-container--default .select2-selection--multiple {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 0.5rem !important;
            color: var(--white) !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--primary-color) !important;
            border: none !important;
            color: var(--white) !important;
            padding: 0.25rem 0.5rem !important;
        }

        .btn-primary {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-color-dark);
        }

        /* Loading state */
        .btn-primary.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Success message */
        .success-message {
            padding: 1rem;
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid rgba(0, 255, 0, 0.2);
            border-radius: 0.5rem;
            color: #00ff00;
            margin-bottom: 1rem;
            display: none;
        }
    </style>
</head>
<body>
    <?php include('components/header.php'); ?>
    
    <div class="details-container">
        <div class="details-header">
            <h1>Video Details</h1>
            <p>Add information about your video</p>
        </div>

        <!-- Video Preview -->
        <div class="video-preview">
            <video controls width="100%" id="videoPreview">
                <source src="<?php echo htmlspecialchars($video); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <form id="videoDetailsForm">
            <input type="hidden" id="videoId" value="<?php echo htmlspecialchars($video_id); ?>">
            <input type="hidden" id="videoPath" value="<?php echo htmlspecialchars($video); ?>">
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($video_details['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['name']); ?>"
                                <?php echo ($video_details['category'] ?? '') === $category['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tags">Tags</label>
                <select id="tags" name="tags[]" multiple required>
                    <?php if (!empty($video_details['tags'])): ?>
                        <?php foreach (json_decode($video_details['tags'], true) ?? [] as $tag): ?>
                            <option value="<?php echo htmlspecialchars($tag); ?>" selected>
                                <?php echo htmlspecialchars($tag); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Save Details
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for tags with data from database
            const tagsByCategory = <?php echo json_encode($tags_by_category); ?>;
            
            $('#tags').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                data: Object.values(tagsByCategory).flat().map(tag => ({
                    id: tag,
                    text: tag
                })),
                placeholder: 'Select or type tags...',
                theme: 'default'
            });

            // Handle form submission
            $('#videoDetailsForm').on('submit', function(e) {
                e.preventDefault();
                
                // Disable submit button and show loading state
                const $submitBtn = $(this).find('button[type="submit"]');
                $submitBtn.prop('disabled', true).addClass('loading').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                
                const formData = {
                    video_id: $('#videoId').val(),
                    videoPath: $('#videoPath').val(),
                    description: $('#description').val(),
                    category: $('#category').val(),
                    tags: $('#tags').val()
                };

                // Validate form data
                if (!formData.description || !formData.category || !formData.tags.length) {
                    alert('Please fill in all required fields');
                    $submitBtn.prop('disabled', false).removeClass('loading').html('<i class="fas fa-save"></i> Save Details');
                    return;
                }

                $.ajax({
                    url: 'save_video_details.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                // Show success message
                                const $successMsg = $('<div class="success-message"><i class="fas fa-check"></i> Video details saved successfully!</div>');
                                $('#videoDetailsForm').prepend($successMsg);
                                $successMsg.fadeIn();
                                
                                // Redirect after a short delay
                                setTimeout(() => {
                                    window.location.href = 'index.php';
                                }, 1500);
                            } else {
                                alert('Error saving video details: ' + (result.error || 'Unknown error'));
                                $submitBtn.prop('disabled', false).removeClass('loading').html('<i class="fas fa-save"></i> Save Details');
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('Error processing server response');
                            $submitBtn.prop('disabled', false).removeClass('loading').html('<i class="fas fa-save"></i> Save Details');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        alert('Error saving video details: ' + error);
                        $submitBtn.prop('disabled', false).removeClass('loading').html('<i class="fas fa-save"></i> Save Details');
                    }
                });
            });
        });
    </script>
</body>
</html>
