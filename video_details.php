<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Details - Media Resource Portal</title>
    <link rel="stylesheet" href="app/styles.css">
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
            color: var(--gray-300);
            font-size: 0.9rem;
            font-weight: 500;
        }

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

        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .form-group select option[value=""] {
            color: var(--gray-400);
        }

        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 45px;
            background: rgba(255, 255, 255, 0.05);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: var(--primary);
            border: none;
            color: var(--white);
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: var(--white);
            margin-right: 0.5rem;
        }

        .select2-dropdown {
            background: var(--gray-800);
            border: 1px solid var(--gray-700);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--gray-700);
            color: var(--white);
        }

        .select2-container--default .select2-results__option {
            color: var(--white);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--primary);
        }

        .submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: var(--primary);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .submit-btn i {
            font-size: 1.1rem;
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
    <div class="details-container">
        <div class="details-header">
            <h1>Video Details</h1>
            <p>Add information about your video</p>
        </div>
        <form id="videoDetailsForm">
            <input type="hidden" id="videoPath" name="videoPath">
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Enter a detailed description of your video" required></textarea>
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

            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i>
                Save Details
            </button>
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
                let allTags = [];
                Object.entries(data.tags).forEach(([category, tags]) => {
                    if (tags.length === 0) {
                        allTags.push(category);
                    } else {
                        allTags = allTags.concat(tags);
                    }
                });
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
                    multiple: true,
                    theme: "default"
                });
            })
            .catch(error => {
                console.error('Error loading tags:', error);
                $('#tags').select2({
                    tags: true,
                    tokenSeparators: [',', ' '],
                    placeholder: "Enter tags",
                    allowClear: true,
                    multiple: true,
                    theme: "default"
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
                    window.location.href = 'index.html';
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
