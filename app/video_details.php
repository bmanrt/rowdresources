<?php
session_start();
require_once('auth_check.php');
require_once('db.php'); // Assuming your database connection is in db.php

// Ensure user is authenticated
if (!isAuthenticated()) {
    redirectToLogin();
    exit();
}

$video_id = $_GET['video_id'] ?? '';

if (empty($video_id)) {
    header("Location: index.php");
    exit();
}

// Debug logging
error_log("Received video ID: " . $video_id);

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
    </style>
</head>
<body>
    <?php include('components/header.php'); ?>
    
    <div class="details-container">
        <div class="details-header">
            <h1>Video Details</h1>
        </div>
        
        <?php
        // Fetch video details from database
        $stmt = $conn->prepare("SELECT * FROM user_media WHERE video_id = ? AND user_id = ?");
        $stmt->bind_param("si", $video_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $video_data = $result->fetch_assoc();
        
        if (!$video_data) {
            echo "<p>Video not found</p>";
            exit;
        }
        
        // Decode tags if they exist
        $tags = [];
        if (!empty($video_data['tags'])) {
            $tags = json_decode($video_data['tags'], true) ?? [];
        }
        ?>
        
        <div class="video-player">
            <video controls width="100%">
                <source src="<?php echo htmlspecialchars($video_data['file_path']); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <form id="videoDetailsForm" class="details-form">
            <input type="hidden" id="video_id" value="<?php echo htmlspecialchars($video_id); ?>">
            <input type="hidden" id="videoPath" value="<?php echo htmlspecialchars($video_data['file_path']); ?>">
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($video_data['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">Select Category</option>
                    <?php
                    $categories = ['Training', 'Meeting', 'Presentation', 'Other'];
                    foreach ($categories as $cat) {
                        $selected = ($video_data['category'] ?? '') === $cat ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($cat) . "\" $selected>" . htmlspecialchars($cat) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="tags">Tags</label>
                <select id="tags" name="tags[]" multiple class="tags-select">
                    <?php
                    if (!empty($tags)) {
                        foreach ($tags as $tag) {
                            echo "<option value=\"" . htmlspecialchars($tag) . "\" selected>" . htmlspecialchars($tag) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <button type="submit" class="submit-btn">Save Details</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for tags
            $('#tags').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                placeholder: 'Add tags...',
                allowClear: true
            });

            // Handle form submission
            $('#videoDetailsForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    video_id: $('#video_id').val(),
                    videoPath: $('#videoPath').val(),
                    description: $('#description').val(),
                    category: $('#category').val(),
                    tags: $('#tags').val() || []
                };

                // Disable form while submitting
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).text('Saving...');

                // Send data to server
                $.ajax({
                    url: 'save_video_details.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    success: function(response) {
                        const data = JSON.parse(response);
                        if (data.success) {
                            alert('Video details saved successfully!');
                            // Optionally redirect to video list
                            // window.location.href = 'index.php';
                        } else {
                            alert('Error: ' + (data.error || 'Unknown error occurred'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Error saving video details: ' + error);
                    },
                    complete: function() {
                        // Re-enable form
                        submitBtn.prop('disabled', false).text('Save Details');
                    }
                });
            });
        });
    </script>
</body>
</html>
