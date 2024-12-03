<?php
require_once '../../db_config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get admin info
$stmt = $conn->prepare("
    SELECT username, full_name, last_login
    FROM admin_users 
    WHERE id = ?
");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// Get recent activities with error handling
$activities_query = "
    (SELECT 
        'media' as type, 
        COALESCE(m.file_path, 'Untitled Media') as title, 
        m.created_at, 
        u.username,
        u.full_name
    FROM user_media m 
    JOIN app_users u ON m.user_id = u.id)
    UNION ALL
    (SELECT 
        'user' as type, 
        COALESCE(full_name, username) as title, 
        created_at, 
        username,
        full_name
    FROM app_users)
    ORDER BY created_at DESC 
    LIMIT 10
";

try {
    $activities = $conn->query($activities_query);
    if (!$activities) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    $activities_error = "Failed to load recent activities: " . $e->getMessage();
}

// Get statistics with error handling
$stats = [
    'total_users' => 0,
    'total_media' => 0,
    'recent_uploads' => 0,
    'recent_users' => 0
];

try {
    // Get total users from app_users
    $result = $conn->query("SELECT COUNT(*) as count FROM app_users");
    if ($result) {
        $stats['total_users'] = $result->fetch_assoc()['count'];
    }

    // Get total media from user_media
    $result = $conn->query("SELECT COUNT(*) as count FROM user_media");
    if ($result) {
        $stats['total_media'] = $result->fetch_assoc()['count'];
    }

    // Get recent uploads from user_media (last 7 days)
    $result = $conn->query("SELECT COUNT(*) as count FROM user_media WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    if ($result) {
        $stats['recent_uploads'] = $result->fetch_assoc()['count'];
    }

    // Get recent users from app_users (last 7 days)
    $result = $conn->query("SELECT COUNT(*) as count FROM app_users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    if ($result) {
        $stats['recent_users'] = $result->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    $stats_error = "Failed to load statistics: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rowd Resources</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #2c3e50;
            color: white;
            padding-top: 1rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.8rem 1.5rem;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-card h3 {
            margin: 0;
            color: #2c3e50;
        }
        .stats-card p {
            margin: 0.5rem 0 0 0;
            color: #666;
        }
        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-item .icon {
            width: 40px;
            height: 40px;
            border-radius: 20px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        .logo-section {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }
        .logo-section h4 {
            color: white;
            margin: 0;
        }
        .error-state {
            text-align: center;
            padding: 2rem;
            background: #fff3f3;
            border-radius: 10px;
            color: #dc3545;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-section">
            <h4>Rowd Resources</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.php">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="media.php">
                    <i class="bi bi-collection-play"></i> Media
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Dashboard</h4>
            <div class="user-info">
                <span class="me-2"><?php echo htmlspecialchars($admin['full_name']); ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>

        <div class="container-fluid px-0">
            <?php if (isset($stats_error)): ?>
                <div class="error-state">
                    <i class="bi bi-exclamation-triangle fs-1 mb-3"></i>
                    <h5>Error Loading Statistics</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($stats_error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="bi bi-people fs-1 mb-2 text-primary"></i>
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="bi bi-collection-play fs-1 mb-2 text-success"></i>
                        <h3><?php echo number_format($stats['total_media']); ?></h3>
                        <p>Total Media</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="bi bi-cloud-upload fs-1 mb-2 text-info"></i>
                        <h3><?php echo number_format($stats['recent_uploads']); ?></h3>
                        <p>Recent Uploads</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="bi bi-person-plus fs-1 mb-2 text-warning"></i>
                        <h3><?php echo number_format($stats['recent_users']); ?></h3>
                        <p>New Users</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Activities -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Activities</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (isset($activities_error)): ?>
                                <div class="error-state m-3">
                                    <i class="bi bi-exclamation-triangle fs-1 mb-3"></i>
                                    <h5>Error Loading Activities</h5>
                                    <p class="mb-0"><?php echo htmlspecialchars($activities_error); ?></p>
                                </div>
                            <?php else: ?>
                                <?php if ($activities && $activities->num_rows > 0): ?>
                                    <?php while ($activity = $activities->fetch_assoc()): ?>
                                        <div class="activity-item d-flex align-items-center">
                                            <div class="icon">
                                                <i class="bi bi-<?php echo $activity['type'] === 'media' ? 'collection-play' : 'person'; ?>"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0">
                                                    <?php if ($activity['type'] === 'media'): ?>
                                                        New media uploaded: <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                                        <small class="text-muted">by <?php echo htmlspecialchars($activity['full_name'] ?? $activity['username']); ?></small>
                                                    <?php else: ?>
                                                        New user registered: <strong><?php echo htmlspecialchars($activity['full_name'] ?? $activity['username']); ?></strong>
                                                    <?php endif; ?>
                                                </p>
                                                <small class="text-muted">
                                                    <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted">
                                        <i class="bi bi-clock-history fs-1 mb-2"></i>
                                        <p class="mb-0">No recent activities</p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- System Info -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">System Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">PHP Version</small>
                                <strong><?php echo PHP_VERSION; ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">MySQL Version</small>
                                <strong><?php echo $conn->get_server_info(); ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Server Software</small>
                                <strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?></strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Last Login</small>
                                <strong><?php echo $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never'; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
