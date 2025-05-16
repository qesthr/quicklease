<?php
require_once '../../db.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_POST['booking_id']) || !isset($_POST['location']) || !isset($_POST['booking_date']) || !isset($_POST['return_date'])) {
        throw new Exception('Missing required fields');
    }

    $booking_id = $_POST['booking_id'];
    $location = $_POST['location'];
    $booking_date = $_POST['booking_date'];
    $return_date = $_POST['return_date'];
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        throw new Exception('User not authenticated');
    }

    // Verify the booking belongs to the user
    $check_stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND users_id = ?");
    $check_stmt->execute([$booking_id, $user_id]);
    if (!$check_stmt->fetch()) {
        throw new Exception('Unauthorized access');
    }

    // Update the booking
    $stmt = $pdo->prepare("UPDATE bookings SET location = ?, booking_date = ?, return_date = ? WHERE id = ? AND users_id = ?");
    $result = $stmt->execute([$location, $booking_date, $return_date, $booking_id, $user_id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
    } else {
        throw new Exception('Failed to update booking');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 