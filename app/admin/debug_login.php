<?php
require_once '../../db_config.php';

// Test credentials
$username = 'admin';
$password = 'admin123';

// Debug info array
$debug = [];

// 1. Check connection
$debug['connection'] = $conn->connect_error ? "Failed: " . $conn->connect_error : "Success";

// 2. Check if user exists
$stmt = $conn->prepare("SELECT id, username, password, status FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$debug['user_exists'] = $user ? "Yes" : "No";
if ($user) {
    $debug['user_status'] = $user['status'];
    $debug['stored_password_hash'] = $user['password'];
    
    // 3. Test password verification
    $debug['password_verify'] = password_verify($password, $user['password']) ? "Match" : "No Match";
    
    // 4. Generate new hash for comparison
    $debug['new_password_hash'] = password_hash($password, PASSWORD_DEFAULT);
}

// Output debug info
header('Content-Type: text/plain');
echo "Login Debug Information:\n";
echo "======================\n\n";
foreach ($debug as $key => $value) {
    echo str_pad($key . ": ", 25) . $value . "\n";
}

// Also show table structure
echo "\nTable Structure:\n";
echo "================\n";
$result = $conn->query("DESCRIBE admin_users");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
}
?>
