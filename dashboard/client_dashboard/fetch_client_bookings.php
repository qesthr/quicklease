<?php
require_once realpath(__DIR__ . '/../../db.php');
session_start();

header('Content-Type: application/json');

$customer_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in or customer ID missing']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT 
        b.id, 
        c.model AS car_model, 
        b.location,
        b.booking_date, 
        b.return_date, 
        b.status,
        b.preferences,
        b.total_price
    FROM bookings b
    INNER JOIN car c ON b.car_id = c.id
    WHERE b.customer_id = ?
    ORDER BY b.booking_date DESC");
    $stmt->execute([$customer_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $bookings]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
