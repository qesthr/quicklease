<?php
require_once __DIR__ . '/../../db.php';

try {
    $user_id = 4; // raydendelfin's ID
    
    echo "<h3>Checking Database Connection:</h3>";
    if ($pdo) {
        echo "Database connection successful<br><br>";
    }
    
    echo "<h3>Checking Bookings Table Structure:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Available tables: " . implode(", ", $tables) . "<br><br>";
    
    echo "<h3>Checking Bookings for User ID 4:</h3>";
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE users_id = ?");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($bookings)) {
        echo "No bookings found for user ID 4<br><br>";
        
        echo "<h3>Sample Booking Data:</h3>";
        $all_bookings = $pdo->query("SELECT * FROM bookings LIMIT 1")->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample booking: <pre>" . print_r($all_bookings, true) . "</pre>";
    } else {
        echo "Found " . count($bookings) . " bookings:<br>";
        echo "<pre>" . print_r($bookings, true) . "</pre>";
    }
    
    echo "<h3>Checking Car Table:</h3>";
    $cars = $pdo->query("SELECT * FROM car LIMIT 1")->fetchAll(PDO::FETCH_ASSOC);
    echo "Sample car data: <pre>" . print_r($cars, true) . "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 