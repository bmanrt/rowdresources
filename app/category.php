<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');  // Added database connection

// Ensure user is authenticated
if (!isAuthenticated()) {
    redirectToLogin();
    exit();
}

// Get current user data
$currentUser = getCurrentUser();

// Get category from URL
$category = $_GET['category'] ?? '';

// Get all categories for the dropdown
$categoriesQuery = "SELECT DISTINCT category FROM user_media WHERE media_type = 'video' AND category IS NOT NULL ORDER BY category";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category ? htmlspecialchars($category) : 'All Categories'; ?> - Videos</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
</head>
<body>
    <?php include('components/header.php'); ?>
    <main>
        <div class="category-container">
            <div class="category-header">
                <a href="index.php" class="back-to-home">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Home</span>
                </a>
                <h1><?php echo $category ? htmlspecialchars($category) : 'All Categories'; ?></h1>
                <p class="category-description">
                    <?php if ($category): ?>
                        Explore our collection of videos in the <?php echo htmlspecialchars($category); ?> category
                    <?php else: ?>
                        Browse through all our video categories
                    <?php endif; ?>
                </p>
            </div>

            <div class="container">
                <div class="videos-grid" id="videosContainer">
                    <!-- Videos will be populated here -->
                </div>
            </div>
        </div>
    </main>

    <script src="script.js"></script>
    <script src="shared.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script>
        async function loadCategoryVideos() {
            try {
                const category = getUrlParameter('category');
                const response = await fetch(`fetch_videos.php${category ? `?category=${encodeURIComponent(category)}` : ''}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                
                const videosContainer = document.getElementById('videosContainer');
                if (!videosContainer) return;
                
                videosContainer.innerHTML = '';
                
                if (!data.videos || data.videos.length === 0) {
                    videosContainer.innerHTML = `
                        <div class="no-videos">
                            <i class="fas fa-video-slash"></i>
                            <p>No videos available in ${category ? `the "${category}" category` : 'any category'}</p>
                        </div>`;
                    return;
                }
                
                videosContainer.innerHTML = data.videos.map(video => createVideoCard(video)).join('');
                
            } catch (error) {
                console.error('Error loading videos:', error);
                const videosContainer = document.getElementById('videosContainer');
                if (videosContainer) {
                    videosContainer.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Error loading videos. Please try again later.</p>
                            <small>${error.message}</small>
                        </div>`;
                }
            }
        }

        // Helper function to get URL parameters
        function getUrlParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // Initialize everything when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            initializeDropdowns();
            loadCategories();
            loadCategoryVideos();
        });
    </script>
</body>
</html>
