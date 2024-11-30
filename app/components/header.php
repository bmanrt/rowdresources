<?php
// Get current user data if not already available
if (!isset($currentUser)) {
    $currentUser = getCurrentUser();
}
?>
<header class="header">
    <nav class="nav">
        <a href="index.php" class="nav-brand">
            <img src="assets/images/logo.webp" alt="Logo">
        </a>
        
        <div class="nav-links">
            <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                Home
            </a>
            
            <div class="dropdown">
                <div class="nav-link dropdown-toggle">
                    <i class="fas fa-folder"></i>
                    Categories
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu" id="categoriesDropdown">
                    <!-- Categories will be populated by JavaScript -->
                </div>
            </div>

            <div class="dropdown">
                <div class="nav-link dropdown-toggle">
                    <i class="fas fa-globe"></i>
                    English
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">English</a>
                    <a href="#" class="dropdown-item">Spanish</a>
                    <a href="#" class="dropdown-item">French</a>
                </div>
            </div>

            <a href="search.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'search.php' ? 'active' : ''; ?>">
                <i class="fas fa-search"></i>
                Search
            </a>

            <!-- User Menu -->
            <div class="dropdown user-menu">
                <div class="nav-link dropdown-toggle">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($currentUser['username']); ?>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="profile.php" class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-circle"></i>
                        Profile
                    </a>
                    <?php if (hasRole('admin')): ?>
                    <a href="admin/dashboard.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        Admin Panel
                    </a>
                    <?php endif; ?>
                    <a href="logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>
