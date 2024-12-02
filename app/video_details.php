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
$videoPath = str_replace('\\', '/', $videoPath);
$dbPath = ltrim($videoPath, '/');  // Remove leading slash for DB comparison

// Get video details from database
$stmt = $conn->prepare("SELECT * FROM user_media WHERE file_path = ? AND media_type = 'video' LIMIT 1");
$stmt->bind_param("s", $dbPath);
$stmt->execute();
$result = $stmt->get_result();
$video = $result->fetch_assoc();

if (!$video) {
    header('Location: index.php');
    exit();
}

// Format video path for frontend display
$displayPath = '/rowdresources/' . ltrim($dbPath, '/');
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
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <style>
        .details-container {
            padding-top: calc(var(--header-height) + 2rem);
            max-width: 800px;
            margin: 0 auto;
            padding-left: 2rem;
            padding-right: 2rem;
        }

        .details-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .details-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .details-form {
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
            font-size: 0.95rem;
        }

        .form-group textarea, 
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            color: var(--white);
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group textarea:focus,
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.5;
        }

        .form-group select {
            background: #1a1a1a !important;
            color: #fff !important;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23ffffff' d='M6 8.825L1.175 4 2.238 2.938 6 6.7l3.763-3.763L10.825 4z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 30px !important;
        }

        .form-group select option {
            background: #1a1a1a;
            color: #fff;
            padding: 8px;
        }

        /* Select2 Custom Styles */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--multiple,
        .select2-container--default .select2-selection--single {
            background: #1a1a1a !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 4px !important;
            min-height: 42px !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff !important;
            padding: 8px 12px !important;
            line-height: 1.5 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 8px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #fff transparent transparent transparent !important;
        }

        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #fff transparent !important;
        }

        .select2-dropdown {
            background: #1a1a1a !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 4px !important;
            margin-top: 4px !important;
        }

        .select2-container--default .select2-results__option {
            padding: 8px 12px !important;
            color: #fff !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--primary) !important;
            color: #fff !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background: rgba(var(--primary-rgb), 0.2) !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background: #1a1a1a !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #fff !important;
            border-radius: 4px !important;
            padding: 6px 10px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 4px 8px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: var(--primary) !important;
            border: none !important;
            color: #fff !important;
            border-radius: 3px !important;
            padding: 4px 8px !important;
            margin: 4px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff !important;
            margin-right: 6px !important;
            border-right: none !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
        }

        .select2-container--default .select2-search--inline .select2-search__field {
            color: #fff !important;
            margin-top: 0 !important;
            padding: 4px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15) !important;
        }

        .save-btn {
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

        .save-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .video-preview {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            background: #000;
        }

        .video-preview video {
            width: 100%;
            display: block;
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

        .notification.error {
            background: var(--error, #dc3545);
        }

        .notification.show {
            transform: translateX(0);
        }

        @media (max-width: 768px) {
            .details-container {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .details-header h1 {
                font-size: 2rem;
            }

            .details-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include('components/header.php'); ?>

    <div id="notification" class="notification">
        <i class="fas fa-check-circle"></i>
        <span id="notification-message"></span>
    </div>

    <div class="details-container">
        <div class="details-header">
            <h1>Video Details</h1>
            <p>Add information about your video</p>
        </div>

        <form id="videoDetailsForm" class="details-form">
            <input type="hidden" id="videoPath" name="videoPath">
            <input type="hidden" id="videoId" name="videoId">

            <div class="video-preview">
                <video 
                    id="previewVideo" 
                    playsinline 
                    controls
                    data-plyr-config='{ "settings": ["captions", "quality", "speed", "loop"] }'
                >
                    <source src="<?php echo htmlspecialchars($displayPath); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required 
                          placeholder="Enter a description for your video"></textarea>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tags">Tags</label>
                <select id="tags" name="tags[]" multiple="multiple" required>
                </select>
            </div>

            <button type="submit" class="save-btn">
                <i class="fas fa-save"></i>
                Save Details
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script>
        // Initialize Plyr
        const player = new Plyr('#previewVideo', {
            controls: [
                'play-large', 'play', 'progress', 'current-time', 'duration',
                'mute', 'volume', 'settings', 'pip', 'airplay', 'fullscreen'
            ],
            settings: ['quality', 'speed'],
            speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] }
        });

        // Set form values
        document.getElementById('videoPath').value = '<?php echo htmlspecialchars($dbPath); ?>';
        document.getElementById('videoId').value = '<?php echo htmlspecialchars($video['id'] ?? ''); ?>';

        // Load categories from JSON
        fetch('../data/categories.json')
            .then(response => response.json())
            .then(data => {
                const categorySelect = document.getElementById('category');
                data.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category;
                    categorySelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading categories:', error));

        // Load tags from JSON
        fetch('../data/tags.json')
            .then(response => response.json())
            .then(data => {
                const tagsSelect = document.getElementById('tags');
                
                // Create an array to store all tags
                let allTags = [];
                
                // Process each category and its tags
                Object.entries(data.tags).forEach(([category, tags]) => {
                    // Add the category itself as a tag if it has no subtags
                    if (tags.length === 0) {
                        allTags.push(category);
                    } else {
                        // Add all the subtags
                        allTags = allTags.concat(tags);
                    }
                });
                
                // Add each tag as an option
                allTags.forEach(tag => {
                    const option = document.createElement('option');
                    option.value = tag;
                    option.textContent = tag;
                    tagsSelect.appendChild(option);
                });

                // Initialize Select2
                $('#tags').select2({
                    tags: true,
                    tokenSeparators: [',', ' '],
                    placeholder: "Select or enter tags",
                    allowClear: true,
                    theme: "classic"
                });
            })
            .catch(error => {
                console.error('Error loading tags:', error);
                // Initialize Select2 even if tags failed to load
                $('#tags').select2({
                    tags: true,
                    tokenSeparators: [',', ' '],
                    placeholder: "Enter tags",
                    allowClear: true,
                    theme: "classic"
                });
            });

        // Handle form submission
        document.getElementById('videoDetailsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                videoPath: document.getElementById('videoPath').value,
                description: document.getElementById('description').value,
                category: document.getElementById('category').value,
                tags: $('#tags').val(),
                video_id: document.getElementById('videoId').value
            };

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;

            fetch('save_video_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Video details saved successfully!', 'success');
                    // Redirect to video player after a short delay
                    setTimeout(() => {
                        window.location.href = `player.php?video=${encodeURIComponent(data.new_path)}&from=upload`;
                    }, 1500);
                } else {
                    throw new Error(data.error || 'Failed to save video details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(error.message || 'Error saving video details', 'error');
                // Reset button state
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationMessage = document.getElementById('notification-message');
            
            notification.className = 'notification ' + type;
            notificationMessage.textContent = message;
            
            // Show notification
            setTimeout(() => notification.classList.add('show'), 100);
            
            // Hide notification after 5 seconds
            setTimeout(() => {
                notification.classList.remove('show');
            }, 5000);
        }
    </script>
</body>
</html>
