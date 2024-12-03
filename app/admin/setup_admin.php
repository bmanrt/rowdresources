<?php
require_once '../../db_config.php';

function setupAdmin($userId) {
    global $conn;
    
    try {
        // Update user to admin role
        $stmt = $conn->prepare("UPDATE app_users SET role = 'admin' WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo "Successfully updated user to admin role.<br>";
        } else {
            echo "No user found with ID $userId.<br>";
            return false;
        }
        
        // Create necessary tables
        $sqlFile = file_get_contents(__DIR__ . '/admin_setup.sql');
        $queries = explode(';', $sqlFile);
        
        foreach ($queries as $query) {
            if (trim($query) != '') {
                $conn->query($query);
            }
        }
        
        echo "Admin setup completed successfully!";
        return true;
    } catch (Exception $e) {
        echo "Error during setup: " . $e->getMessage();
        return false;
    }
}

// Usage example:
// Replace 1 with the ID of the user you want to make admin
// setupAdmin(1);
?>
