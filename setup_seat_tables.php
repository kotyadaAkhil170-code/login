<?php
// setup_seat_tables.php
// Run this ONCE to populate seat_tables based on booking.html layout

require_once 'db.php'; // reuse your existing DB connection

/*
Seat & price rules (MATCHES booking.html)
----------------------------------------
2-seater (AED 200):
T01–T09, T19, T20

3-seater (AED 300):
T10–T18
*/

$twoSeaters   = array_merge(range(1, 9), [19, 20]);
$threeSeaters = range(10, 18);

$inserted = 0;

for ($i = 1; $i <= 20; $i++) {

    $tableCode = "T" . str_pad($i, 2, "0", STR_PAD_LEFT);

    if (in_array($i, $twoSeaters)) {
        $seats = 2;
        $price = 200;
    } else {
        $seats = 3;
        $price = 300;
    }

    // Check if table already exists
    $check = $conn->prepare(
        "SELECT id FROM seat_tables WHERE table_code = ?"
    );
    $check->bind_param("s", $tableCode);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {

        $stmt = $conn->prepare(
            "INSERT INTO seat_tables (table_code, seat_count, status)
             VALUES (?, ?, 'available')"
        );
        $stmt->bind_param("si", $tableCode, $seats);
        $stmt->execute();
        $stmt->close();

        $inserted++;
    }

    $check->close();
}

echo "<h2>Seat table setup complete</h2>";
echo "<p>New tables inserted: <strong>$inserted</strong></p>";
echo "<p>If 0 inserted, tables already exist.</p>";
echo "<p><strong>IMPORTANT:</strong> Delete <code>setup_seat_tables.php</code> after running.</p>";
