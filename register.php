<?php
include('db_config.php');

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$country = $_POST['country'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Insert data into database
$sql = "INSERT INTO users (name, email, phone, country, password) VALUES ('$name', '$email', '$phone', '$country', '$password')";

if ($conn->query($sql) === TRUE) {
    echo "Registration successful!";
    header("Location: login.html");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
