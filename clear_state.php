<?php
session_start();

/* STEP A: load DB */
require_once __DIR__ . '/../db.php';

/* STEP B: HARD DEBUG (temporary) */
if (!$conn) {
  die("DB connection FAILED");
}

/* STEP C: DELETE BOOKINGS */
$sql = "DELETE FROM seat_bookings";
$result = $conn->query($sql);

if (!$result) {
  die("DELETE FAILED: " . $conn->error);
}

/* STEP D: CONFIRM */
echo "ALL BOOKINGS CLEARED SUCCESSFULLY";
exit;
