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
    <title>Upload Video - Media Resource Portal</title>
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

        .upload-zone {
            position: relative;
            width: 100%;
            min-height: 200px;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-zone:hover, .upload-zone.dragover {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
        }

        .upload-zone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--primary);
        }

        .upload-text {
            text-align: center;
            color: var(--white);
        }

        .file-info {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            display: none;
        }

        .file-info.show {
            display: block;
        }

        .progress-container {
            margin-top: 1.5rem;
            display: none;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress {
            width: 0%;
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .progress-text {
            margin-top: 0.5rem;
            text-align: center;
            color: var(--white);
            font-size: 0.9rem;
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
            margin-top: 1.5rem;
        }

        .upload-btn:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .upload-btn:disabled {
            background: var(--gray-600);
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            background: rgba(255, 59, 48, 0.1);
            color: #ff3b30;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            display: none;
            align-items: center;
            gap: 0.5rem;
        }

        .error-message.show {
            display: flex;
        }

        .supported-formats {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--gray-400);
        }

        .supported-formats h3 {
            color: var(--white);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .upload-container {
                padding: calc(var(--header-height) + 1rem) 1rem 2rem;
            }

            .upload-header h1 {
                font-size: 2rem;
            }

            .upload-form {
                padding: 1.5rem;
            }

            .upload-zone {
                min-height: 180px;
                padding: 1.5rem;
            }

            .upload-icon {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .upload-header h1 {
                font-size: 1.75rem;
            }

            .upload-zone {
                min-height: 150px;
                padding: 1rem;
            }

            .upload-icon {
                font-size: 2rem;
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
            <div class="upload-zone" id="uploadZone">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <div class="upload-text">
                    <p>Drag and drop your video here or click to browse</p>
                    <small>Maximum file size: 500MB</small>
                </div>
                <input type="file" id="media" name="media" accept="video/mp4,video/webm,video/ogg" required 
                       aria-label="Choose a video file">
            </div>

            <div id="fileInfo" class="file-info">
                <p><strong>Selected file:</strong> <span id="fileName">No file selected</span></p>
                <p><strong>Size:</strong> <span id="fileSize">-</span></p>
                <p><strong>Type:</strong> <span id="fileType">-</span></p>
            </div>

            <div id="progressContainer" class="progress-container">
                <div class="progress-bar">
                    <div class="progress" id="progress"></div>
                </div>
                <div class="progress-text" id="progressText">0%</div>
            </div>

            <div id="errorDisplay" class="error-message" role="alert" aria-live="polite"></div>

            <button type="submit" class="upload-btn" id="uploadBtn" disabled>
                <i class="fas fa-cloud-upload-alt"></i>
                Upload Video
            </button>

            <div class="supported-formats">
                <h3>Supported Formats</h3>
                <p>MP4, WebM, OGG (Maximum file size: 50MB)</p>
            </div>
        </form>
    </div>

    <script src="script.js"></script>
    <script src="shared.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('uploadForm');
            const uploadZone = document.getElementById('uploadZone');
            const fileInput = document.getElementById('media');
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const fileType = document.getElementById('fileType');
            const progressContainer = document.getElementById('progressContainer');
            const progress = document.getElementById('progress');
            const progressText = document.getElementById('progressText');
            const uploadBtn = document.getElementById('uploadBtn');
            const errorDisplay = document.getElementById('errorDisplay');

            // Format file size
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Show error message
            function showError(message) {
                errorDisplay.innerHTML = `<i class="fas fa-exclamation-circle"></i>${message}`;
                errorDisplay.classList.add('show');
                progressContainer.style.display = 'none';
                uploadBtn.disabled = true;
            }

            // Clear error message
            function clearError() {
                errorDisplay.classList.remove('show');
                errorDisplay.innerHTML = '';
            }

            // Handle file selection
            function handleFileSelect(file) {
                clearError();
                
                if (!file) {
                    fileInfo.classList.remove('show');
                    uploadBtn.disabled = true;
                    return;
                }

                const maxSize = 50 * 1024 * 1024; // 50MB
                const allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];

                if (!allowedTypes.includes(file.type)) {
                    showError('Invalid file type. Please upload MP4, WebM, or OGG video.');
                    fileInput.value = '';
                    return;
                }

                if (file.size > maxSize) {
                    showError('File size exceeds 50MB limit');
                    fileInput.value = '';
                    return;
                }

                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileType.textContent = file.type;
                fileInfo.classList.add('show');
                uploadBtn.disabled = false;
            }

            // File input change handler
            fileInput.addEventListener('change', (e) => {
                handleFileSelect(e.target.files[0]);
            });

            // Drag and drop handlers
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });

            uploadZone.addEventListener('dragleave', () => {
                uploadZone.classList.remove('dragover');
            });

            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
                const file = e.dataTransfer.files[0];
                fileInput.files = e.dataTransfer.files;
                handleFileSelect(file);
            });

            // Form submission handler
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                clearError();

                const file = fileInput.files[0];
                if (!file) {
                    showError('Please select a file to upload');
                    return;
                }

                try {
                    uploadBtn.disabled = true;
                    progressContainer.style.display = 'block';
                    progress.style.width = '0%';
                    progressText.textContent = '0%';

                    const formData = new FormData(this);
                    const xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = Math.round((e.loaded / e.total) * 100);
                            progress.style.width = percentComplete + '%';
                            progressText.textContent = percentComplete + '%';
                        }
                    });

                    xhr.addEventListener('load', function() {
                        try {
                            const response = JSON.parse(this.responseText);
                            if (response.success) {
                                window.location.href = response.redirect;
                            } else {
                                showError(response.error + (response.details ? '\n' + response.details : ''));
                            }
                        } catch (error) {
                            showError('Upload failed: Invalid server response');
                        }
                    });

                    xhr.addEventListener('error', () => {
                        showError('Upload failed: Network error');
                    });

                    xhr.open('POST', 'handle_upload.php');
                    xhr.send(formData);

                } catch (error) {
                    showError('Upload failed: ' + error.message);
                    uploadBtn.disabled = false;
                }
            });
        });
    </script>
</body>
</html>
