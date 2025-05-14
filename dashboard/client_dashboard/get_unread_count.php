<?php
require_once(__DIR__ . '/../includes/NotificationHandler.php');
require_once(__DIR__ . '/../db.php');

if (!isset($_SESSION)) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$notificationHandler = new NotificationHandler($pdo, $_SESSION['user_id'], 'client');
$count = $notificationHandler->getUnreadCount();

echo json_encode(['count' => $count]); 