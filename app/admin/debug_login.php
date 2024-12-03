<?php
require_once '../../db_config.php';

// Test both sets of credentials
$tests = [
    ['username' => 'admin', 'password' => 'admin123'],
    ['username' => 'admin', 'password' => '12345678'],
    ['username' => 'mayo', 'password' => '12345678']
];

foreach ($tests as $test) {
    $username = $test['username'];
    $password = $test['password'];
    
    echo "\nTesting login for username: {$username}\n";
    echo "====================================\n";
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, username, password, status FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        echo "User found\n";
        echo "Status: {$user['status']}\n";
        echo "Stored hash: {$user['password']}\n";
        echo "Password verification: " . (password_verify($password, $user['password']) ? "SUCCESS" : "FAILED") . "\n";
        
        // Generate a new hash for this password
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "New hash for '{$password}': {$new_hash}\n";
        echo "Verify new hash: " . (password_verify($password, $new_hash) ? "SUCCESS" : "FAILED") . "\n";
    } else {
        echo "User not found\n";
    }
    echo "\n";
}

// Show all admin users
echo "\nAll Admin Users:\n";
echo "================\n";
$result = $conn->query("SELECT username, email, status FROM admin_users");
while ($row = $result->fetch_assoc()) {
    echo "Username: {$row['username']}, Email: {$row['email']}, Status: {$row['status']}\n";
}
?>
