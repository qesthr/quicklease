<?php
require_once __DIR__ . '/../../db.php';

try {
    // Test database connection
    $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    echo "Database connection successful\n";

    // Test bookings query
    $stmt = $pdo->query("SELECT * FROM bookings LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Bookings query successful. Sample data:\n";
    print_r($result);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 