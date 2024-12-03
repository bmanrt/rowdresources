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
    <?php include('components/head_common.php'); ?>
    <title>Search - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <style>
        .filter-checkbox {
            display: inline-flex;
            align-items: center;
            margin-right: 20px;
            cursor: pointer;
            font-size: 14px;
            user-select: none;
        }

        .filter-checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .filter-checkbox span {
            position: relative;
            padding-left: 28px;
            color: #333;
        }

        .filter-checkbox span:before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            border: 2px solid #4a90e2;
            border-radius: 4px;
            background-color: white;
            transition: all 0.2s ease;
        }

        .filter-checkbox input[type="checkbox"]:checked + span:before {
            background-color: #4a90e2;
            border-color: #4a90e2;
        }

        .filter-checkbox input[type="checkbox"]:checked + span:after {
            content: '';
            position: absolute;
            left: 6px;
            top: 50%;
            transform: translateY(-50%) rotate(45deg);
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
        }

        .filter-checkbox:hover span:before {
            border-color: #357abd;
        }

        .search-filters {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <?php include('components/header.php'); ?>
    <main>
        <div class="search-container">
            <div class="search-header">
                <h1>Search Videos</h1>
                <form id="searchForm" class="search-form" onsubmit="event.preventDefault(); searchVideos();">
                    <div class="search-input-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   id="searchInput" 
                                   placeholder="Search by description, tags, or category..." 
                                   class="search-input"
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        <button type="submit" id="searchButton" class="search-button">
                            <i class="fas fa-search"></i>
                            <span>Search</span>
                        </button>
                    </div>
                    <div class="search-filters">
                        <label class="filter-checkbox">
                            <input type="checkbox" id="searchDescription" checked>
                            <span>Description</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" id="searchTags" checked>
                            <span>Tags</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" id="searchCategory" checked>
                            <span>Category</span>
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

    <script src="script.js"></script>
    <script src="shared.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
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
