<?php
require_once '../../db.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_POST['booking_id'])) {
        throw new Exception('Booking ID is required');
    }

    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        throw new Exception('User not authenticated');
    }

    // Verify the booking belongs to the user
    $check_stmt = $pdo->prepare("SELECT id, status FROM bookings WHERE id = ? AND users_id = ?");
    $check_stmt->execute([$booking_id, $user_id]);
    $booking = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception('Unauthorized access');
    }

    if ($booking['status'] === 'Cancelled') {
        throw new Exception('Booking is already cancelled');
    }

    if ($booking['status'] === 'Completed') {
        throw new Exception('Cannot cancel a completed booking');
    }

    // Update the booking status to Cancelled
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ? AND users_id = ?");
    $result = $stmt->execute([$booking_id, $user_id]);

    if ($result) {
        // Update car status to Available
        $stmt = $pdo->prepare("
            UPDATE car c
            JOIN bookings b ON b.car_id = c.id
            SET c.status = 'Available'
            WHERE b.id = ?
        ");
        $stmt->execute([$booking_id]);

        echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
    } else {
        throw new Exception('Failed to cancel booking');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 