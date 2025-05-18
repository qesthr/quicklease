<?php
require_once realpath(__DIR__ . '/../../db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];
    $location = $_POST['location'];
    $booking_date = $_POST['booking_date'];
    $return_date = $_POST['return_date'];
    $preferences = $_POST['preferences'];
    $total_price = $_POST['total_price'];

    // Simulate logged-in user/customer
    $users_id = $_SESSION['user_id'] ?? 1; // replace 1 with actual fallback logic if needed
    $customer_id = $_SESSION['customer_id'] ?? 1;

    try {
        $pdo->beginTransaction();

        // Insert into bookings table
        $stmt = $pdo->prepare("INSERT INTO bookings (
            users_id, customer_id, car_id, location, booking_date, return_date, preferences, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', NOW())");

        $stmt->execute([
            $users_id,
            $customer_id,
            $car_id,
            $location,
            $booking_date,
            $return_date,
            $preferences
        ]);

        // Update car status to 'booked'
        $update = $pdo->prepare("UPDATE car SET status = 'booked' WHERE id = ?");
        $update->execute([$car_id]);

        $pdo->commit();
        echo 'success';
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo 'Database Error: ' . $e->getMessage();
    }
} else {
    echo 'Invalid request';
}
?>
