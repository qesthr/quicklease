<!-- book_car.php -->
<?php
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
    $stmt = $pdo->prepare("INSERT INTO bookings 
        (users_id, customer_id, car_id, location, booking_date, return_date, preferences, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
        
    $stmt->execute([
        $_POST['user_id'],
        $_POST['user_id'],  // Set customer_id same as users_id for now
        $_POST['car_id'],
        $_POST['location'],
        $booking_datetime,
        $return_datetime,
        $_POST['preferences'] ?? ''
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
