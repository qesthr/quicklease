<?php
require_once '../db.php';
session_start();

header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['booking_id']) || !isset($_POST['location']) || 
        !isset($_POST['booking_date']) || !isset($_POST['return_date'])) {
        throw new Exception('Missing required fields');
    }

    $booking_id = intval($_POST['booking_id']);
    $location = trim($_POST['location']);
    $booking_date = trim($_POST['booking_date']);
    $return_date = trim($_POST['return_date']);

    // Validate dates
    if (strtotime($return_date) <= strtotime($booking_date)) {
        throw new Exception('Return date must be after booking date');
    }

    // Update the booking
    $stmt = $pdo->prepare("UPDATE bookings SET location = ?, booking_date = ?, return_date = ? WHERE id = ?");
    $result = $stmt->execute([$location, $booking_date, $return_date, $booking_id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
    } else {
        throw new Exception('Failed to update booking');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 