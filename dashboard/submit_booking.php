<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO booking_details (customer_name, location, car_model, booking_date, return_date, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['customer_name'] ?? 'Anonymous',
        $_POST['location'],
        $_POST['car_model'],
        $_POST['booking_date'],
        $_POST['return_date'],
        'Active' // default status
    ]);
    echo 'success';
}
?>
