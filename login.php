<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Fetch password from database
  $stmt = $conn->prepare("SELECT password FROM users WHERE email=?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->bind_result($db_pass);
  $stmt->fetch();
  $stmt->close();

  // Compare plain text passwords
  if ($password === $db_pass) {
    $_SESSION['email'] = $email;
    echo "<script>alert('Login successful'); window.location.href='dashboard.php';</script>";
  } else {
    echo "<script>alert('Invalid email or password');</script>";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2 class="text-center mb-4"><i class="bi bi-person-circle"></i> Login</h2>
  <form method="POST">
    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
    <button type="submit" class="btn btn-primary w-100">Login</button>
  </form>
</div>
</body>
</html>