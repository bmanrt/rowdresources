<?php
header('Content-Type: application/json');

$response = array(
    'status' => 'success',
    'data' => array(
        'message' => 'Hello from PHP!',
        'value' => 42
    )
);

echo json_encode($response);
?>
