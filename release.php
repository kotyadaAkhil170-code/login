<?php
require __DIR__ . '/../db.php';

if (!isset($_POST['table_code'])) {
  header("Location: manage.php");
  exit;
}

$tableCode = $_POST['table_code'];

// DELETE booking for this table
$stmt = $conn->prepare(
  "DELETE FROM seat_bookings WHERE table_code = ?"
);
$stmt->bind_param("s", $tableCode);
$stmt->execute();
$stmt->close();

// Go back to admin panel
header("Location: manage.php");
exit;
