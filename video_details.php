<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Details</title>
    <link rel="stylesheet" href="app/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .video-details-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.html");
        exit();
    }
    ?>
    <div class="video-details-container">
        <h2>Video Details</h2>
        <form id="videoDetailsForm">
            <input type="hidden" id="videoPath" name="videoPath">
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tags">Tags:</label>
                <select id="tags" name="tags[]" multiple="multiple" required>
                </select>
            </div>

            <button type="submit" class="submit-btn">Save Details</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Get video path and ID from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const videoPath = urlParams.get('video');
        const videoId = urlParams.get('video_id');
        document.getElementById('videoPath').value = videoPath;

        // Load categories and tags
        fetch('data/categories.json')
            .then(response => response.json())
            .then(data => {
                const categorySelect = document.getElementById('category');
                data.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category;
                    categorySelect.appendChild(option);
                });
            });

        // Load tags from tags.json
        fetch('data/tags.json')
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

                // Initialize Select2 with the loaded tags
                $('#tags').select2({
                    tags: true, // Allow custom tags
                    tokenSeparators: [',', ' '], // Allow multiple tags
                    placeholder: "Select or enter tags",
                    allowClear: true,
                    multiple: true
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
                    multiple: true
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
                video_id: videoId
            };

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
                    // Redirect to dashboard after successful save
                    window.location.href = 'dashboard.php';
                } else {
                    alert('Error saving video details: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving video details');
            });
        });
    </script>
</body>
</html>
