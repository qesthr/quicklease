<?php
require_once '../loginpage/includes/db.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: bookings.php"); // Redirect back to the list
    exit();
}   
?>
