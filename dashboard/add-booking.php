<?php
require_once '../loginpage/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = $_POST['customer_name'];
    $carModel = $_POST['car_model'];
    $bookingDate = $_POST['booking_date'];
    $returnDate = $_POST['return_date'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("INSERT INTO bookings (customer_name, car_model, booking_date, return_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$customerName, $carModel, $bookingDate, $returnDate, $status]);

    header("Location: bookings.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Booking</title>
</head>
<body>
    <h2>Add New Booking</h2>
    <form method="POST">
        <label>Customer Name: <input type="text" name="customer_name" required></label><br>
        <label>Car Model: <input type="text" name="car_model" required></label><br>
        <label>Booking Date: <input type="date" name="booking_date" required></label><br>
        <label>Return Date: <input type="date" name="return_date" required></label><br>
        <label>Status:
            <select name="status">
                <option value="Active">Active</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </label><br>
        <button type="submit">Save Booking</button>
    </form>
</body>
</html>
