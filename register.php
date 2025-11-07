<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
  <h2 class="text-center"><i class="bi bi-person-plus"></i> Register</h2>
  <form method="POST">
    <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
    <button type="submit" name="register" class="btn btn-red w-100">Register</button>
  </form>
</div>

<?php
if (isset($_POST['register'])) {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $plainPassword = $_POST['password']; // Store plain password

  // Insert plain password directly (not recommended for real apps)
  $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  if ($stmt) {
    $stmt->bind_param("sss", $name, $email, $plainPassword);
    if ($stmt->execute()) {
      echo "<script>alert('Registered successfully');</script>";
    } else {
      echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
  } else {
    echo "<script>alert('Prepare failed: " . $conn->error . "');</script>";
  }
}
?>
</body>
</html>