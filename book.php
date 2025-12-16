<?php
header("Content-Type: application/json");
require __DIR__ . "/../db.php";

$data = json_decode(file_get_contents("php://input"), true);

$name   = trim($data["customer_name"] ?? "");
$phone  = trim($data["customer_phone"] ?? "");
$tables = $data["tables"] ?? [];

if (!$name || !$phone || empty($tables)) {
  echo json_encode(["ok"=>false,"message"=>"Invalid input"]);
  exit;
}

foreach ($tables as $code) {

  // 🔒 HARD BACKEND LOCK
  $check = $conn->prepare(
    "SELECT 1 FROM seat_bookings WHERE table_code = ? LIMIT 1"
  );
  $check->bind_param("s", $code);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    echo json_encode([
      "ok"=>false,
      "message"=>"Table already booked: $code"
    ]);
    exit;
  }
  $check->close();

  // Insert booking
  $stmt = $conn->prepare(
    "INSERT INTO seat_bookings (table_code, customer_name, customer_phone)
     VALUES (?,?,?)"
  );
  $stmt->bind_param("sss", $code, $name, $phone);
  $stmt->execute();
}

echo json_encode(["ok"=>true]);
