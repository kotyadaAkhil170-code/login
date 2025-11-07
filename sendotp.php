<?php
include 'config.php';
require 'assets/PHPMailer.php';
require 'assets/SMTP.php';
require 'assets/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;

$email = $_POST['email'];
$otp = rand(100000, 999999);

// Save OTP
$stmt = $conn->prepare("UPDATE users SET otp=? WHERE email=?");
$stmt->bind_param("ss", $otp, $email);
$stmt->execute();
$stmt->close();

// Send Email
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'kotyadaakhil170@gmail.com'; // Replace with your Gmail
$mail->Password = 'icfh pyjw jead gtfb';   // Replace with your Gmail App Password
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('kotyadaakhil170@gmail.com', 'Auth System');
$mail->addAddress($email);
$mail->isHTML(true);
$mail->Subject = 'Your OTP Code';
$mail->Body = "<h3>Your OTP is: $otp</h3>";

$mail->send();
echo "OTP sent to your email.";
?>