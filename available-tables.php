<?php
header("Content-Type: application/json");
require __DIR__ . "/../db.php";

$sql = "
SELECT 
  v.table_code,
  v.price,
  CASE 
    WHEN b.table_code IS NULL THEN 1
    ELSE 0
  END AS available
FROM venue_tables v
LEFT JOIN seat_bookings b 
  ON v.table_code = b.table_code
ORDER BY v.table_code
";

$res = $conn->query($sql);

$tables = [];
while ($row = $res->fetch_assoc()) {
  $tables[] = [
    "table_code" => $row["table_code"],
    "price"      => $row["price"],
    "available"  => (bool)$row["available"]
  ];
}

echo json_encode([
  "ok" => true,
  "tables" => $tables
]);
