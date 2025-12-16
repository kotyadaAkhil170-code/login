<?php
$host = "localhost";
$db   = "auth_db";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("DB Connection failed");
}
