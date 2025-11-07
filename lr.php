<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'];

  if ($action === 'login') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($db_pass);
    if ($stmt->fetch()) {
      if ($password === $db_pass) {
        $_SESSION['email'] = $email;
        echo 'Login successful';
      } else {
        echo 'Invalid email or password';
      }
    } else {
      echo 'Invalid email or password';
    }
    $stmt->close();

  } elseif ($action === 'register') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      echo "Email already registered";
    } else {
      $stmt->close();
      $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $name, $email, $password);
      $stmt->execute();
      echo "Registered successfully";
    }
    $stmt->close();
  }
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Modern Login/Register</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
    body {
      background: linear-gradient(to right, #e2e2e2, #c9d6ff);
      display: flex; align-items: center; justify-content: center;
      flex-direction: column; height: 100vh;
    }
    .container {
      background-color: #fff; border-radius: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
      position: relative; overflow: hidden;
      width: 768px; max-width: 100%; min-height: 480px;
    }
    .container p { font-size: 14px; line-height: 20px; letter-spacing: 0.3px; margin: 20px 0; }
    .container span { font-size: 12px; }
    .container a { color: #333; font-size: 13px; text-decoration: none; margin: 15px 0 10px; }
    .container button {
      background-color: #512da8; color: #fff; font-size: 12px;
      padding: 10px 45px; border: 1px solid transparent;
      border-radius: 8px; font-weight: 600; letter-spacing: 0.5px;
      text-transform: uppercase; margin-top: 10px; cursor: pointer;
    }
    .container button.hidden { background-color: transparent; border-color: #fff; }
    .container form {
      background-color: #fff; display: flex;
      align-items: center; justify-content: center;
      flex-direction: column; padding: 0 40px; height: 100%;
    }
    .container input {
      background-color: #eee; border: none;
      margin: 8px 0; padding: 10px 15px; font-size: 13px;
      border-radius: 8px; width: 100%; outline: none;
    }
    .form-container { position: absolute; top: 0; height: 100%; transition: all 0.6s ease-in-out; }
    .sign-in { left: 0; width: 50%; z-index: 2; }
    .container.active .sign-in { transform: translateX(100%); }
    .sign-up { left: 0; width: 50%; opacity: 0; z-index: 1; }
    .container.active .sign-up {
      transform: translateX(100%); opacity: 1; z-index: 5;
      animation: move 0.6s;
    }
    @keyframes move {
      0%, 49.99% { opacity: 0; z-index: 1; }
      50%, 100% { opacity: 1; z-index: 5; }
    }
    .social-icons { margin: 20px 0; }
    .social-icons a {
      border: 1px solid #ccc; border-radius: 20%;
      display: inline-flex; justify-content: center; align-items: center;
      margin: 0 3px; width: 40px; height: 40px;
    }
    .toggle-container {
      position: absolute; top: 0; left: 50%;
      width: 50%; height: 100%; overflow: hidden;
      transition: all 0.6s ease-in-out;
      border-radius: 150px 0 0 100px; z-index: 1000;
    }
    .container.active .toggle-container {
      transform: translateX(-100%);
      border-radius: 0 150px 100px 0;
    }
    .toggle {
     background: linear-gradient(to right, #c82333, #dc3545);
      color: #fff; position: relative; left: -100%;
      height: 100%; width: 200%; transform: translateX(0);
      transition: all 0.6s ease-in-out;
    }
    .container.active .toggle { transform: translateX(50%); }
    .toggle-panel {
      position: absolute; width: 50%; height: 100%;
      display: flex; align-items: center; justify-content: center;
      flex-direction: column; padding: 0 30px; text-align: center;
      top: 0; transition: all 0.6s ease-in-out;
    }
    .toggle-left { transform: translateX(-200%); }
    .container.active .toggle-left { transform: translateX(0); }
    .toggle-right { right: 0; transform: translateX(0); }
    .container.active .toggle-right { transform: translateX(200%); }
  </style>
</head>
<body>
  <div class="container" id="container">
    <div class="form-container sign-up">
      <form id="registerForm" onsubmit="event.preventDefault(); submitForm('register');">
        <h1>Create Account</h1>
        <div class="social-icons">
          <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
          <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
          <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
        <span>or use your email for registration</span>
        <input type="text" name="name" placeholder="Name" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Sign Up</button>
      </form>
    </div>

    <div class="form-container sign-in">
      <form id="loginForm" onsubmit="event.preventDefault(); submitForm('login');">
        <h1>Sign In</h1>
        <div class="social-icons">
          <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
          <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
          <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
        <span>or use your email password</span>
        <input type="email        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <a href="forgot.php">Forget Your Password?</a>
        <button type="submit">Sign In</button>
      </form>
    </div>

    <!-- Toggle Panels -->
    <div class="toggle-container">
      <div class="toggle">
        <div class="toggle-panel toggle-left">
          <h1>Welcome Back!</h1>
          <p>Enter your personal details to use all of site features</p>
          <button class="hidden" id="login">Sign In</button>
        </div>
        <div class="toggle-panel toggle-right">
          <h1>Hello, Friend!</h1>
          <p>Register with your personal details to use all of site features</p>
          <button class="hidden" id="register">Sign Up</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');

    registerBtn.addEventListener('click', () => {
      container.classList.add("active");
    });

    loginBtn.addEventListener('click', () => {
      container.classList.remove("active");
    });

    function submitForm(type) {
      const formData = new FormData(document.getElementById(type + 'Form'));
      formData.append('action', type);

      fetch('', {
        method: 'POST',
        body: formData
      })
      .then(res => res.text())
      .then(data => {
        alert(data);
        if (data.includes('Login successful')) {
          window.location.href = 'dashboard.php';
        }
      });
    }
  </script>
</body>
</html>

