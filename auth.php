<?php
// auth.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

/* ---------- DATABASE CONFIG ---------- */
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "auth_db";   // <-- make sure this DB exists

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo "Database connection failed";
    exit;
}

/* ---------- ONLY POST REQUESTS ---------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request";
    exit;
}

$action = $_POST['action'] ?? '';

/* ======================================================
   LOGIN
====================================================== */
if ($action === 'login') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        echo "Email and password required";
        exit;
    }

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo "User not found";
        exit;
    }

    $stmt->bind_result($uid, $db_pass);
    $stmt->fetch();

    // PLAIN TEXT comparison (as requested)
    if ($password === $db_pass) {
        $_SESSION['user_id'] = $uid;
        $_SESSION['email']   = $email;
        echo "Login successful";
    } else {
        echo "Invalid password";
    }

    $stmt->close();
    exit;
}

/* ======================================================
   REGISTER
====================================================== */
if ($action === 'register') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        echo "All fields are required";
        exit;
    }

    // check existing user
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "Email already registered";
        exit;
    }
    $check->close();

    // insert new user (plain password)
    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        echo "Registration successful";
    } else {
        echo "Registration failed";
    }

    $stmt->close();
    exit;
}

/* ======================================================
   FALLBACK
====================================================== */
echo "Unknown action";
exit;
