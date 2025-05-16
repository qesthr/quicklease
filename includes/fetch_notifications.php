<?php
require_once '../db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

try {
    // Get unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unreadCount = $stmt->fetchColumn();

    // Get recent notifications
    $stmt = $pdo->prepare("
        SELECT n.*, b.car_id, c.model as car_model, 
               DATE_FORMAT(n.created_at, '%M %d, %Y %H:%i') as formatted_date
        FROM notifications n 
        LEFT JOIN bookings b ON n.booking_id = b.id 
        LEFT JOIN car c ON b.car_id = c.id 
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'unreadCount' => $unreadCount,
            'notifications' => $notifications
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching notifications'
    ]);
} 