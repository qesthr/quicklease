<?php
require_once '../loginpage/includes/db.php';

// Fetch bookings
$stmt = $pdo->query("SELECT * FROM bookings ORDER BY id DESC");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bookings | QuickLease Admin</title>
  <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        padding: 12px;
        text-align: center;
    }
    th {
        background-color: #f7a200;
    }
    td {
        background-color: #e8edff;
    }
    .actions button {
        margin: 0 2px;
        padding: 5px 10px;
        border: none;
        cursor: pointer;
        border-radius: 4px;
        font-size: 12px;
    }
    .view-btn { background-color: #0014ff; color: white; }
    .edit-btn { background-color: #ffc107; }
    .cancel-btn { background-color: #f44336; color: white; }
    .add-booking {
        float: right;
        background-color: #ffa500;
        padding: 8px 16px;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-weight: bold;
        color: #fff;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="/quicklease/images/logo.png" alt=""></div>
  <a href="reports.php"><button class="nav-btn">Reports</button></a>
  <a href="accounts.php"><button class="nav-btn">Accounts</button></a>
  <a href="cars.php"><button class="nav-btn">Cars</button></a>
  <a href="bookings.php"><button class="nav-btn active">Bookings</button></a>
  <button class="logout-btn" onclick="window.location.href='../loginpage/login.php'">Logout</button>
</div>

<div class="main">
  <header>
    <h1>Bookings</h1>
    <div class="user-info">
      <span class="notification">🔔</span>
      <span class="profile-pic">👤</span>
    </div>
    <button class="add-booking" onclick="window.location.href='add-booking.php'">+ Add Bookings</button>
  </header>

  <table>
    <thead>
      <tr>
        <th>Booking ID</th>
        <th>Customer Name</th>
        <th>Car Model</th>
        <th>Booking Date</th>
        <th>Return Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bookings as $booking): ?>
        <tr>
          <td><?= htmlspecialchars($booking['id']) ?></td>
          <td><?= htmlspecialchars($booking['customer_name']) ?></td>
          <td><?= htmlspecialchars($booking['car_model']) ?></td>
          <td><?= htmlspecialchars($booking['booking_date']) ?></td>
          <td><?= htmlspecialchars($booking['return_date']) ?></td>
          <td><?= htmlspecialchars($booking['status']) ?></td>
          <td class="actions">
            <form action="view-booking.php" method="GET" style="display:inline;">
              <input type="hidden" name="id" value="<?= $booking['id'] ?>">
              <button class="view-btn">View</button>
            </form>
            <form action="edit-booking.php" method="GET" style="display:inline;">
              <input type="hidden" name="id" value="<?= $booking['id'] ?>">
              <button class="edit-btn">Edit</button>
            </form>
            <form action="cancel-booking.php" method="POST" style="display:inline;">
              <input type="hidden" name="id" value="<?= $booking['id'] ?>">
              <button class="cancel-btn" onclick="return confirm('Cancel this booking?')">Cancel</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</body>
</html>
