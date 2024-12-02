<?php
session_start();
require_once('auth_check.php');

// Ensure user is authenticated
if (!isAuthenticated()) {
    redirectToLogin();
    exit();
}

// Get current user data
$currentUser = getCurrentUser();

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
    require_once('http://154.113.83.252/rowdresources/db_config.php');
    
    $user_id = $currentUser['id'];
    $target_dir = "http://154.113.83.252/rowdresources/uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Generate a unique ID for the video
    $video_id = uniqid('vid_');

    // Get the file extension
    $file_extension = strtolower(pathinfo($_FILES["media"]["name"], PATHINFO_EXTENSION));

    // Create filename with video ID
    $filename = $video_id . "." . $file_extension;
    $target_file = $target_dir . $filename;
    $uploadOk = 1;

    // Check if file is a video
    $allowed_types = ['video/mp4', 'video/webm', 'video/quicktime'];
    if (!in_array($_FILES["media"]["type"], $allowed_types)) {
        echo "File is not a supported video type.";
        $uploadOk = 0;
    }

    // Check file size (limit to 500MB)
    if ($_FILES["media"]["size"] > 500000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain video formats
    if($file_extension != "mp4" && $file_extension != "webm" && $file_extension != "mov") {
        echo "Sorry, only MP4, WebM & MOV files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
            // Add video_id column if it doesn't exist
            $alter_query = "ALTER TABLE user_media ADD COLUMN IF NOT EXISTS video_id VARCHAR(255)";
            if (!$conn->query($alter_query)) {
                echo "Error adding video_id column: " . $conn->error;
                exit;
            }

            // Insert the initial record with the temporary filename
            $stmt = $conn->prepare("INSERT INTO user_media (user_id, file_path, media_type, video_id) VALUES (?, ?, 'video', ?)");
            $stmt->bind_param("iss", $user_id, $target_file, $video_id);
            if ($stmt->execute()) {
                // Redirect to video details page with the video ID
                header("Location: http://154.113.83.252/rowdresources/app/video_details.php?video=" . urlencode($target_file) . "&video_id=" . urlencode($video_id));
                exit();
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .upload-container {
            max-width: 800px;
            margin: 5rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .upload-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .upload-header h1 {
            font-size: 2rem;
            color: var(--white);
            margin-bottom: 0.5rem;
        }

        .upload-header p {
            color: var(--gray-400);
            font-size: 1rem;
        }

        .upload-area {
            border: 2px dashed var(--gray-600);
            border-radius: 1rem;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.02);
        }

        .upload-area:hover, .upload-area.dragover {
            border-color: var(--primary);
            background: rgba(37, 99, 235, 0.1);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        .upload-text {
            margin-bottom: 1rem;
            color: var(--gray-300);
        }

        .upload-text strong {
            color: var(--primary);
            text-decoration: underline;
            cursor: pointer;
        }

        .file-info {
            color: var(--gray-400);
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        #videoPreview {
            max-width: 100%;
            margin-top: 1.5rem;
            border-radius: 0.5rem;
            display: none;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background: var(--gray-700);
            border-radius: 2px;
            margin-top: 1rem;
            overflow: hidden;
            display: none;
        }

        .progress {
            width: 0%;
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .upload-container {
                margin: 2rem 1rem;
                padding: 1.5rem;
            }

            .upload-area {
                padding: 2rem 1rem;
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
        <form id="uploadForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <input type="file" id="fileInput" name="media" accept="video/*" style="display: none">
            <div id="uploadArea" class="upload-area">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <div class="upload-text">
                    <p>Drag and drop your video here or <strong>browse</strong></p>
                    <p class="file-info">Supported formats: MP4, WebM, MOV (max. 500MB)</p>
                </div>
                <div class="progress-bar" id="progressBar">
                    <div class="progress" id="progress"></div>
                </div>
                <video id="videoPreview" controls></video>
            </div>
        </form>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const videoPreview = document.getElementById('videoPreview');
        const progressBar = document.getElementById('progressBar');
        const progress = document.getElementById('progress');
        const form = document.getElementById('uploadForm');

        // Handle drag and drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadArea.classList.add('dragover');
        }

        function unhighlight(e) {
            uploadArea.classList.remove('dragover');
        }

        // Handle file selection
        uploadArea.addEventListener('click', () => fileInput.click());
        
        uploadArea.addEventListener('drop', handleDrop);
        fileInput.addEventListener('change', handleFiles);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles({ target: { files } });
        }

        function handleFiles(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('video/')) {
                // Show video preview
                videoPreview.style.display = 'block';
                videoPreview.src = URL.createObjectURL(file);
                
                // Show progress bar
                progressBar.style.display = 'block';
                
                // Simulate upload progress
                let width = 0;
                const interval = setInterval(() => {
                    if (width >= 100) {
                        clearInterval(interval);
                        form.submit();
                    } else {
                        width++;
                        progress.style.width = width + '%';
                    }
                }, 50);
            } else {
                alert('Please select a valid video file.');
            }
        }
    </script>
</body>
</html>
