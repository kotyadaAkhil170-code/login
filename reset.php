<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

if (!isset($_GET['token'])) {
  die("Invalid token");
}
$token = $_GET['token'];

if (isset($_POST['reset'])) {
  $newPassword = $_POST['password'];
  $confirmPassword = $_POST['confirm_password'];

  if ($newPassword !== $confirmPassword) {
    echo "<script>alert('Passwords do not match');</script>";
  } else {
    // Update password and clear token
    $stmt = $conn->prepare("UPDATE users SET password=?, token=NULL WHERE token=?");
    $stmt->bind_param("ss", $newPassword, $token);
    if ($stmt->execute()) {
      echo "<script>alert('Password updated successfully'); window.location='login.php';</script>";
    } else {
      echo "<script>alert('Error updating password');</script>";
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
  <h2 class="text-center">Reset Password</h2>
  <form method="POST">
    <input type="password" name="password" class="form-control mb-2" placeholder="New Password" required>
    <input type="password" name="confirm_password" class="form-control mb-2" placeholder="Confirm Password" required>
    <button type="submit" name="reset" class="btn btn-red w-100">Update Password</button>
  </form>
</div>
</body>
</html>