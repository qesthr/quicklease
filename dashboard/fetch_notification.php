<?php
require_once '../db.php'; // Include database connection

// Set the correct content type for JSON response
header('Content-Type: application/json');

// Start session to get the logged-in customer's ID
session_start();

// Debugging: Check if the session is working
if (!isset($_SESSION['customer_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Customer ID not found in session. Debug: ' . print_r($_SESSION, true)
    ]);
    exit;
}

try {
    // Get the logged-in customer's ID from the session
    $customer_id = $_SESSION['customer_id'];

    // Fetch notifications for the logged-in customer, sorted by most recent
    $stmt = $pdo->prepare("SELECT 
            message, 
            is_read, 
            created_at 
        FROM notifications 
        WHERE customer_id = ? 
        ORDER BY created_at DESC");
    $stmt->execute([$customer_id]);

    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the notifications as a JSON response
    echo json_encode([
        'success' => true,
        'data' => $notifications
    ]);
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>