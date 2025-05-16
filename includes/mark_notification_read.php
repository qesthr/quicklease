<?php
require_once '../db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
$success = $stmt->execute([$_POST['notification_id'], $_SESSION['user_id']]);

echo json_encode(['success' => $success]); 