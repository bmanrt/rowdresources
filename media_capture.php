<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}

include('db_config.php'); // Include your database configuration

$user_id = $_SESSION['user_id'];
$target_dir = "uploads/";

// Generate a unique ID for the video
$video_id = uniqid('vid_');

// Get the file extension
$file_extension = strtolower(pathinfo($_FILES["media"]["name"], PATHINFO_EXTENSION));

// Create a temporary filename with just the ID (tags will be added later)
$temp_filename = $video_id . "." . $file_extension;
$target_file = $target_dir . $temp_filename;
$uploadOk = 1;

// Check if file is a video
$file_type = mime_content_type($_FILES["media"]["tmp_name"]);
if(!strstr($file_type, "video/")) {
    echo "File is not a video.";
    $uploadOk = 0;
}

// Check file size (limit to 50MB)
if ($_FILES["media"]["size"] > 50000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain video formats
if($file_extension != "mp4" && $file_extension != "avi" && $file_extension != "mov") {
    echo "Sorry, only MP4, AVI & MOV files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
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
            header("Location: video_details.php?video=" . urlencode($target_file) . "&video_id=" . urlencode($video_id));
            exit();
        }
        $stmt->close();

    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

$conn->close();
?>
