<?php
require_once 'db_config.php';

try {
    // Read the SQL file
    $sql = file_get_contents('add_columns.sql');
    
    // Execute the SQL
    if ($conn->multi_query($sql)) {
        echo "Columns added successfully\n";
    } else {
        throw new Exception("Error executing SQL: " . $conn->error);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
