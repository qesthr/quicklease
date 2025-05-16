<?php
require_once '../db.php';

// Set the correct content type for JSON response
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for user_id in session
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode([
        'success' => false,
        'error' => 'User not authenticated'
    ]);
    exit;
}

try {
    // Verify database connection
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }

    // Get unread notifications count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE users_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    $unreadCount = $stmt->fetchColumn();

    // Fetch notifications
    $stmt = $pdo->prepare("
        SELECT 
            id,
            message,
            is_read,
            created_at,
            DATE_FORMAT(created_at, '%M %d, %Y %H:%i') as formatted_date
        FROM notifications 
        WHERE users_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format notifications for display
    $formattedNotifications = array_map(function($notif) {
        return [
            'id' => $notif['id'],
            'message' => $notif['message'],
            'is_read' => (bool)$notif['is_read'],
            'created_at' => $notif['formatted_date'],
            'type' => 'general'  // Default type since it's not in the table
        ];
    }, $notifications);

    echo json_encode([
        'success' => true,
        'data' => [
            'unreadCount' => $unreadCount,
            'notifications' => $formattedNotifications
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database Error in fetch_notification.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database Error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Error in fetch_notification.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 