<?php
require_once '../loginpage/includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: bookings.php");
exit;
?>
