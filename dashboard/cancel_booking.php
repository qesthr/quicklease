<?php
require_once '../db.php';
session_start();

header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['booking_id'])) {
        throw new Exception('Booking ID is required');
    }

    $booking_id = intval($_POST['booking_id']);

    // Update the booking status to Cancelled
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
    $result = $stmt->execute([$booking_id]);

    if ($result) {
        // Add notification for the user
        $stmt = $pdo->prepare("
            SELECT b.users_id, u.firstname 
            FROM bookings b 
            JOIN users u ON b.users_id = u.id 
            WHERE b.id = ?
        ");
        $stmt->execute([$booking_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $message = "Your booking #" . $booking_id . " has been cancelled.";
            $stmt = $pdo->prepare("INSERT INTO notifications (users_id, message) VALUES (?, ?)");
            $stmt->execute([$user['users_id'], $message]);
        }

        echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
    } else {
        throw new Exception('Failed to cancel booking');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 