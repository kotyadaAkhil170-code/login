<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Montserrat', sans-serif;
    }

    body {
      background: linear-gradient(to right, #f8d7da, #ffffff);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .forgot-box {
      background-color: #fff;
      border-radius: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      padding: 40px;
      max-width: 400px;
      width: 100%;
      text-align: center;
    }

    .forgot-box h2 {
      color: #dc3545;
      margin-bottom: 20px;
    }

    .forgot-box input {
      background-color: #f8f9fa;
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 12px;
      font-size: 14px;
      margin-bottom: 20px;
      width: 100%;
    }

    .forgot-box button {
      background-color: #dc3545;
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-weight: 600;
      width: 100%;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .forgot-box button:hover {
      background-color: #c82333;
    }

    .step {
      display: none;
    }

    .step.active {
      display: block;
    }
  </style>
</head>
<body>
  <div class="forgot-box">
    <h2><i class="bi bi-envelope"></i> Forgot Password</h2>

    <div id="step1" class="step active">
      <input type="email" id="email" placeholder="Enter your email" required>
      <button onclick="sendOTP()">Send OTP</button>
    </div>

    <div id="step2" class="step">
      <input type="text" id="otp" placeholder="Enter OTP" required>
      <button onclick="verifyOTP()">Verify OTP</button>
    </div>

    <div id="step3" class="step">
      <input type="password" id="newpass" placeholder="New Password" required>
      <button onclick="resetPassword()">Reset Password</button>
    </div>
  </div>

  <script>
    function sendOTP() {
      const email = document.getElementById('email').value;
      if (!email) return alert("Please enter your email.");
      fetch('sendotp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email)
      })
      .then(res => res.text())
      .then(data => {
        alert(data);
        showStep(2);
      });
    }

    function verifyOTP() {
      const email = document.getElementById('email').value;
      const otp = document.getElementById('otp').value;
      if (!otp) return alert("Please enter the OTP.");
      fetch('verifyotp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email) + '&otp=' + encodeURIComponent(otp)
      })
      .then(res => res.text())
      .then(data => {
        if (data === 'success') {
          showStep(3);
        } else {
          alert('Invalid OTP');
        }
      });
    }

    function resetPassword() {
      const email = document.getElementById('email').value;
      const newpass = document.getElementById('newpass').value;
      if (!newpass) return alert("Please enter a new password.");
      fetch('resetpassword.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email) + '&newpass=' + encodeURIComponent(newpass)
      })
      .then(res => res.text())
      .then(data => {
        alert(data);
        window.location.href = 'index.php';
      });
    }

    function showStep(step) {
      document.querySelectorAll('.step').forEach(div => div.classList.remove('active'));
      document.getElementById('step' + step).classList.add('active');
    }
  </script>
</body>
</html>