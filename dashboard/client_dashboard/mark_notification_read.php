<?php
require_once '../../includes/session_handler.php';
require_once '../../db.php';

// Start client session
startClientSession();

// Check if user is logged in and is a client
if (!isClient()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get the notification ID from POST data
$data = json_decode(file_get_contents('php://input'), true);
$notification_id = $data['notification_id'] ?? null;

if (!$notification_id) {
    echo json_encode(['success' => false, 'error' => 'Notification ID is required']);
    exit();
}

try {
    // Update the notification
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND users_id = ?");
    $result = $stmt->execute([$notification_id, $_SESSION['user_id']]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update notification']);
    }
} catch (PDOException $e) {
    error_log("Error marking notification as read: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?> 