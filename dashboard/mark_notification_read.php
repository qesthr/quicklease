<?php
require_once '../db.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['user_id'] ?? null;
$notificationId = $_POST['notification_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

if (!$notificationId) {
    echo json_encode(['success' => false, 'error' => 'Notification ID not provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND users_id = ?
    ");
    $success = $stmt->execute([$notificationId, $userId]);
    
    echo json_encode(['success' => $success]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 