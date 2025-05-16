<?php
require_once '../db.php';

// Handle Add Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $users_id = isset($_POST['users_id']) ? trim($_POST['users_id']) : null;
        $car_id = isset($_POST['car_id']) ? intval($_POST['car_id']) : null;
        $location = isset($_POST['location']) ? trim($_POST['location']) : null;
        $booking_date = isset($_POST['booking_date']) ? trim($_POST['booking_date']) : null;
        $return_date = isset($_POST['return_date']) ? trim($_POST['return_date']) : null;
        $status = isset($_POST['status']) ? trim($_POST['status']) : null;

        if (empty($users_id) || empty($car_id) || empty($location) || empty($booking_date) || empty($return_date) || empty($status)) {
            throw new Exception("All fields are required.");
        }

        if (!strtotime($booking_date) || !strtotime($return_date)) {
            throw new Exception("Invalid booking or return date.");
        }

        if (strtotime($return_date) <= strtotime($booking_date)) {
            throw new Exception("Return date must be after the booking date.");
        }

        // Check if car is available
        $stmt = $pdo->prepare("SELECT status FROM car WHERE id = ?");
        $stmt->execute([$car_id]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car || $car['status'] !== 'Available') {
            throw new Exception("Selected car is not available for booking.");
        }

        $pdo->beginTransaction();

        // Insert booking
        $stmt = $pdo->prepare("INSERT INTO bookings (users_id, car_id, location, booking_date, return_date, status) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$users_id, $car_id, $location, $booking_date, $return_date, $status]);

        // Update car status if booking is Active
        if ($status === 'Active') {
            $stmt = $pdo->prepare("UPDATE car SET status = 'Booked' WHERE id = ?");
            $stmt->execute([$car_id]);
        }

        $pdo->commit();
        $_SESSION['success'] = "Booking added successfully.";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: bookings.php");
    exit;
}

// Handle Edit Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edit') {
    $stmt = $pdo->prepare("UPDATE bookings SET users_id=?, car_id=?, booking_date=?, return_date=?, status=? WHERE id=?");
    $stmt->execute([
        $_POST['users_id'],
        $_POST['car_id'],
        $_POST['booking_date'],
        $_POST['return_date'],
        $_POST['status'],
        $_POST['id']
    ]);
    $_SESSION['success'] = "Booking updated successfully.";
    header("Location: bookings.php");
    exit;
}

// Handle Cancel Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'cancel') {
    $stmt = $pdo->prepare("UPDATE bookings SET status='Cancelled' WHERE id=?");
    $stmt->execute([$_POST['id']]);
    header("Location: bookings.php");
    exit;
}

// ✅ Handle Approve Booking (with Notification)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'approve') {
    try {
        $pdo->beginTransaction();
        
        $id = $_POST['id'];
        $status = "Active";

        // Get booking and car information
        $stmt = $pdo->prepare("SELECT b.*, c.id as car_id FROM bookings b 
                              INNER JOIN car c ON b.car_id = c.id 
                              WHERE b.id = ?");
        $stmt->execute([$id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new Exception("Booking not found");
        }

        // Update booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // Update car status to 'Booked'
        $stmt = $pdo->prepare("UPDATE car SET status = 'Booked' WHERE id = ?");
        $stmt->execute([$booking['car_id']]);

        // Fetch customer info
        $stmt = $pdo->prepare("SELECT u.id, u.firstname, u.email FROM bookings b 
                              INNER JOIN users u ON b.users_id = u.id 
                              WHERE b.id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $message = "Dear {$user['firstname']}, your booking has been approved.";
            $stmt = $pdo->prepare("INSERT INTO notifications (users_id, message) VALUES (?, ?)");
            $stmt->execute([$user['id'], $message]);
        }

        $pdo->commit();
        $_SESSION['success'] = "Booking approved successfully";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error approving booking: " . $e->getMessage();
    }

    header("Location: bookings.php");
    exit;
}

// Handle Reject Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'reject') {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Rejected' WHERE id = ?");
    $stmt->execute([$id]);

    // Fetch customer info
    $stmt = $pdo->prepare("SELECT cu.id, cu.users_id FROM bookings b INNER JOIN users cu ON b.users_id = cu.id WHERE b.id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $message = "Dear {$user['users_id']}, your booking has been rejected.";
        $stmt = $pdo->prepare("INSERT INTO notifications (users_id, message) VALUES (?, ?)");
        $stmt->execute([$user['id'], $message]);
    }

    header("Location: bookings.php");
    exit;
}

// Fetch bookings
$stmt = $pdo->query("SELECT 
        b.id,
        b.users_id,
        b.car_id, 
        cu.firstname, 
        c.model AS car_model, 
        c.price,
        b.location,
        b.booking_date, 
        b.return_date, 
        b.status 
    FROM bookings b
    INNER JOIN users cu ON b.users_id = cu.id
    INNER JOIN car c ON b.car_id = c.id
    ORDER BY b.booking_date DESC");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT 
            b.id,
            b.users_id,
            b.car_id, 
            cu.firstname, 
            c.model AS car_model, 
            b.location,
            b.booking_date, 
            b.return_date, 
            b.status 
        FROM bookings b
        INNER JOIN users cu ON b.users_id = cu.id
        INNER JOIN car c ON b.car_id = c.id
        WHERE 
            cu.firstname LIKE :search OR 
            c.model LIKE :search OR 
            b.booking_date LIKE :search OR 
            b.return_date LIKE :search OR 
            b.status LIKE :search
        ORDER BY b.booking_date DESC");
    $stmt->execute(['search' => '%' . $search . '%']);
} else {
    $stmt = $pdo->query("SELECT 
            b.id,
            b.users_id,
            b.car_id, 
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
}
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

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="booking-header">
        <div class="add-booking-button-container">
            <button class="btn btn-add" onclick="openModal('addModal')">Add Booking</button>
        </div>
        <div class="search-container">
            <form method="GET" action="bookings.php">
            <?php
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            ?>
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
                                <?php if ($booking['status'] !== 'Cancelled' && $booking['status'] !== 'Completed'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $booking['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="cancel-btn" onclick="return confirm('Cancel this booking?')">Cancel</button>
                                    </form>
                                <?php endif; ?>
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
                <label for="add-booking-date">Booking Date:</label>
                <input type="date" name="booking_date" id="add-booking-date" required>
            </div>
            
            <div class="form-group">
                <label for="add-return-date">Return Date:</label>
                <input type="date" name="return_date" id="add-return-date" required>
            </div>
            
            <div class="form-group">
                <label for="add-location">Location:</label>
                <select name="location" id="add-location" required>
                    <option value="">Select Location</option>
                    <option value="Horhe Car Rental and Carwash">Horhe Car Rental and Carwash, Km. 4 Sayre Hwy, Malaybalay</option>
                    <option value="JJL CAR RENTAL SERVICES">JJL CAR RENTAL SERVICES AND CARWASH, Magsaysay Ext, Malaybalay</option>
                    <option value="DS CAR RENTAL SERVICES">DS CAR RENTAL SERVICES, NATIONAL HIGH WAY, ZONE 1, Malaybalay</option>
                    <option value="Pren's Car Rental Services">Pren's Car Rental Services, Km. 4 Sayre Hwy, Malaybalay</option>
                    <option value="ZV Car Rental">ZV Car Rental, P5, Malaybalay</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="add-status">Status:</label>
                <select name="status" id="add-status" required>
                    <option value="Pending">Pending</option>
                    <option value="Active">Active</option>
                    <option value="Completed">Completed</option>
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
                <h2>Edit Booking Status</h2>
                <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit-id">

                <!-- Customer Name (read-only) -->
                <div class="form-group">
                    <label for="edit-users-text">Customer Name:</label>
                    <input type="text" id="edit-users-text" readonly>
                    <input type="hidden" name="users_id" id="edit-users">
                </div>

                <!-- Car (read-only) -->
                <div class="form-group">
                    <label for="edit-car-id-text">Car:</label>
                    <input type="text" id="edit-car-id-text" readonly>
                    <input type="hidden" name="car_id" id="edit-car-id">
                </div>

                <!-- Location (read-only) -->
                <div class="form-group">
                    <label for="edit-location">Location:</label>
                    <input type="text" name="location" id="edit-location" readonly>
                </div>

                <!-- Booking Date (read-only) -->
                <div class="form-group">
                    <label for="edit-date">Booking Date:</label>
                    <input type="date" name="booking_date" id="edit-date" readonly>
                </div>

                <!-- Return Date (read-only) -->
                <div class="form-group">
                    <label for="edit-return">Return Date:</label>
                    <input type="date" name="return_date" id="edit-return" readonly>
                </div>

                <!-- Status (editable) -->
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
                    <button type="submit" class="btn-save">Update Status</button>
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
      <p><strong>Location:</strong> <span id="view-location"></span></p>
      <p><strong>Price per Day:</strong> <span id="view-price"></span></p>
      <p><strong>Total Price:</strong> <span id="view-total-price"></span></p>
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
    document.getElementById('edit-users-text').value = data.firstname;
    document.getElementById('edit-car-id').value = data.car_id;
    document.getElementById('edit-car-id-text').value = data.car_model;
    document.getElementById('edit-location').value = data.location;
    document.getElementById('edit-date').value = data.booking_date;
    document.getElementById('edit-return').value = data.return_date;
    document.getElementById('edit-status').value = data.status;
    openModal('editModal');
}

    function openViewModal(data) {
      document.getElementById('view-id').innerText = data.id;
      document.getElementById('view-users').innerText = data.firstname;
      document.getElementById('view-car').innerText = data.car_model;
      document.getElementById('view-location').innerText = data.location;
      document.getElementById('view-price').innerText = "₱" + (data.price ? data.price.toFixed(2) : "0.00");
      
      // Calculate total price based on number of days
      const bookingDate = new Date(data.booking_date);
      const returnDate = new Date(data.return_date);
      const diffTime = Math.abs(returnDate - bookingDate);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      const totalPrice = diffDays * (data.price || 0);
      
      document.getElementById('view-total-price').innerText = "₱" + totalPrice.toFixed(2);
      document.getElementById('view-date').innerText = data.booking_date;
      document.getElementById('view-return').innerText = data.return_date;
      document.getElementById('view-status').innerText = data.status;
      openModal('viewModal');
    }
  </script>

<script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>

</body>
</html>
