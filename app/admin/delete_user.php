<?php
require_once '../../db_config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    
    try {
        // Delete the user
        $delete_user = $conn->prepare("DELETE FROM app_users WHERE id = ?");
        $delete_user->bind_param("i", $user_id);
        $delete_user->execute();

        if ($delete_user->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            throw new Exception('User not found');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
