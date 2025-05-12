<?php
include '../db.php';

header('Content-Type: application/json');

try {
    // Fetch count of all bookings to match Total Bookings count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'] ?? 0;

    echo json_encode(['count' => $count]);
} catch (Exception $e) {
    echo json_encode(['count' => 0, 'error' => $e->getMessage()]);
}
?>
