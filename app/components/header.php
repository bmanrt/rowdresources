<?php
require_once(__DIR__ . '/../auth_check.php');

// Get current user data if not already available
if (!isset($currentUser)) {
    $currentUser = getCurrentUser();
}
?>
<header class="header">
    <nav class="nav">
        <a href="../app/index.php" class="nav-brand">
            <img src="../app/assets/images/logo.webp" alt="Logo">
        </a>
        
        <button class="mobile-menu-toggle" aria-label="Toggle menu" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="nav-links" id="navLinks">
            <a href="../app/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            
            <div class="dropdown">
                <div class="nav-link dropdown-toggle">
                    <i class="fas fa-folder"></i>
                    <span>Categories</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu" id="categoriesDropdown">
                    <a href="../app/category.php" class="dropdown-item">
                        <i class="fas fa-layer-group"></i>
                        All Categories
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="../app/category.php?category=<?php echo urlencode($cat); ?>" class="dropdown-item">
                        <i class="fas fa-folder"></i>
                        <?php echo htmlspecialchars($cat); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="dropdown">
                <div class="nav-link dropdown-toggle">
                    <i class="fas fa-globe"></i>
                    <span>English</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">English</a>
                    <a href="#" class="dropdown-item">Spanish</a>
                    <a href="#" class="dropdown-item">French</a>
                </div>
            </div>

            <a href="../app/search.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'search.php' ? 'active' : ''; ?>">
                <i class="fas fa-search"></i>
                <span>Search</span>
            </a>

            <!-- User Menu -->
            <div class="dropdown user-menu">
                <div class="nav-link dropdown-toggle">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($currentUser['username']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="../app/profile.php" class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-circle"></i>
                        Profile
                    </a>
                    <?php if (hasRole('admin')): ?>
                    <a href="../app/admin/dashboard.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        Admin Panel
                    </a>
                    <?php endif; ?>
                    <a href="../app/logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>
<?php include(__DIR__ . '/header-scripts.php'); ?>
