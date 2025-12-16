<?php
require "db.php";
$data = json_decode(file_get_contents("php://input"), true);

$seat = $data["seat"] ?? "";
$name = $data["name"] ?? "";
$phone = $data["phone"] ?? "";

if(!$seat || !$name || !$phone){
  echo json_encode(["error"=>"Missing data"]);
  exit;
}

$stmt = $conn->prepare(
  "INSERT INTO seat_bookings (table_code, customer_name, customer_phone)
   VALUES (?, ?, ?)"
);
$stmt->bind_param("sss", $seat, $name, $phone);

if($stmt->execute()){
  echo json_encode(["success"=>true]);
}else{
  echo json_encode(["error"=>"Seat already booked"]);
}
$stmt->close();
