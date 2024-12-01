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

// Get categories for the dropdown
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
    <title>Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
</head>
<body>
    <?php include('components/header.php'); ?>

    <main>
        <div class="hero-section">
            <div class="overlay"></div>
            <div class="hero-content">
                <h1 class="animate-text">Media Resource Portal</h1>
                <p class="hero-description">Beyond reaching the whole world, over 7 billion people will be effectively discipled as they're engaged with Rhapsody of Realities in an accountable way.</p>
                
                <div class="cta-buttons">
                    <button class="btn primary">
                        <i class="fas fa-tv"></i>
                        RhapsodyTV
                        <span class="badge">2024</span>
                    </button>
                    <button class="btn secondary">
                        <i class="fas fa-play-circle"></i>
                        Watch Live
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Latest Videos Section -->
    <section class="latest-videos">
        <div class="container">
            <div class="section-header">
                <div class="header-left">
                    <h2>Latest Videos</h2>
                    <button onclick="window.location.href='latest.php'" class="view-all-btn">
                        View All <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
                <div class="controls">
                    <button class="control-btn" data-section="latest" data-direction="prev">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="control-btn" data-section="latest" data-direction="next">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="videos-grid horizontal-scroll" id="latestVideosContainer"></div>
        </div>
    </section>

    <!-- Category Sections -->
    <div id="categorySections">
        <!-- Category sections will be dynamically added here -->
    </div>

    <!-- Category Section Template -->
    <template id="categorySectionTemplate">
        <section class="latest-videos">
            <div class="container">
                <div class="section-header">
                    <div class="header-left">
                        <h2>{category_name}</h2>
                        <button onclick="window.location.href='category.php?category={category_url}'" class="view-all-btn">
                            View All <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                    <div class="controls">
                        <button class="control-btn" data-section="{category_id}" data-direction="prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="control-btn" data-section="{category_id}" data-direction="next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="videos-grid horizontal-scroll" id="{category_id}"></div>
            </div>
        </section>
    </template>

    <?php if (hasRole('admin')): ?>
    <!-- Admin Quick Actions -->
    <div class="admin-actions">
        <button class="btn admin-btn" onclick="location.href='admin/videos/add.php'">
            <i class="fas fa-plus"></i> Add Video
        </button>
        <button class="btn admin-btn" onclick="location.href='admin/categories/manage.php'">
            <i class="fas fa-folder-plus"></i> Manage Categories
        </button>
    </div>
    <?php endif; ?>

    <script src="script.js"></script>
    <script src="shared.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initializeDropdowns();
            loadLatestVideos();
            loadCategorySections();
            initializeScrollControls();
        });
    </script>
</body>
</html>
