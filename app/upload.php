<?php
// Prevent any output before our JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["media"])) {
    header('Content-Type: application/json');
    
    try {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate a unique ID for the video
        $video_id = uniqid('vid_');
        
        // Get the file extension
        $file_extension = strtolower(pathinfo($_FILES["media"]["name"], PATHINFO_EXTENSION));
        
        // Create a temporary filename with just the ID
        $temp_filename = $video_id . "." . $file_extension;
        $target_file = $target_dir . $temp_filename;
        
        // Check if file is a video
        $file_type = mime_content_type($_FILES["media"]["tmp_name"]);
        if(!strstr($file_type, "video/")) {
            throw new Exception("File is not a video.");
        }
        
        // Check file size (limit to 50MB)
        if ($_FILES["media"]["size"] > 50000000) {
            throw new Exception("Sorry, your file is too large.");
        }
        
        // Allow certain video formats
        if($file_extension != "mp4" && $file_extension != "avi" && $file_extension != "mov") {
            throw new Exception("Sorry, only MP4, AVI & MOV files are allowed.");
        }
        
        if (!move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
            throw new Exception("Sorry, there was an error uploading your file.");
        }
        
        // Add video_id column if it doesn't exist
        $alter_query = "ALTER TABLE user_media ADD COLUMN IF NOT EXISTS video_id VARCHAR(255)";
        if (!$conn->query($alter_query)) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        // Insert the initial record with the temporary filename
        $stmt = $conn->prepare("INSERT INTO user_media (user_id, file_path, media_type, video_id) VALUES (?, ?, 'video', ?)");
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        
        $relative_path = "uploads/" . $temp_filename;
        $stmt->bind_param("iss", $currentUser['id'], $relative_path, $video_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Database execute error: " . $stmt->error);
        }
        
        $stmt->close();
        
        echo json_encode([
            "success" => true,
            "redirect" => "video_details.php?video=" . urlencode($relative_path) . "&video_id=" . urlencode($video_id)
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => $e->getMessage()
        ]);
    }
    exit;
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

            <p>Share your video content with the community</p>

        </div>

        <form id="uploadForm" class="upload-form" enctype="multipart/form-data">

            <div class="form-group">

                <label for="media">Choose Video File</label>

                <input type="file" id="media" name="media" accept="video/*" required>

            </div>

            <div class="form-group">

                <button type="submit" class="upload-btn" id="uploadBtn">

                    <i class="fas fa-cloud-upload-alt"></i>

                    Upload Video

                </button>

            </div>

        </form>

        <div id="uploadProgress" class="progress-container" style="display: none;">

            <div class="progress-bar" id="progressBar">

                <div class="progress" id="progress" style="width: 0%"></div>

            </div>

            <div class="progress-text">0%</div>

        </div>

    </div>

    <script src="script.js"></script>

    <script src="shared.js"></script>

    <script>

        document.getElementById('uploadForm').addEventListener('submit', function(e) {

            e.preventDefault();

            const formData = new FormData(this);

            const progressBar = document.querySelector('.progress');

            const progressText = document.querySelector('.progress-text');

            const uploadProgress = document.getElementById('uploadProgress');

            uploadProgress.style.display = 'block';

            fetch('upload.php', {

                method: 'POST',

                body: formData

            })

            .then(response => response.json())

            .then(data => {

                if (data.error) {

                    alert(data.error);

                } else if (data.redirect) {

                    window.location.href = data.redirect;

                }

            })

            .catch(error => {

                console.error('Error:', error);

                alert('An error occurred during upload');

            });

        });

    </script>

    <!-- Include mobile navigation and dropdown scripts

    <script src="js/mobile-nav.js"></script>

    <script src="js/dropdowns.js"></script>

</body>

</html>
