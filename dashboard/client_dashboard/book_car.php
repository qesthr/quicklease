
<!-- book_car.php -->
<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once realpath(__DIR__ . '/../../db.php');

header('Content-Type: application/json');

function log_error($message) {
    file_put_contents(__DIR__ . '/error_log.txt', date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

try {
    // Validate required fields
    $required = ['user_id', 'car_id', 'location', 'booking_date', 'return_date'];
    foreach($required as $field) {
        if(empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Combine date and time
    $booking_datetime = $_POST['booking_date'] . ' ' . $_POST['booking_time'];
    $return_datetime = $_POST['return_date'] . ' ' . $_POST['return_time'];

    // Start transaction
    $pdo->beginTransaction();

    // Insert booking
    $stmt = $pdo->prepare("
    INSERT INTO bookings (users_id, car_id, location, booking_date, return_date, phone, status)
    VALUES (:users_id, :car_id, :location, :booking_date, :return_date, :phone, :status)
");
    $stmt->bindParam(':users_id', $_POST['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':car_id', $_POST['car_id'], PDO::PARAM_INT);
    $stmt->bindParam(':location', $_POST['location'], PDO::PARAM_STR);
    $stmt->bindParam(':booking_date', $booking_datetime, PDO::PARAM_STR);
    $stmt->bindParam(':return_date', $return_datetime, PDO::PARAM_STR);
    $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        
$stmt->execute([
    ':users_id' => $_POST['user_id'],
    ':car_id' => $_POST['car_id'],
    ':location' => $_POST['location'],
    ':booking_date' => $booking_datetime,
    ':return_date' => $return_datetime,
    ':phone' => $_POST['phone'] ?? null,
    ':status' => 'Pending'
]);

    // Update car status
    $pdo->prepare("UPDATE car SET status = 'Pending' WHERE id = ?")
        ->execute([$_POST['car_id']]);

    $pdo->commit();
    
    echo json_encode(['success' => true]);
    
} catch(Exception $e) {
    $pdo->rollBack();
    log_error($e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
