<?php
require_once '../../db_config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to safely display values
function debug_var($var) {
    return htmlspecialchars(print_r($var, true));
}

echo "<pre>";

// Test database connection
echo "Database Connection Test:\n";
echo "=======================\n";
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error . "\n";
} else {
    echo "Connection successful\n";
}

// Show all admin users and their status
echo "\nAll Admin Users:\n";
echo "===============\n";
$result = $conn->query("SELECT username, email, status, password FROM admin_users");
while ($row = $result->fetch_assoc()) {
    echo "Username: " . $row['username'] . "\n";
    echo "Email: " . $row['email'] . "\n";
    echo "Status: " . $row['status'] . "\n";
    echo "Password Hash Length: " . strlen($row['password']) . "\n";
    echo "------------------------\n";
}

// Test specific login
$test_username = 'admin';
$test_password = 'admin123';

echo "\nTesting Login for '{$test_username}':\n";
echo "============================\n";

$stmt = $conn->prepare("SELECT id, username, password, status FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $test_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    if ($user = $result->fetch_assoc()) {
        echo "User found:\n";
        echo "Status: " . $user['status'] . "\n";
        echo "Password Hash: " . $user['password'] . "\n";
        echo "Password Verification: " . (password_verify($test_password, $user['password']) ? "SUCCESS" : "FAILED") . "\n";
        
        // Test the WHERE clause with status
        echo "\nTesting full WHERE clause:\n";
        $stmt2 = $conn->prepare("SELECT id FROM admin_users WHERE username = ? AND status = 'active'");
        $stmt2->bind_param("s", $test_username);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        echo "User found with status check: " . ($result2->num_rows > 0 ? "YES" : "NO") . "\n";
    } else {
        echo "User not found\n";
    }
} else {
    echo "Query failed: " . $conn->error . "\n";
}

echo "</pre>";
?>
