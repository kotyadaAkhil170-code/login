<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db.php'; // must create $conn

$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo 'fail';
    exit;
}

$stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
$stmt->bind_param("ss", $password, $email);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'fail';
}

$stmt->close();
