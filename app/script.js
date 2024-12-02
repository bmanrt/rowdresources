// Initialize dropdowns
function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            // Remove any existing event listeners
            const newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);
            
            newToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Close all other dropdowns
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        const otherMenu = otherDropdown.querySelector('.dropdown-menu');
                        const otherIcon = otherDropdown.querySelector('.fa-chevron-down');
                        if (otherMenu) {
                            otherMenu.classList.remove('show');
                            otherDropdown.classList.remove('active');
                        }
                        if (otherIcon) {
                            otherIcon.style.transform = 'rotate(0deg)';
                        }
                    }
                });
                
                // Toggle current dropdown
                const isOpen = menu.classList.contains('show');
                menu.classList.toggle('show');
                dropdown.classList.toggle('active');
                
                // Rotate chevron icon
                const icon = newToggle.querySelector('.fa-chevron-down');
                if (icon) {
                    icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                const menu = dropdown.querySelector('.dropdown-menu');
                const icon = dropdown.querySelector('.fa-chevron-down');
                if (menu) {
                    menu.classList.remove('show');
                    dropdown.classList.remove('active');
                }
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        }
    });
}

// Load categories for dropdown
async function loadCategories() {
    try {
        const response = await fetch('fetch_videos.php?action=categories');
        const data = await response.json();
        const categories = data.categories || [];
        
        const dropdown = document.getElementById('categoriesDropdown');
        if (dropdown) {
            let html = '<a href="category.php" class="dropdown-item">All Categories</a>';
            categories.forEach(category => {
                // Check if we're on the category page and this category is active
                const urlParams = new URLSearchParams(window.location.search);
                const currentCategory = urlParams.get('category');
                const isActive = currentCategory === category;
                
                html += `
                    <a href="category.php?category=${encodeURIComponent(category)}" 
                       class="dropdown-item${isActive ? ' active' : ''}">${category}</a>
                `;
            });
            dropdown.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        const dropdown = document.getElementById('categoriesDropdown');
        if (dropdown) {
            dropdown.innerHTML = '<p class="error-message">Error loading categories</p>';
        }
    }
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Create video card HTML
function createVideoCard(video) {
    // Use the video path directly as it's already formatted in fetch_videos.php
    const videoPath = video.path;
    
    return `
        <div class="video-card" onclick="openVideoModal('${encodeURIComponent(videoPath)}', '${encodeURIComponent(video.description || 'No description')}', '${encodeURIComponent(video.category || 'Uncategorized')}', '${encodeURIComponent(video.created_at)}')">
            <div class="video-thumbnail">
                <video src="${videoPath}" preload="metadata"></video>
            </div>
            <div class="video-info">
                <h3 class="video-title">${video.description || 'No description'}</h3>
                <div class="video-meta">
                    <span><i class="fas fa-folder"></i>${video.category || 'Uncategorized'}</span>
                    <span><i class="fas fa-calendar"></i>${video.created_at}</span>
                </div>
            </div>
        </div>
    `;
}

// Load latest videos
async function loadLatestVideos() {
    try {
        const response = await fetch('fetch_videos.php');
        const data = await response.json();
        
        const container = document.getElementById('latestVideosContainer');
        if (!container) return;
        
        if (!data.videos || data.videos.length === 0) {
            container.innerHTML = '<p class="no-videos">No videos available</p>';
            return;
        }
        
        container.innerHTML = data.videos.map(video => createVideoCard(video)).join('');
        
    } catch (error) {
        console.error('Error loading latest videos:', error);
        const container = document.getElementById('latestVideosContainer');
        if (container) {
            container.innerHTML = `
                <p class="error-message">
                    Error loading videos. Please try again.<br>
                    <small>${error.message}</small>
                </p>`;
        }
    }
}

// Load category sections
async function loadCategorySections() {
    try {
        const response = await fetch('fetch_videos.php?action=categories_with_videos');
        const data = await response.json();
        
        const categorySections = document.getElementById('categorySections');
        if (!categorySections) return;
        
        const template = document.getElementById('categorySectionTemplate');
        if (!template) return;
        
        // Clear existing sections first
        categorySections.innerHTML = '';
        
        if (!data.categories || data.categories.length === 0) {
            categorySections.innerHTML = '<p class="no-videos">No categories available</p>';
            return;
        }
        
        data.categories.forEach(category => {
            // Only create section if there are videos
            if (category.videos && category.videos.length > 0) {
                const categoryId = `category_${category.name.toLowerCase().replace(/[^a-z0-9]+/g, '_')}`;
                const section = template.innerHTML
                    .replace(/{category_name}/g, category.name)
                    .replace(/{category_url}/g, encodeURIComponent(category.name))
                    .replace(/{video_count}/g, category.video_count)
                    .replace(/{category_id}/g, categoryId);
                
                categorySections.insertAdjacentHTML('beforeend', section);
                
                const container = document.getElementById(categoryId);
                if (container) {
                    container.innerHTML = category.videos.map(video => createVideoCard(video)).join('');
                }
            }
        });
        
        // Initialize scroll controls for all sections
        initializeScrollControls();
        
    } catch (error) {
        console.error('Error loading category sections:', error);
        const categorySections = document.getElementById('categorySections');
        if (categorySections) {
            categorySections.innerHTML = `
                <p class="error-message">
                    Error loading categories. Please try again.<br>
                    <small>${error.message}</small>
                </p>`;
        }
    }
}

// Initialize scroll controls
function initializeScrollControls() {
    document.querySelectorAll('.control-btn').forEach(button => {
        button.addEventListener('click', () => {
            const sectionId = button.dataset.section;
            const direction = button.dataset.direction;
            const container = document.getElementById(
                sectionId === 'latest' ? 'latestVideosContainer' : sectionId
            );
            
            if (!container) return;
            
            const scrollAmount = container.clientWidth * 0.8;
            const scrollPosition = direction === 'next' 
                ? container.scrollLeft + scrollAmount 
                : container.scrollLeft - scrollAmount;
            
            container.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
        });
    });
}
// Mobile Menu and Dropdown Functionality
document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navLinks = document.getElementById('navLinks');
    const dropdowns = document.querySelectorAll('.dropdown');

    // Mobile menu toggle
    if (mobileMenuToggle && navLinks) {
        mobileMenuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            navLinks.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
    }

    // Handle dropdowns
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');

        if (toggle && menu) {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Close other dropdowns
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        const otherMenu = otherDropdown.querySelector('.dropdown-menu');
                        if (otherMenu) {
                            otherMenu.classList.remove('show');
                        }
                    }
                });

                // Toggle current dropdown
                menu.classList.toggle('show');
            });
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                const menu = dropdown.querySelector('.dropdown-menu');
                if (menu) {
                    menu.classList.remove('show');
                }
            });
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (navLinks && navLinks.classList.contains('active') && 
            !navLinks.contains(e.target) && 
            !mobileMenuToggle.contains(e.target)) {
            navLinks.classList.remove('active');
            document.body.classList.remove('menu-open');
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            // Close mobile menu
            if (navLinks) {
                navLinks.classList.remove('active');
                document.body.classList.remove('menu-open');
            }

            // Close all dropdowns
            dropdowns.forEach(dropdown => {
                const menu = dropdown.querySelector('.dropdown-menu');
                if (menu) {
                    menu.classList.remove('show');
                }
            });
        }
    });
});
// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initializeDropdowns();
    loadCategories();
    loadLatestVideos();
    loadCategorySections();
});
