<?php
header('Content-Type: application/json');
include('db_config.php');

// Fetch users and their data capture entry counts
$sql = "SELECT users.name, COUNT(captured_data.id) AS entries
        FROM users
        LEFT JOIN captured_data ON users.id = captured_data.user_id
        GROUP BY users.id
        ORDER BY entries DESC";
$result = $conn->query($sql);

$leaderboards = [];
while ($row = $result->fetch_assoc()) {
    $leaderboards[] = [
        'name' => $row['name'],
        'entries' => $row['entries']
    ];
}

echo json_encode($leaderboards);
?>
