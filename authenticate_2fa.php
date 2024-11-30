<?php
session_start();
include('db_config.php');

$code = $_POST['code'];
$stored_code = $_SESSION['2fa_code'];

if ($code == $stored_code) {
    unset($_SESSION['2fa_code']);
    header("Location: dashboard.php");
} else {
    echo "Invalid code. Please try again.";
}
?>
