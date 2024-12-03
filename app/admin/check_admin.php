<?php
require_once '../../db_config.php';

// Check if admin_users table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($tableCheck->num_rows === 0) {
    die("admin_users table does not exist! Please run setup.php first.");
}

// Check if admin user exists
$stmt = $conn->prepare("SELECT id, username, email FROM admin_users WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Admin user does not exist! Please run setup.php to create the default admin user.");
}

$admin = $result->fetch_assoc();
echo "Admin user exists with ID: " . $admin['id'] . " and email: " . $admin['email'] . "\n";

// Create new admin password for testing
$newPassword = 'admin123';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update admin password
$stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $hashedPassword);
if ($stmt->execute()) {
    echo "Admin password has been reset to: " . $newPassword;
} else {
    echo "Error updating password: " . $stmt->error;
}
?>
