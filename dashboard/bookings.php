<?php
require_once '../db.php';

// Handle Add Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $stmt = $pdo->prepare("INSERT INTO bookings (customer_name, car_id, booking_date, return_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['customer_name'],
        $_POST['car_id'],
        $_POST['booking_date'],
        $_POST['return_date'],
        $_POST['status']
    ]);
    header("Location: bookings.php");
    exit;
}

// Handle Edit Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $stmt = $pdo->prepare("UPDATE bookings SET customer_name=?, car_id=?, booking_date=?, return_date=?, status=? WHERE id=?");
    $stmt->execute([
        $_POST['customer_name'],
        $_POST['car_id'],
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
    $stmt = $pdo->prepare("UPDATE bookings SET status='Cancelled' WHERE id=?");
    $stmt->execute([$_POST['id']]);
    header("Location: bookings.php");
    exit;
}

// Fetch bookings
<<<<<<< HEAD
$stmt = $pdo->query("
    SELECT bookings.*, 
           car.model AS car_model, 
           customer.name AS customer_name 
    FROM bookings 
    JOIN car ON bookings.car_id = car.id 
    JOIN customer ON bookings.customer_id = customer.id 
    ORDER BY bookings.id DESC
");
=======
$stmt = $pdo->query("SELECT bookings.*, car.model AS car_model FROM bookings JOIN car ON bookings.car_id = car.id ORDER BY bookings.id DESC");
>>>>>>> 423ca1d0 (bookings look goods IthinkSOW)
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookings | QuickLease Admin</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/bookings.css">

</head>
<body>

  <div class="booking-content">
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/topbar.php'; ?>

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
        <label>Car:</label>
        <select name="car_id" required>
          <?php
          $cars = $pdo->query("SELECT id, model FROM car")->fetchAll(PDO::FETCH_ASSOC);
          foreach ($cars as $car) {
              echo "<option value='{$car['id']}'>{$car['model']}</option>";
          }
          ?>
        </select>
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
        <label>Car:</label>
        <select name="car_id" id="edit-car-id" required>
          <?php
          $cars = $pdo->query("SELECT id, model FROM car")->fetchAll(PDO::FETCH_ASSOC);
          foreach ($cars as $car) {
              echo "<option value='{$car['id']}'>{$car['model']}</option>";
          }
          ?>
        </select>
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
    document.getElementById('edit-car-id').value = data.car_id;
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

<script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>

</body>
</html>
