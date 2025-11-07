<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

$email = $_POST['email'];
$otp = $_POST['otp'];

// Fetch OTP from database
$stmt = $conn->prepare("SELECT otp FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($db_otp);
$stmt->fetch();
$stmt->close();

// Debugging (optional): log both values
// error_log("Entered OTP: $otp | DB OTP: $db_otp");

// Compare safely
if (!empty($db_otp) && trim($otp) === trim($db_otp)) {
  // Optional: clear OTP after success
  $stmt = $conn->prepare("UPDATE users SET otp=NULL WHERE email=?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  echo 'success';
} else {
  echo 'fail';
}
?>