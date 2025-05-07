<?php
require_once '../db.php'; // Include database connection
session_start(); // Start session to get the logged-in customer's ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the logged-in customer's ID from the session
    $customer_id = $_SESSION['customer_id'] ?? null;

    if (!$customer_id) {
        echo 'error: Customer not logged in.';
        exit;
    }

    // Validate and sanitize input
    $location = $_POST['location'] ?? null;
    $car_model = $_POST['car_model'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;
    $return_date = $_POST['return_date'] ?? null;
    $preferences = $_POST['preferences'] ?? null;

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

        $stmt = $pdo->prepare("INSERT INTO bookings 
        (users_id, customer_id, car_id, location, booking_date, return_date, preferences, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $stmt->execute([
        $users_id,
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
        echo 'error: ' . $e->getMessage();
    }
} else {
    echo 'error: Invalid request method.';
}
?>