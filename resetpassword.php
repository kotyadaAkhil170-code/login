<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

$email = $_POST['email'];
$newpass = $_POST['newpass']; // No hashing

$stmt = $conn->prepare("UPDATE users SET password=?, otp=NULL WHERE email=?");
$stmt->bind_param("ss", $newpass, $email);
$stmt->execute();
$stmt->close();

echo "Password reset successful.";
?>