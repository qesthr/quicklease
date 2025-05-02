<?php
require_once '../loginpage/includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: bookings.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "Booking not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Booking</title>
</head>
<body>
    <h2>Booking Details</h2>
    <p><strong>Booking ID:</strong> <?= htmlspecialchars($booking['id']) ?></p>
    <p><strong>Customer Name:</strong> <?= htmlspecialchars($booking['customer_name']) ?></p>
    <p><strong>Car Model:</strong> <?= htmlspecialchars($booking['car_model']) ?></p>
    <p><strong>Booking Date:</strong> <?= $booking['booking_date'] ?></p>
    <p><strong>Return Date:</strong> <?= $booking['return_date'] ?></p>
    <p><strong>Status:</strong> <?= $booking['status'] ?></p>
    <a href="bookings.php">Back to Bookings</a>
</body>
</html>
