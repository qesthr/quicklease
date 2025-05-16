<?php
require_once 'db.php';

try {
    // Create notifications table
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50) DEFAULT 'general',
        is_read TINYINT(1) DEFAULT 0,
        booking_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
    )";

    $pdo->exec($sql);
    echo "Notifications table created successfully!";

} catch(PDOException $e) {
    echo "Error creating notifications table: " . $e->getMessage();
}
?> 