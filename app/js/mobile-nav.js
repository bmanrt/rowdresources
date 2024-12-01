/**
 * Mobile Navigation Handler
 * This script handles mobile menu toggle and dropdown functionality across all pages
 */

class MobileNav {
    constructor() {
        // Core elements
        this.mobileMenuToggle = document.getElementById('mobileMenuToggle');
        this.navLinks = document.getElementById('navLinks');
        this.dropdowns = document.querySelectorAll('.dropdown');
        this.activeDropdown = null;
        
        if (this.mobileMenuToggle && this.navLinks) {
            this.init();
        } else {
            console.warn('Mobile navigation elements not found');
        }
    }

    init() {
        // Mobile menu toggle
        this.mobileMenuToggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggleMobileMenu();
        });

        // Handle dropdowns
        this.dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            if (toggle) {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.toggleDropdown(dropdown);
                });
            }
        });

        // Prevent menu close when clicking inside dropdown
        this.dropdowns.forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            const clickedDropdown = e.target.closest('.dropdown');
            if (!clickedDropdown) {
                this.closeAllDropdowns();
            }
            
            if (!this.navLinks.contains(e.target) && 
                !this.mobileMenuToggle.contains(e.target)) {
                this.closeMobileMenu();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                this.closeMobileMenu();
                this.closeAllDropdowns();
            }
        });
    }

    toggleMobileMenu() {
        this.navLinks.classList.toggle('active');
        document.body.classList.toggle('menu-open');
        if (!this.navLinks.classList.contains('active')) {
            this.closeAllDropdowns();
        }
    }

    closeMobileMenu() {
        this.navLinks.classList.remove('active');
        document.body.classList.remove('menu-open');
        this.closeAllDropdowns();
    }

    toggleDropdown(dropdown) {
        const menu = dropdown.querySelector('.dropdown-menu');
        if (!menu) return;

        const isCurrentlyOpen = menu.classList.contains('show');

        // Close all other dropdowns
        this.dropdowns.forEach(other => {
            if (other !== dropdown) {
                const otherMenu = other.querySelector('.dropdown-menu');
                if (otherMenu) {
                    otherMenu.classList.remove('show');
                    other.classList.remove('active');
                }
            }
        });

        // Toggle current dropdown
        if (isCurrentlyOpen) {
            menu.classList.remove('show');
            dropdown.classList.remove('active');
            this.activeDropdown = null;
        } else {
            menu.classList.add('show');
            dropdown.classList.add('active');
            this.activeDropdown = dropdown;
        }

        // Update toggle icon
        const toggleIcon = dropdown.querySelector('.fa-chevron-down');
        if (toggleIcon) {
            toggleIcon.style.transform = isCurrentlyOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }

    closeAllDropdowns() {
        this.dropdowns.forEach(dropdown => {
            const menu = dropdown.querySelector('.dropdown-menu');
            const toggleIcon = dropdown.querySelector('.fa-chevron-down');
            if (menu) {
                menu.classList.remove('show');
                dropdown.classList.remove('active');
            }
            if (toggleIcon) {
                toggleIcon.style.transform = 'rotate(0deg)';
            }
        });
        this.activeDropdown = null;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing mobile navigation');
    window.mobileNav = new MobileNav();
});
