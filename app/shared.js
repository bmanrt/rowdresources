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

// Initialize video player modal
function initializeVideoPlayer() {
    console.log('Initializing video player...');
    // Create modal HTML if it doesn't exist
    if (!document.getElementById('videoModal')) {
        console.log('Creating video modal...');
        const modalHTML = `
            <div id="videoModal" class="video-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="video-title"></h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <video id="player" playsinline controls>
                            <source type="video/mp4" />
                        </video>
                    </div>
                    <div class="modal-footer">
                        <div class="video-meta">
                            <span class="category"><i class="fas fa-folder"></i></span>
                            <span class="date"><i class="fas fa-calendar"></i></span>
                        </div>
                        <div class="modal-actions">
                            <a href="#" class="open-player-btn" title="Open in Player">
                                <i class="fas fa-external-link-alt"></i>
                                <span>Open in Player</span>
                            </a>
                            <a href="#" class="download-btn" download>
                                <i class="fas fa-download"></i>
                                <span>Download</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Add modal styles if not already in stylesheet
        if (!document.getElementById('modalStyles')) {
            console.log('Adding modal styles...');
            const modalStyles = `
                .video-modal {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.9);
                    z-index: 1000;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                .video-modal.show {
                    display: flex !important;
                    opacity: 1;
                }
                .modal-content {
                    position: relative;
                    width: 90%;
                    max-width: 1200px;
                    margin: auto;
                    background: var(--card-bg);
                    border-radius: 8px;
                    overflow: hidden;
                }
                .modal-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 1rem;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                }
                .modal-body {
                    position: relative;
                    background: #000;
                }
                .modal-footer {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 1rem;
                    border-top: 1px solid rgba(255, 255, 255, 0.1);
                }
                .close-modal {
                    background: none;
                    border: none;
                    color: var(--text-color);
                    font-size: 1.5rem;
                    cursor: pointer;
                    padding: 0.5rem;
                    line-height: 1;
                }
                .close-modal:hover {
                    color: var(--primary-color);
                }
                .download-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    padding: 0.5rem 1rem;
                    background: var(--primary-color);
                    color: white;
                    border-radius: 4px;
                    text-decoration: none;
                    font-weight: 500;
                    transition: background 0.3s;
                }
                .download-btn:hover {
                    background: var(--primary-dark);
                }
                .modal-actions {
                    display: flex;
                    gap: 1rem;
                    align-items: center;
                }
                .open-player-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    padding: 0.5rem 1rem;
                    background: var(--secondary-color);
                    color: white;
                    border-radius: 4px;
                    text-decoration: none;
                    font-weight: 500;
                    transition: background 0.3s;
                }
                .open-player-btn:hover {
                    background: var(--secondary-dark);
                }
                .plyr--video {
                    aspect-ratio: 16/9;
                }
            `;
            const styleSheet = document.createElement('style');
            styleSheet.id = 'modalStyles';
            styleSheet.textContent = modalStyles;
            document.head.appendChild(styleSheet);
        }
    }
}

// Open video modal
function openVideoModal(videoPath, description, category, date) {
    console.log('Opening video modal...');
    videoPath = decodeURIComponent(videoPath);
    description = decodeURIComponent(description);
    category = decodeURIComponent(category);
    date = decodeURIComponent(date);

    const modal = document.getElementById('videoModal');
    if (!modal) {
        console.error('Video modal not found! Initializing...');
        initializeVideoPlayer();
    }

    const player = document.getElementById('player');
    const source = player.querySelector('source');
    const downloadBtn = modal.querySelector('.download-btn');
    const openPlayerBtn = modal.querySelector('.open-player-btn');
    
    // Update modal content
    modal.querySelector('.video-title').textContent = description;
    modal.querySelector('.category').innerHTML = `<i class="fas fa-folder"></i>${category}`;
    modal.querySelector('.date').innerHTML = `<i class="fas fa-calendar"></i>${date}`;
    
    // Format paths for display and player
    const displayPath = videoPath;
    const dbPath = videoPath.includes('http://154.113.83.252/rowdresources/') 
        ? videoPath.split('rowdresources/')[1]
        : videoPath;
    
    console.log('Video paths:', {
        displayPath,
        dbPath
    });

    // Update video source and action links
    source.src = displayPath;
    downloadBtn.href = displayPath;
    openPlayerBtn.href = `player.php?video=${encodeURIComponent(dbPath)}`;
    
    // Initialize or update Plyr
    if (!window.videoPlayer) {
        console.log('Initializing Plyr...');
        window.videoPlayer = new Plyr('#player', {
            controls: [
                'play-large', 'play', 'progress', 'current-time', 'duration',
                'mute', 'volume', 'settings', 'pip', 'airplay', 'fullscreen'
            ],
            settings: ['quality', 'speed'],
            speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] }
        });
    } else {
        player.load();
    }
    
    // Show modal
    modal.classList.add('show');
    console.log('Modal should be visible now');
    
    // Close modal on background click or close button
    const closeHandler = (e) => {
        if (e.target === modal || e.target.classList.contains('close-modal')) {
            closeVideoModal();
        }
    };
    modal.removeEventListener('click', closeHandler);
    modal.addEventListener('click', closeHandler);
    
    // Close on escape key
    const escHandler = (e) => {
        if (e.key === 'Escape') {
            closeVideoModal();
        }
    };
    document.removeEventListener('keydown', escHandler);
    document.addEventListener('keydown', escHandler);
}

// Close video modal
function closeVideoModal() {
    console.log('Closing video modal...');
    const modal = document.getElementById('videoModal');
    if (window.videoPlayer) {
        window.videoPlayer.pause();
    }
    modal.classList.remove('show');
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing video player...');
    initializeVideoPlayer();
});

// Also initialize when the script loads (in case DOMContentLoaded already fired)
initializeVideoPlayer();
