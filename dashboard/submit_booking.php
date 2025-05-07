<?php
require_once '../db.php'; // Ensure this initializes $pdo
session_start(); // Start the session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if customer is logged in
    if (!isset($_SESSION['customer_id'])) {
        echo 'error: Customer not logged in.';
        exit;
    }

    $customer_id = $_SESSION['customer_id'];

    // Retrieve and sanitize input
    $location = trim($_POST['location'] ?? '');
    $car_model = trim($_POST['car_model'] ?? '');
    $booking_date = trim($_POST['booking_date'] ?? '');
    $return_date = trim($_POST['return_date'] ?? '');
    $preferences = trim($_POST['preferences'] ?? '');

    // Validate required fields
    if (empty($location) || empty($car_model) || empty($booking_date) || empty($return_date)) {
        echo 'error: Missing required fields.';
        exit;
    }

    try {
        // Fetch car ID based on the car model
        $stmt = $pdo->prepare("SELECT id FROM car WHERE model = ?");
        $stmt->execute([$car_model]);
        $car_id = $stmt->fetchColumn();

        if (!$car_id) {
            echo 'error: Car not found.';
            exit;
        }

        // Insert booking into the database
        $stmt = $pdo->prepare("INSERT INTO bookings 
            (customer_id, car_id, location, booking_date, return_date, preferences, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->execute([
            $customer_id,
            $car_id,
            $location,
            $booking_date,
            $return_date,
            $preferences,
            'Active'
        ]);

        echo 'success';
    } catch (PDOException $e) {
        // Log the error message in a real application
        echo 'error: ' . $e->getMessage();
    }
} else {
    echo 'error: Invalid request method.';
}
?>
