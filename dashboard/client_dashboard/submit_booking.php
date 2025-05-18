<?php
require_once '../../includes/session_handler.php';
require_once '../../db.php';

// Start client session and check access
startClientSession();
requireClient();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];
    $location = $_POST['location'];
    $booking_date = $_POST['booking_date'];
    $return_date = $_POST['return_date'];
    $preferences = $_POST['preferences'];
    $total_price = $_POST['total_price'];

    // Get user ID from session
    $users_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Insert into bookings table
        $stmt = $pdo->prepare("INSERT INTO bookings (
            users_id, car_id, location, booking_date, return_date, preferences, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())");

        $stmt->execute([
            $users_id,
            $car_id,
            $location,
            $booking_date,
            $return_date,
            $preferences
        ]);

        // Update car status to 'Pending'
        $update = $pdo->prepare("UPDATE car SET status = 'Pending' WHERE id = ?");
        $update->execute([$car_id]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Booking submitted successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
