// Shared dropdown initialization
function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        otherMenu.classList.remove('show');
                    }
                });
                
                menu.classList.toggle('show');
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
}

// Shared category loading
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
            dropdown.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Error loading categories</p>
                    <small>${error.message}</small>
                </div>`;
        }
    }
}

// Format file size helper
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Create video card HTML helper
function createVideoCard(video) {
    const videoElement = document.createElement('video');
    videoElement.controls = true;
    videoElement.preload = 'metadata';
    videoElement.style.width = '100%';
    videoElement.style.maxHeight = '200px';
    
    const videoPath = video.path.startsWith('http') ? 
        video.path : 
        (video.path.startsWith('/') ? video.path : '/' + video.path);
    
    const source = document.createElement('source');
    source.src = videoPath;
    source.type = 'video/mp4';
    videoElement.appendChild(source);
    
    const tagsHtml = video.tags && video.tags.length > 0 
        ? `<span><i class="fas fa-tags"></i> ${video.tags.join(', ')}</span>` 
        : '';
    
    const fileSize = video.size ? formatFileSize(video.size) : '';
    const fileSizeHtml = fileSize ? `<span><i class="fas fa-file"></i> ${fileSize}</span>` : '';
    
    return `
        <div class="video-card">
            <div class="video-thumbnail">
                ${videoElement.outerHTML}
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
            <div class="video-info">
                <h3 class="video-title">${video.description}</h3>
                <div class="video-meta">
                    <span><i class="fas fa-calendar"></i> ${video.created_at}</span>
                    ${fileSizeHtml}
                    ${tagsHtml}
                </div>
            </div>
        </div>
    `;
}
