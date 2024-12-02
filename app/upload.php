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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('components/head_common.php'); ?>
    <title>Upload - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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

        .form-group input[type="file"] {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: var(--white);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-group input[type="file"]:hover {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
        }

        .upload-btn {
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

        .upload-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .upload-btn:disabled {
            background: var(--gray-600);
            cursor: not-allowed;
            transform: none;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            margin-top: 1rem;
            overflow: hidden;
            display: none;
        }

        .progress-bar .progress {
            width: 0%;
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .error-message {
            background: rgba(255, 59, 48, 0.1);
            color: #ff3b30;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            <h1>Upload Video</h1>
            <p>Share your video with the community</p>
        </div>

        <form id="uploadForm" class="upload-form" action="handle_upload.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="media">Select Video</label>
                <input type="file" id="media" name="media" accept="video/*" required>
            </div>

            <button type="submit" class="upload-btn" id="uploadBtn">
                <i class="fas fa-cloud-upload-alt"></i>
                Upload Video
            </button>

            <div class="progress-bar" id="progressBar">
                <div class="progress" id="progress"></div>
            </div>
        </form>
    </div>

    <script src="script.js"></script>
    <script src="shared.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('uploadForm');
            const progressBar = document.getElementById('progressBar');
            const progress = document.getElementById('progress');
            const uploadBtn = document.getElementById('uploadBtn');

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validate file size (50MB limit)
                const fileInput = document.getElementById('media');
                const maxSize = 50 * 1024 * 1024; // 50MB in bytes
                
                if (fileInput.files[0].size > maxSize) {
                    alert('File size exceeds 50MB limit');
                    return;
                }

                const formData = new FormData(this);
                uploadBtn.disabled = true;
                progressBar.style.display = 'block';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.action, true);

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progress.style.width = percentComplete + '%';
                    }
                };

                xhr.onload = function() {
                    uploadBtn.disabled = false;
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                window.location.href = response.redirect;
                            } else {
                                alert(response.error || 'Upload failed');
                            }
                        } catch (e) {
                            alert('Upload failed');
                        }
                    } else {
                        alert('Upload failed');
                    }
                };

                xhr.onerror = function() {
                    uploadBtn.disabled = false;
                    alert('Upload failed');
                };

                xhr.send(formData);
            });
        });
    </script>
    <!-- Include mobile navigation and dropdown scripts -->
    <script src="js/mobile-nav.js"></script>
    <script src="js/dropdowns.js"></script>
</body>
</html>
