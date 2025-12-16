<?php
session_start();
require_once __DIR__ . '/../db.php';

/* Fetch booked tables */
$stmt = $conn->query("
  SELECT table_code, customer_name, customer_phone
  FROM seat_bookings
");

$booked = [];
while ($row = $stmt->fetch_assoc()) {
  $booked[$row['table_code']] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin – Table Management</title>
  <style>
    body {
      background:#111;
      color:#fff;
      font-family: Arial, sans-serif;
      padding:20px;
    }
    h1 { margin-bottom:20px; }

    .grid {
      display:grid;
      grid-template-columns:repeat(auto-fill, minmax(120px,1fr));
      gap:15px;
    }

    .box {
      padding:14px;
      border-radius:8px;
      text-align:center;
      font-weight:bold;
    }

    .available {
      background:#1abc9c;
      color:#000;
    }

    .booked {
      background:#000;
      color:#fff;
    }

    small {
      display:block;
      font-size:12px;
      margin-top:6px;
      opacity:.8;
    }

    button {
      margin-top:8px;
      padding:6px 10px;
      border:none;
      border-radius:4px;
      cursor:pointer;
    }

    .release {
      background:#e74c3c;
      color:#fff;
    }

    .clear-all {
      margin-top:30px;
      background:#c0392b;
      color:#fff;
      padding:10px 20px;
      font-size:14px;
    }
  </style>
</head>
<body>

<h1>Admin – Table Management</h1>

<div class="grid">
<?php
for ($i = 1; $i <= 20; $i++) {
  $code = 'T' . str_pad($i, 2, '0', STR_PAD_LEFT);

  if (isset($booked[$code])) {
    echo "
      <div class='box booked'>
        $code
        <small>{$booked[$code]['customer_name']}</small>
        <small>{$booked[$code]['customer_phone']}</small>
        <form method='post' action='release.php'>
          <input type='hidden' name='table_code' value='$code'>
          <button class='release'>Release</button>
        </form>
      </div>
    ";
  } else {
    echo "
      <div class='box available'>
        $code
        <small>Available</small>
      </div>
    ";
  }
}
?>
</div>

<form method="post" action="clear_state.php">
  <button class="clear-all">Clear ALL Bookings</button>
</form>

</body>
</html>
