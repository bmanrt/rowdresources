<?php
require_once '../../db_config.php';

function createTables($conn) {
    $tables = [
        // Admin Users Table
        "CREATE TABLE IF NOT EXISTS admin_users (
            id INT NOT NULL AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        // Admin Logs Table
        "CREATE TABLE IF NOT EXISTS admin_logs (
            id INT NOT NULL AUTO_INCREMENT,
            admin_id INT NOT NULL,
            action_type ENUM('login', 'user_management', 'media_management', 'settings_change') NOT NULL,
            action_description TEXT NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        // Media Stats Table
        "CREATE TABLE IF NOT EXISTS media_stats (
            id INT NOT NULL AUTO_INCREMENT,
            video_id INT NOT NULL,
            views INT DEFAULT 0,
            likes INT DEFAULT 0,
            shares INT DEFAULT 0,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_video (video_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ];

    foreach ($tables as $sql) {
        if (!$conn->query($sql)) {
            echo "Error creating table: " . $conn->error . "<br>";
            return false;
        }
    }
    return true;
}

function createDefaultAdmin($conn) {
    // Check if admin already exists
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Create default admin user
        $username = 'admin';
        $email = 'admin@rowdresources.com';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $fullName = 'System Administrator';
        
        $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $fullName);
        
        if (!$stmt->execute()) {
            echo "Error creating admin user: " . $stmt->error . "<br>";
            return false;
        }
        echo "Default admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Admin user already exists.<br>";
    }
    return true;
}

// Run setup
echo "<h2>Setting up admin system...</h2>";

if (createTables($conn)) {
    echo "Tables created successfully!<br>";
    if (createDefaultAdmin($conn)) {
        echo "Setup completed successfully!<br>";
        echo "<a href='login.php'>Go to Admin Login</a>";
    }
} else {
    echo "Error during setup!<br>";
}
?>
