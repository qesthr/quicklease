<?php
require_once '../db.php';

header('Content-Type: application/json');

$today = date('Y-m-d');

// Fetch today's bookings with required fields and total cost
$stmt = $pdo->prepare("
    SELECT 
        b.id AS booking_id,
        u.firstname AS customer_name,
        c.model AS car_model,
        b.booking_date,
        b.return_date,
        b.status,
        (DATEDIFF(b.return_date, b.booking_date) * c.price) AS total_cost
    FROM bookings b
    JOIN users u ON b.users_id = u.id
    JOIN car c ON b.car_id = c.id
    WHERE DATE(b.booking_date) = ?
");
$stmt->execute([$today]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch today's payments (assuming a payments table exists)
$stmt = $pdo->prepare("SELECT payment_date AS time, 'Payment' AS type, CONCAT('Payment ID: ', id, ', Amount: ₱', amount) AS details FROM payments WHERE DATE(payment_date) = ?");
$stmt->execute([$today]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch today's user account activities (assuming a user_activities table exists)
$stmt = $pdo->prepare("SELECT activity_time AS time, 'User Activity' AS type, activity_description AS details FROM user_activities WHERE DATE(activity_time) = ?");
$stmt->execute([$today]);
$user_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch today's car activities (assuming a car_activities table exists)
$stmt = $pdo->prepare("SELECT activity_time AS time, 'Car Activity' AS type, activity_description AS details FROM car_activities WHERE DATE(activity_time) = ?");
$stmt->execute([$today]);
$car_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Merge all transactions
$transactions = array_merge($bookings, $payments, $user_activities, $car_activities);

// Sort by time descending
usort($transactions, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});

echo json_encode($transactions);
?>
