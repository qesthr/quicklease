<?php
// Correct path to your db.php file
require_once '../db.php'; // Ensure this initializes $pdo

// Handle car deletion
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']); // Sanitize input
    $stmt = $pdo->prepare("DELETE FROM car WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: cars.php");
        exit();
    } else {
        echo "Error deleting car.";
    }
}

// Fetch all cars
$stmt = $pdo->query("SELECT * FROM car");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cars Catalogue</title>
    <style>
        body { margin: 0; font-family: 'Arial'; background-color: #f1f0e8; }
        .sidebar { width: 250px; background: #1e1ebf; color: white; height: 100vh; position: fixed; }
        .sidebar a { display: block; padding: 20px; color: white; text-decoration: none; }
        .sidebar a.active, .sidebar a:hover { background: #ffb400; color: black; }
        .top-bar { margin-left: 250px; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .content { margin-left: 250px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #e7ebfc; }
        th, td { padding: 15px; text-align: center; border-bottom: 1px solid #ccc; }
        th { background-color: #2323c1; color: white; }
        .btn { padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-edit { background: #ffc107; }
        .btn-delete { background: #ff6b6b; color: white; }
        .btn-add { background: #ffb400; color: black; margin-top: 10px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 style="text-align:center; padding: 20px;">Quick<span style="color: orange;">Lease</span></h2>
    <a href="reports.php">Reports</a>
    <a href="accounts.php">Accounts</a>
    <a class="active" href="cars.php">Cars</a>
    <a href="bookings.php">Bookings</a>
    <button class="logout-btn" onclick="window.location.href='../loginpage/login.php'">Logout</button>
</div>

<div class="top-bar">
    <h1>Cars Catalogue</h1>
    <div>
        <button class="btn btn-add" onclick="window.location.href='add_car.php'">+ Add Car</button>
        <span style="margin-left: 20px;"><img src="bell_icon.png" alt="Notifications" width="24"></span>
        <span><img src="admin_icon.png" alt="Admin" width="32" style="border-radius: 50%; margin-left: 15px;"></span>
    </div>
</div>

<div class="content">
    <table>
        <thead>
            <tr>
                <th>Car ID</th>
                <th>Car Model</th>
                <th>Plate No.</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cars as $car): ?>
            <tr>
                <td><?= htmlspecialchars($car['id']) ?></td>
                <td><?= htmlspecialchars($car['model']) ?></td>
                <td><?= htmlspecialchars($car['plate_no']) ?></td>
                <td><?= htmlspecialchars($car['price']) ?>/Day</td>
                <td><?= htmlspecialchars($car['status']) ?></td>
                <td>
                    <a href="edit_car.php?id=<?= htmlspecialchars($car['id']) ?>" class="btn btn-edit">Edit</a>
                    <a href="cars.php?delete_id=<?= htmlspecialchars($car['id']) ?>" class="btn btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>