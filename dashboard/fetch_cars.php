<?php
require_once '../../db.php';

header('Content-Type: application/json');

$sql = "SELECT id, model, plate_no, price, status, image, seats, transmission, mileage, features FROM car WHERE status = 'Available'";
$result = $conn->query($sql);

$cars = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $cars]);
} else {
    echo json_encode(['success' => false, 'error' => 'No available cars found.']);
}
?>