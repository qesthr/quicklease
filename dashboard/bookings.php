<?php
require_once '../db.php';

// Handle Add Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Validate and sanitize input
    $users_id = isset($_POST['users_id']) ? trim($_POST['users_id']) : null;
    $car_id = isset($_POST['car_id']) ? intval($_POST['car_id']) : null;
    $booking_date = isset($_POST['booking_date']) ? trim($_POST['booking_date']) : null;
    $return_date = isset($_POST['return_date']) ? trim($_POST['return_date']) : null;
    $status = isset($_POST['status']) ? trim($_POST['status']) : null;

    // Check for missing fields
    if (empty($users_id) || empty($car_id) || empty($booking_date) || empty($return_date) || empty($status)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: bookings.php");
        exit;
    }

    // Validate dates
    if (!strtotime($booking_date) || !strtotime($return_date)) {
        $_SESSION['error'] = "Invalid booking or return date.";
        header("Location: bookings.php");
        exit;
    }

    // Ensure return date is after booking date
    if (strtotime($return_date) <= strtotime($booking_date)) {
        $_SESSION['error'] = "Return date must be after the booking date.";
        header("Location: bookings.php");
        exit;
    }

    try {
        // Insert booking into the database
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, car_id, booking_date, return_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $car_id, $booking_date, $return_date, $status]);

        // Redirect with success message
        $_SESSION['success'] = "Booking added successfully.";
        header("Location: bookings.php");
        exit;
    } catch (PDOException $e) {
        // Handle database errors
        $_SESSION['error'] = "Error adding booking: " . $e->getMessage();
        header("Location: bookings.php");
        exit;
    }
}

// Handle Edit Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $stmt = $pdo->prepare("UPDATE bookings SET users_id=?, car_id=?, booking_date=?, return_date=?, status=? WHERE id=?");
    $stmt->execute([
        $_POST['users_id'],
        $_POST['car_id'],
        $_POST['booking_date'],
        $_POST['return_date'],
        $_POST['status'],
        $_POST['id']
    ]);
   // Redirect with a success message
   $_SESSION['success'] = "Booking updated successfully.";
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

// Handle Approve Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve') {
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Approved' WHERE id = ?");
    $stmt->execute([$_POST['id']]);

    // Fetch customer ID and name
    $stmt = $pdo->prepare("SELECT cu.id, cu.users_id FROM bookings b INNER JOIN users cu ON b.users_id = cu.id WHERE b.id = ?");
    $stmt->execute([$_POST['id']]);
    $users = $stmt->fetch(PDO::FETCH_ASSOC);

    // Insert notification
    $message = "Dear {$users['users_id']}, your booking has been approved.";
    $stmt = $pdo->prepare("INSERT INTO notifications (users_id, message) VALUES (?, ?)");
    $stmt->execute([$users['id'], $message]);
}

// Handle Reject Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reject') {
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Rejected' WHERE id = ?");
    $stmt->execute([$_POST['id']]);

    // Fetch customer ID and name
    $stmt = $pdo->prepare("SELECT cu.id, cu.users_id FROM bookings b INNER JOIN users cu ON b.users_id = cu.id WHERE b.id = ?");
    $stmt->execute([$_POST['id']]);
    $users = $stmt->fetch(PDO::FETCH_ASSOC);

    // Insert notification
    $message = "Dear {$users['users_id']}, your booking has been rejected.";
    $stmt = $pdo->prepare("INSERT INTO notifications (users_id, message) VALUES (?, ?)");
    $stmt->execute([$users['id'], $message]);
}

// Fetch bookings
$stmt = $pdo->query("SELECT 
        b.id, 
        cu.firstname, 
        c.model AS car_model, 
        b.location,
        b.booking_date, 
        b.return_date, 
        b.status 
    FROM bookings b
    INNER JOIN users cu ON b.users_id = cu.id
    INNER JOIN car c ON b.car_id = c.id
    ORDER BY b.booking_date DESC");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// echo '<pre>';
// print_r($bookings);
// echo '</pre>';
// exit;
// ?>

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

    <div class="booking-header">
        <div class="add-booking-button-container">
            <button class="btn btn-add" onclick="openModal('addModal')">Add Booking</button>
        </div>
        <div class="search-container">
            <form method="GET" action="bookings.php">
                <input type="text" name="search" placeholder="Search bookings..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($search)): ?>
                    <a href="bookings.php" class="clear-search"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Car Model</th>
                    <th>Location</th>
                    <th>Booking Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8" class="no-results">No bookings found<?= !empty($search) ? ' matching your search' : '' ?>.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['id']) ?></td>
                            <td><?= htmlspecialchars($booking['firstname']) ?></td>
                            <td><?= htmlspecialchars($booking['car_model']) ?></td>
                            <td><?= htmlspecialchars($booking['location']) ?></td>
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
                <?php endif; ?>
            </tbody>
        </table>
    </div>
  </div>

  <!-- ADD Modal -->
  <div class="modal" id="addModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addModal')">×</span>
        <h2>Add Booking</h2>
        <form method="POST" id="addForm">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="add-users">Customer Name:</label>
                <select name="users_id" id="add-users" required>
                    <option value="">Select Customer</option>
                    <?php
                    $userss = $pdo->query("SELECT id, firstname FROM users")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($userss as $users) {
                        echo "<option value='{$users['id']}'>{$users['firstname']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="add-car">Car:</label>
                <select name="car_id" id="add-car" required>
                    <option value="">Select Car</option>
                    <?php
                    $cars = $pdo->query("SELECT id, model FROM car")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($cars as $car) {
                        echo "<option value='{$car['id']}'>{$car['model']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="add-location">Location:</label>
                <input type="text" name="location" id="add-location" required>
            </div>
            
            <div class="form-group">
                <label for="add-booking-date">Booking Date:</label>
                <input type="date" name="booking_date" id="add-booking-date" required>
            </div>
            
            <div class="form-group">
                <label for="add-return-date">Return Date:</label>
                <input type="date" name="return_date" id="add-return-date" required>
            </div>
            
            <div class="form-group">
                <label for="add-status">Status:</label>
                <select name="status" id="add-status" required>
                    <option value="Pending">Pending</option>
                    <option value="Active">Active</option>
                    <option value="Completed">Completed</option>
                    <option value="Pending">Pending</option>

                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-save">Save Booking</button>
            </div>
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
            
            <div class="form-group">
                <label for="edit-users">Customer Name:</label>
                <select name="users_id" id="edit-users" required>
                    <?php
                    $userss = $pdo->query("SELECT id, firstname FROM users")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($userss as $users) {
                        echo "<option value='{$users['id']}'>{$users['firstname']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit-car-id">Car:</label>
                <select name="car_id" id="edit-car-id" required>
                    <?php
                    $cars = $pdo->query("SELECT id, model FROM car")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($cars as $car) {
                        echo "<option value='{$car['id']}'>{$car['model']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit-location">Location:</label>
                <input type="text" name="location" id="edit-location" required>
            </div>
            
            <div class="form-group">
                <label for="edit-date">Booking Date:</label>
                <input type="date" name="booking_date" id="edit-date" required>
            </div>
            
            <div class="form-group">
                <label for="edit-return">Return Date:</label>
                <input type="date" name="return_date" id="edit-return" required>
            </div>
            
            <div class="form-group">
                <label for="edit-status">Status:</label>
                <select name="status" id="edit-status" required>
                    <option value="Pending">Pending</option>
                    <option value="Active">Active</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-save">Update Booking</button>
            </div>
        </form>
    </div>
  </div>

  <!-- VIEW Modal -->
  <div class="modal" id="viewModal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('viewModal')">×</span>
      <h2>Booking Details</h2>
      <p><strong>ID:</strong> <span id="view-id"></span></p>
      <p><strong>Customer:</strong> <span id="view-users"></span></p>
      <p><strong>Car Model:</strong> <span id="view-car"></span></p>
      <p><strong>Booking Date:</strong> <span id="view-date"></span></p>
      <p><strong>Return Date:</strong> <span id="view-return"></span></p>
      <p><strong>Status:</strong> <span id="view-status"></span></p>
      <button onclick="closeModal('viewModal')">Close</button> <!-- Add a close button -->
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
      document.getElementById('edit-users').value = data.users_id;
      document.getElementById('edit-car-id').value = data.car_id;
      document.getElementById('edit-date').value = data.booking_date;
      document.getElementById('edit-return').value = data.return_date;
      document.getElementById('edit-status').value = data.status;
      openModal('editModal');
    }
    function openViewModal(data) {
      document.getElementById('view-id').innerText = data.id;
      document.getElementById('view-users').innerText = data.users_id;
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
