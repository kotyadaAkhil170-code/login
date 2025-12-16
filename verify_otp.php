<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db.php'; // MUST define $conn

$email = trim($_POST['email'] ?? '');
$otp   = trim($_POST['otp'] ?? '');

if ($email === '' || $otp === '') {
    echo 'fail';
    exit;
}

// Fetch OTP from database
$stmt = $conn->prepare("SELECT otp FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($db_otp);
$stmt->fetch();
$stmt->close();

// Compare safely
if (!empty($db_otp) && trim($otp) === trim($db_otp)) {

    // Clear OTP after success
    $stmt = $conn->prepare("UPDATE users SET otp=NULL WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();

    echo 'success';
} else {
    echo 'fail';
}
