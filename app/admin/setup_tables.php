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

        // App Users Table
        "CREATE TABLE IF NOT EXISTS app_users (
            id INT NOT NULL AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL DEFAULT NULL,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            role ENUM('user', 'admin') DEFAULT 'user',
            profile_image VARCHAR(255) DEFAULT NULL,
            reset_token VARCHAR(64) DEFAULT NULL,
            reset_token_expires TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        // User Media Table
        "CREATE TABLE IF NOT EXISTS user_media (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            media_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            media_type VARCHAR(50) NOT NULL,
            thumbnail_url VARCHAR(255),
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status ENUM('active', 'inactive', 'processing') DEFAULT 'active',
            views INT DEFAULT 0,
            PRIMARY KEY (id),
            FOREIGN KEY (user_id) REFERENCES app_users(id) ON DELETE CASCADE
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

// Create default admin user
function createDefaultAdmin($conn) {
    $username = 'admin';
    $email = 'admin@example.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $full_name = 'System Administrator';

    $sql = "INSERT IGNORE INTO admin_users (username, email, password, full_name) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $password, $full_name);
    
    if (!$stmt->execute()) {
        echo "Error creating default admin: " . $stmt->error . "<br>";
        return false;
    }
    
    return true;
}

// Run setup
echo "<h2>Setting up database tables...</h2>";

if (createTables($conn)) {
    echo "Tables created successfully.<br>";
    
    if (createDefaultAdmin($conn)) {
        echo "Default admin user created successfully.<br>";
        echo "<p>Username: admin<br>Password: admin123</p>";
    }
} else {
    echo "Error setting up database.<br>";
}
?>
