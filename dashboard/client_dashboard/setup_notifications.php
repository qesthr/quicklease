<?php
require_once '../../includes/session_handler.php';
require_once '../../db.php';

// Start client session
startClientSession();

// Check if user is logged in and is a client
if (!isClient()) {
    die("Unauthorized access");
}

try {
    // Drop existing notifications table if it exists
    $pdo->exec("DROP TABLE IF EXISTS notifications");
    
    // Create notifications table with updated structure
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        users_id INT NOT NULL,
        booking_id INT,
        message TEXT NOT NULL,
        notification_type VARCHAR(50) NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($sql);
    echo "Notifications table created successfully!<br>";

    // Get existing bookings for the logged-in user
    $bookings_query = "
        SELECT 
            b.id as booking_id,
            b.users_id,
            b.status,
            c.model as car_model,
            DATE_FORMAT(b.booking_date, '%M-%d-%Y') as formatted_booking_date,
            DATE_FORMAT(b.return_date, '%M-%d-%Y') as formatted_return_date
        FROM bookings b
        JOIN car c ON b.car_id = c.id
        WHERE b.users_id = ?
        ORDER BY b.booking_date DESC
    ";
    
    $bookings_stmt = $pdo->prepare($bookings_query);
    $bookings_stmt->execute([$_SESSION['user_id']]);
    $bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare notification insert statement
    $notification_stmt = $pdo->prepare("
        INSERT INTO notifications 
        (users_id, booking_id, message, notification_type, is_read) 
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($bookings as $booking) {
        $message = '';
        $type = '';
        
        // Create notification based on booking status
        switch($booking['status']) {
            case 'Pending':
                $message = "Your booking for {$booking['car_model']} from {$booking['formatted_booking_date']} to {$booking['formatted_return_date']} is pending admin approval.";
                $type = 'booking_pending';
                break;
            case 'Approved':
                $message = "Good news! Your booking for {$booking['car_model']} from {$booking['formatted_booking_date']} to {$booking['formatted_return_date']} has been approved by our admin.";
                $type = 'booking_approved';
                break;
            case 'Completed':
                $message = "Your booking for {$booking['car_model']} has been completed. Thank you for choosing QuickLease!";
                $type = 'booking_completed';
                break;
            case 'Cancelled':
                $message = "Your booking for {$booking['car_model']} has been cancelled.";
                $type = 'booking_cancelled';
                break;
            default:
                $message = "Status update for your {$booking['car_model']} booking: {$booking['status']}";
                $type = 'booking_status_update';
                break;
        }

        // Only insert if we have a valid message and type
        if (!empty($message) && !empty($type)) {
            // Insert notification
            $notification_stmt->execute([
                $booking['users_id'],
                $booking['booking_id'],
                $message,
                $type,
                false
            ]);
        }
    }
    
    echo "Booking notifications created successfully!<br>";
    echo "<a href='client_profile_userdetails.php'>Return to Profile</a>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?> 