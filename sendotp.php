<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

$conn = new mysqli("localhost", "root", "", "auth_db");
if ($conn->connect_error) {
    die("DB connection failed");
}

$email = trim($_POST['email'] ?? '');
if ($email === '') {
    echo "Email required";
    exit;
}

$otp = rand(100000, 999999);

// Save OTP
$stmt = $conn->prepare("UPDATE users SET otp=? WHERE email=?");
$stmt->bind_param("ss", $otp, $email);
$stmt->execute();
$stmt->close();

// Send email
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'kotyadaakhil170@gmail.com';
    $mail->Password   = 'icfh pyjw jead gtfb';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('kotyadaakhil170@gmail.com', 'OTP Verification');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your OTP Code';
    $mail->Body    = "<h2>Your OTP</h2><b>$otp</b>";

    $mail->send();
    echo "OTP sent successfully";

} catch (Exception $e) {
    echo "Mail error";
}
