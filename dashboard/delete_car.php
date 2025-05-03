<?php
require_once '../db.php';

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']); // Ensuring it's an integer
    $stmt = $conn->prepare("UPDATE cars SET status = 'Deleted' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: cars.php");
        exit();
    } else {
        echo "Error updating car status: " . $stmt->error;
    }
}
?>