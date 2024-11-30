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

// Get search query from URL
$search_query = $_GET['q'] ?? '';

// Get categories for filter dropdown
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
    <title>Search - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include('components/header.php'); ?>
    <main>
        <div class="search-container">
            <div class="search-header">
                <h1>Search Videos</h1>
                <form id="searchForm" class="search-form" onsubmit="event.preventDefault(); searchVideos();">
                    <div class="search-input-group">
                        <input type="text" id="searchInput" placeholder="Search by description, tags, or category..." class="search-input">
                        <button type="submit" id="searchButton" class="search-button">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                    <div class="search-filters">
                        <label class="filter-checkbox">
                            <input type="checkbox" id="searchDescription" checked>
                            Description
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" id="searchTags" checked>
                            Tags
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" id="searchCategory" checked>
                            Category
                        </label>
                    </div>
                </form>
            </div>

            <div class="videos-grid" id="searchResults">
                <!-- Search results will be populated by JavaScript -->
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <p>Enter a search term to find videos</p>
                </div>
            </div>
        </div>
    </main>

    <script src="shared.js"></script>
    <script>
        async function searchVideos() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            const searchDescription = document.getElementById('searchDescription').checked;
            const searchTags = document.getElementById('searchTags').checked;
            const searchCategory = document.getElementById('searchCategory').checked;
            
            if (!searchTerm) {
                document.getElementById('searchResults').innerHTML = `
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <p>Enter a search term to find videos</p>
                    </div>`;
                return;
            }
            
            try {
                const params = new URLSearchParams({
                    term: searchTerm,
                    description: searchDescription,
                    tags: searchTags,
                    category: searchCategory
                });
                
                const response = await fetch(`search_videos.php?${params.toString()}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                const resultsContainer = document.getElementById('searchResults');
                
                if (!data.videos || data.videos.length === 0) {
                    resultsContainer.innerHTML = `
                        <div class="no-results">
                            <i class="fas fa-search-minus"></i>
                            <p>No videos found matching "${searchTerm}"</p>
                        </div>`;
                    return;
                }
                
                resultsContainer.innerHTML = data.videos.map(video => createVideoCard(video)).join('');
                
            } catch (error) {
                console.error('Error searching videos:', error);
                document.getElementById('searchResults').innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Error searching videos. Please try again later.</p>
                        <small>${error.message}</small>
                    </div>`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initializeDropdowns();
            loadCategories();
            
            // Set up search button click handler
            document.getElementById('searchButton').addEventListener('click', (e) => {
                e.preventDefault();
                searchVideos();
            });
            
            // Set up input handler for empty search
            document.getElementById('searchInput').addEventListener('input', () => {
                if (document.getElementById('searchInput').value.trim() === '') {
                    document.getElementById('searchResults').innerHTML = `
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <p>Enter a search term to find videos</p>
                        </div>`;
                }
            });
        });
    </script>
</body>
</html>
