<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/session_handler.php';

try {
    startClientSession();
    $user_id = $_SESSION['user_id'] ?? 4; // Use session ID or fallback to 4
    
    echo "<h2>Testing Invoice Query</h2>";
    echo "User ID: " . $user_id . "<br><br>";
    
    // Test the exact query
    $sql = "
        SELECT 
            b.*,
            c.model,
            c.brand,
            c.image,
            c.price,
            c.plate_no
        FROM bookings b
        LEFT JOIN car c ON b.car_id = c.id
        WHERE b.users_id = ?
        ORDER BY b.booking_date DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Query Results:</h3>";
    echo "<pre>";
    print_r($results);
    echo "</pre>";
    
    if (empty($results)) {
        echo "<h3>Testing Individual Tables:</h3>";
        
        // Check bookings table
        echo "<h4>Bookings for user_id = {$user_id}:</h4>";
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE users_id = ?");
        $stmt->execute([$user_id]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($bookings);
        
        // Check car table
        echo "<h4>Sample car data:</h4>";
        $stmt = $pdo->query("SELECT * FROM car LIMIT 1");
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        print_r($car);
    }
    
} catch (Exception $e) {
    echo "<h3>Error:</h3>";
    echo $e->getMessage();
    echo "<br><br>Code: " . $e->getCode();
}
?> 