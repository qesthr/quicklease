<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Get the last 6 months of data
    $query = "
        SELECT 
            DATE_FORMAT(booking_date, '%Y-%m') as month,
            COUNT(*) as total_bookings,
            SUM(total_cost) as total_revenue
        FROM bookings
        WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
        ORDER BY month ASC
    ";
    
    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $bookings = [];
    $revenue = [];
    
    // Format the data for Chart.js
    foreach ($results as $row) {
        // Convert YYYY-MM to Month YYYY format
        $date = DateTime::createFromFormat('Y-m', $row['month']);
        $labels[] = $date->format('M Y');
        $bookings[] = intval($row['total_bookings']);
        $revenue[] = floatval($row['total_revenue']);
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'labels' => $labels,
        'bookings' => $bookings,
        'revenue' => $revenue
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 