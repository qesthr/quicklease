<?php
require_once '../db.php';
session_start();

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('You must be logged in to make a booking');
    }

    // Validate input
    if (!isset($_POST['car_id']) || !isset($_POST['location']) || 
        !isset($_POST['booking_date']) || !isset($_POST['return_date']) ||
        !isset($_POST['pickup_hour']) || !isset($_POST['return_hour'])) {
        throw new Exception('Missing required fields');
    }

    $car_id = intval($_POST['car_id']);
    $location = trim($_POST['location']);
    $booking_date = trim($_POST['booking_date']) . ' ' . trim($_POST['pickup_hour']) . ':00:00';
    $return_date = trim($_POST['return_date']) . ' ' . trim($_POST['return_hour']) . ':00:00';
    $status = 'Pending';
    $user_id = $_SESSION['user_id'];

    // Validate user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Invalid user account');
    }

    // Validate car exists
    $stmt = $pdo->prepare("SELECT id FROM car WHERE id = ?");
    $stmt->execute([$car_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Invalid car selection');
    }

    // Validate dates
    $pickup_timestamp = strtotime($booking_date);
    $return_timestamp = strtotime($return_date);
    $now = time();

    if ($pickup_timestamp === false || $return_timestamp === false) {
        throw new Exception('Invalid date format');
    }

    if ($pickup_timestamp < $now) {
        throw new Exception('Pickup date cannot be in the past');
    }

    if ($return_timestamp <= $pickup_timestamp) {
        throw new Exception('Return date must be after pickup date');
    }

    // Check if car is available
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as booking_count 
        FROM bookings 
        WHERE car_id = ? 
        AND status IN ('Active', 'Pending')
        AND (
            (booking_date BETWEEN ? AND ?) OR
            (return_date BETWEEN ? AND ?) OR
            (booking_date <= ? AND return_date >= ?)
        )
    ");
    $stmt->execute([$car_id, $booking_date, $return_date, $booking_date, $return_date, $booking_date, $return_date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['booking_count'] > 0) {
        throw new Exception('Car is not available for the selected dates');
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // Insert the booking
        $stmt = $pdo->prepare("
            INSERT INTO bookings (users_id, car_id, location, booking_date, return_date, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([$user_id, $car_id, $location, $booking_date, $return_date, $status]);

        if ($result) {
            // Add notification
            $booking_id = $pdo->lastInsertId();
            $message = "New booking #" . $booking_id . " has been created and is pending approval.";
            $stmt = $pdo->prepare("INSERT INTO notifications (users_id, message) VALUES (?, ?)");
            $stmt->execute([$user_id, $message]);

            // Commit transaction
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Booking submitted successfully']);
        } else {
            throw new Exception('Failed to create booking');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 