<?php
require_once '../db.php';

// Handle Add Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $stmt = $pdo->prepare("INSERT INTO booking_details (customer_name, car_model, booking_date, return_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['customer_name'],
        $_POST['car_model'],
        $_POST['booking_date'],
        $_POST['return_date'],
        $_POST['status']
    ]);
    header("Location: bookings.php");
    exit;
}

// Handle Edit Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $stmt = $pdo->prepare("UPDATE booking_details SET customer_name=?, car_model=?, booking_date=?, return_date=?, status=? WHERE id=?");
    $stmt->execute([
        $_POST['customer_name'],
        $_POST['car_model'],
        $_POST['booking_date'],
        $_POST['return_date'],
        $_POST['status'],
        $_POST['id']
    ]);
    header("Location: bookings.php");
    exit;
}

// Handle Cancel Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $stmt = $pdo->prepare("UPDATE booking_details SET status='Cancelled' WHERE id=?");
    $stmt->execute([$_POST['id']]);
    header("Location: bookings.php");
    exit;
}

// Fetch bookings
$stmt = $pdo->query("SELECT * FROM booking_details ORDER BY id DESC");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bookings | QuickLease Admin</title>
  <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    body { margin: 0; font-family: Arial, sans-serif; display: flex; }
    .sidebar { width: 220px; background-color: #1d1de2; color: white; padding: 20px 0; height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; align-items: center; }
    .sidebar .logo img { width: 80px; margin-bottom: 40px; }
    .nav-btn { width: 90%; margin: 10px 0; padding: 10px; border: none; background-color: transparent; color: white; text-align: left; font-weight: bold; cursor: pointer; }
    .nav-btn.active, .nav-btn:hover { background-color: #ffb800; color: black; border-radius: 8px; }
    .logout-btn { margin-top: auto; margin-bottom: 20px; padding: 10px 20px; border: none; background-color: orange; border-radius: 20px; color: white; cursor: pointer; }
    .main { margin-left: 240px; padding: 30px; width: calc(100% - 240px); }
    header { display: flex; justify-content: space-between; align-items: center; }
    .add-booking { background-color: #ffa500; padding: 8px 16px; border: none; border-radius: 20px; cursor: pointer; font-weight: bold; color: #fff; }
    .table-container { overflow-x: auto; margin-top: 30px; }
    table { width: 100%; min-width: 800px; border-collapse: collapse; }
    th, td { padding: 12px; text-align: center; }
    th { background-color: #f7a200; }
    td { background-color: #e8edff; }
    .actions form { display: inline-block; }
    .actions button { margin: 0 2px; padding: 6px 12px; border: none; border-radius: 5px; font-size: 12px; cursor: pointer; }
    .view-btn { background-color: #0014ff; color: white; }
    .edit-btn { background-color: #ffc107; color: black; }
    .cancel-btn { background-color: #f44336; color: white; }

    /* Modal styling */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 10px; width: 400px; }
    .modal h2 { margin-top: 0; }
    .modal label { display: block; margin-bottom: 10px; }
    .modal input, .modal select { width: 100%; padding: 8px; margin-bottom: 10px; }
    .modal button { padding: 8px 16px; margin-top: 10px; }
    .close { float: right; cursor: pointer; font-weight: bold; color: red; }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="/quicklease/images/logo.png" alt="QuickLease Logo"></div>
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
    <button class="add-booking" onclick="openModal('addModal')">+ Add Bookings</button>
  </header>

  <div class="table-container">
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
              <button class="view-btn" onclick="openViewModal(<?= htmlspecialchars(json_encode($booking)) ?>)">View</button>
              <button class="edit-btn" onclick="openEditModal(<?= htmlspecialchars(json_encode($booking)) ?>)">Edit</button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?= $booking['id'] ?>">
                <input type="hidden" name="action" value="cancel">
                <button type="submit" class="cancel-btn" onclick="return confirm('Cancel this booking?')">Cancel</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD Modal -->
<div class="modal" id="addModal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('addModal')">×</span>
    <h2>Add Booking</h2>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <label>Customer Name: <input type="text" name="customer_name" required></label>
      <label>Car Model: <input type="text" name="car_model" required></label>
      <label>Booking Date: <input type="date" name="booking_date" required></label>
      <label>Return Date: <input type="date" name="return_date" required></label>
      <label>Status:
        <select name="status">
          <option value="Active">Active</option>
          <option value="Completed">Completed</option>
        </select>
      </label>
      <button type="submit">Save</button>
    </form>
  </div>
</div>

<!-- EDIT Modal -->
<div class="modal" id="editModal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('editModal')">×</span>
    <h2>Edit Booking</h2>
    <form method="POST" id="editForm">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <label>Customer Name: <input type="text" name="customer_name" id="edit-customer" required></label>
      <label>Car Model: <input type="text" name="car_model" id="edit-car" required></label>
      <label>Booking Date: <input type="date" name="booking_date" id="edit-date" required></label>
      <label>Return Date: <input type="date" name="return_date" id="edit-return" required></label>
      <label>Status:
        <select name="status" id="edit-status">
          <option value="Active">Active</option>
          <option value="Completed">Completed</option>
          <option value="Cancelled">Cancelled</option>
        </select>
      </label>
      <button type="submit">Update</button>
    </form>
  </div>
</div>

<!-- VIEW Modal -->
<div class="modal" id="viewModal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('viewModal')">×</span>
    <h2>Booking Details</h2>
    <p><strong>ID:</strong> <span id="view-id"></span></p>
    <p><strong>Customer:</strong> <span id="view-customer"></span></p>
    <p><strong>Car Model:</strong> <span id="view-car"></span></p>
    <p><strong>Booking Date:</strong> <span id="view-date"></span></p>
    <p><strong>Return Date:</strong> <span id="view-return"></span></p>
    <p><strong>Status:</strong> <span id="view-status"></span></p>
  </div>
</div>

<script>
function openModal(id) {
  document.getElementById(id).style.display = 'block';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}
function openEditModal(data) {
  document.getElementById('edit-id').value = data.id;
  document.getElementById('edit-customer').value = data.customer_name;
  document.getElementById('edit-car').value = data.car_model;
  document.getElementById('edit-date').value = data.booking_date;
  document.getElementById('edit-return').value = data.return_date;
  document.getElementById('edit-status').value = data.status;
  openModal('editModal');
}
function openViewModal(data) {
  document.getElementById('view-id').innerText = data.id;
  document.getElementById('view-customer').innerText = data.customer_name;
  document.getElementById('view-car').innerText = data.car_model;
  document.getElementById('view-date').innerText = data.booking_date;
  document.getElementById('view-return').innerText = data.return_date;
  document.getElementById('view-status').innerText = data.status;
  openModal('viewModal');
}
</script>

</body>
</html>
