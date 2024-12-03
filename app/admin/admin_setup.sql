-- Create admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create admin activity logs table
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT NOT NULL AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action_type ENUM('login', 'user_management', 'media_management', 'settings_change') NOT NULL,
    action_description TEXT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create media statistics table
CREATE TABLE IF NOT EXISTS media_stats (
    id INT NOT NULL AUTO_INCREMENT,
    video_id INT NOT NULL,
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    shares INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_video (video_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, email, password, full_name) 
VALUES ('admin', 'admin@rowdresources.com', '$2y$10$8tDjcFT0ZhPjqafT.z4IcegkFE3NxhXQFR/NOuqgJqGzJZGFx.wMW', 'System Administrator');

-- Create stored procedure for dashboard statistics
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS get_dashboard_stats()
BEGIN
    -- Get total active admins
    SELECT COUNT(*) as total_admins 
    FROM admin_users 
    WHERE status = 'active';
    
    -- Get total media items
    SELECT COUNT(*) as total_media 
    FROM videos;
    
    -- Get recent activities
    SELECT au.username, v.title, v.created_at
    FROM videos v
    JOIN app_users au ON v.user_id = au.id
    ORDER BY v.created_at DESC
    LIMIT 10;
END //
DELIMITER ;
