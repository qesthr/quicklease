<?php
require_once '../loginpage/includes/db.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = $_POST['customer_name'];
    $carModel = $_POST['car_model'];
    $bookingDate = $_POST['booking_date'];
    $returnDate = $_POST['return_date'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE bookings SET customer_name = ?, car_model = ?, booking_date = ?, return_date = ?, status = ? WHERE id = ?");
    $stmt->execute([$customerName, $carModel, $bookingDate, $returnDate, $status, $id]);

    header("Location: bookings.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Booking</title>
</head>
<body>
    <h2>Edit Booking</h2>
    <form method="POST">
        <label>Customer Name: <input type="text" name="customer_name" value="<?= htmlspecialchars($booking['customer_name']) ?>" required></label><br>
        <label>Car Model: <input type="text" name="car_model" value="<?= htmlspecialchars($booking['car_model']) ?>" required></label><br>
        <label>Booking Date: <input type="date" name="booking_date" value="<?= $booking['booking_date'] ?>" required></label><br>
        <label>Return Date: <input type="date" name="return_date" value="<?= $booking['return_date'] ?>" required></label><br>
        <label>Status:
            <select name="status">
                <option value="Active" <?= $booking['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Completed" <?= $booking['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="Cancelled" <?= $booking['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </label><br>
        <button type="submit">Update Booking</button>
    </form>
</body>
</html>
