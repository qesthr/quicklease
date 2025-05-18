<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get booking distribution by status
    $sql = "SELECT 
                CASE 
                    WHEN status = 'pending' THEN 'Pending Bookings'
                    WHEN status = 'confirmed' THEN 'Confirmed Bookings'
                    WHEN status = 'completed' THEN 'Completed Bookings'
                    WHEN status = 'cancelled' THEN 'Cancelled Bookings'
                    ELSE 'Other'
                END as booking_status,
                COUNT(*) as count
            FROM bookings
            GROUP BY 
                CASE 
                    WHEN status = 'pending' THEN 'Pending Bookings'
                    WHEN status = 'confirmed' THEN 'Confirmed Bookings'
                    WHEN status = 'completed' THEN 'Completed Bookings'
                    WHEN status = 'cancelled' THEN 'Cancelled Bookings'
                    ELSE 'Other'
                END
            ORDER BY count DESC";

    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $values = [];
    
    foreach ($results as $row) {
        $labels[] = $row['booking_status'];
        $values[] = (int)$row['count'];
    }

    echo json_encode([
        'labels' => $labels,
        'values' => $values
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?> 