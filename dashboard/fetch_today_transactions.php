<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? date('Y-m-d');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch total bookings
    $stmtTotalBookings = $pdo->query("SELECT COUNT(*) FROM bookings");
    $totalBookings = $stmtTotalBookings->fetchColumn();

    // Fetch total cars
    $stmtTotalCars = $pdo->query("SELECT COUNT(*) FROM car");
    $totalCars = $stmtTotalCars->fetchColumn();

    // Fetch total accounts
    $stmtTotalAccounts = $pdo->query("SELECT COUNT(*) FROM users");
    $totalAccounts = $stmtTotalAccounts->fetchColumn();

    // Fetch bookings for the given date
    $sql = "SELECT 
                b.id AS booking_id,
                CONCAT(u.firstname, ' ', u.lastname) AS customer_name,
                c.model AS car_model,
                DATE_FORMAT(b.booking_date, '%Y-%m-%d') as booking_date,
                DATE_FORMAT(b.return_date, '%Y-%m-%d') as return_date,
                b.location,
                b.status,
                FORMAT(DATEDIFF(b.return_date, b.booking_date) * c.price, 2) AS total_cost
            FROM bookings b
            JOIN users u ON b.users_id = u.id
            JOIN car c ON b.car_id = c.id
            WHERE DATE(b.booking_date) = :date OR DATE(b.return_date) = :date
            ORDER BY b.booking_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'totalBookings' => $totalBookings,
        'totalCars' => $totalCars,
        'totalAccounts' => $totalAccounts,
        'bookings' => $bookings
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
