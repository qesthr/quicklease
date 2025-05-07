<?php
require_once '../db.php';

// Handle Add Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Validate and sanitize input
    $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
    $car_id = isset($_POST['car_id']) ? intval($_POST['car_id']) : null;
    $booking_date = isset($_POST['booking_date']) ? trim($_POST['booking_date']) : null;
    $return_date = isset($_POST['return_date']) ? trim($_POST['return_date']) : null;
    $status = isset($_POST['status']) ? trim($_POST['status']) : null;
    $location = isset($_POST['location']) ? trim($_POST['location']) : null;

    // Check for missing fields
    if (empty($customer_id) || empty($car_id) || empty($booking_date) || empty($return_date) || empty($status) || empty($location)) {
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
        $stmt = $pdo->prepare("INSERT INTO bookings (customer_id, car_id, booking_date, return_date, status, location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customer_id, $car_id, $booking_date, $return_date, $status, $location]);

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
    $stmt = $pdo->prepare("UPDATE bookings SET customer_id=?, car_id=?, booking_date=?, return_date=?, status=?, location=? WHERE id=?");
    $stmt->execute([
        $_POST['customer_id'],
        $_POST['car_id'],
        $_POST['booking_date'],
        $_POST['return_date'],
        $_POST['status'],
        $_POST['location'],
        $_POST['id']
    ]);
    
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


    $stmt = $pdo->prepare("SELECT cu.id, cu.customer_id FROM bookings b INNER JOIN customer cu ON b.customer_id = cu.id WHERE b.id = ?");
    $stmt->execute([$_POST['id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);


    $message = "Dear {$customer['customer_id']}, your booking has been approved.";
    $stmt = $pdo->prepare("INSERT INTO notifications (customer_id, message) VALUES (?, ?)");
    $stmt->execute([$customer['id'], $message]);
}

// Handle Reject Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reject') {
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Rejected' WHERE id = ?");
    $stmt->execute([$_POST['id']]);


    $stmt = $pdo->prepare("SELECT cu.id, cu.customer_id FROM bookings b INNER JOIN customer cu ON b.customer_id = cu.id WHERE b.id = ?");
    $stmt->execute([$_POST['id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);


    $message = "Dear {$customer['customer_id']}, your booking has been rejected.";
    $stmt = $pdo->prepare("INSERT INTO notifications (customer_id, message) VALUES (?, ?)");
    $stmt->execute([$customer['id'], $message]);
}

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = '';
$params = [];

if (!empty($search)) {
    $whereClause = "WHERE b.id LIKE :search 
                    OR cu.customer_name LIKE :search 
                    OR c.model LIKE :search 
                    OR b.location LIKE :search 
                    OR b.booking_date LIKE :search 
                    OR b.return_date LIKE :search 
                    OR b.status LIKE :search";
    $params[':search'] = "%$search%";
}

$query = "SELECT 
        b.id, 
        cu.customer_name, 
        c.model AS car_model, 
        b.location,
        b.booking_date, 
        b.return_date, 
        b.status 
    FROM bookings b
    INNER JOIN customer cu ON b.customer_id = cu.id
    INNER JOIN car c ON b.car_id = c.id
    $whereClause
    ORDER BY b.booking_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookings | QuickLease Admin</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/bookings.css">
    <style>
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .search-container {
            display: flex;
            align-items: center;
        }
        .search-container form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-container input[type="text"] {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 250px;
        }
        .search-container button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-container button:hover {
            background: #45a049;
        }
        .clear-search {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        .clear-search:hover {
            color: #333;
        }
        .no-results {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .modal-content {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group select,
        .form-group input[type="text"],
        .form-group input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-actions {
            margin-top: 20px;
            text-align: right;
        }
        .btn-save {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-save:hover {
            background-color: #45a049;
        }
    </style>
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
                            <td><?= htmlspecialchars($booking['customer_name']) ?></td>
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
                <label for="add-customer">Customer Name:</label>
                <select name="customer_id" id="add-customer" required>
                    <option value="">Select Customer</option>
                    <?php
                    $customers = $pdo->query("SELECT id, customer_name FROM customer")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($customers as $customer) {
                        echo "<option value='{$customer['id']}'>{$customer['customer_name']}</option>";
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
                <label for="edit-customer">Customer Name:</label>
                <select name="customer_id" id="edit-customer" required>
                    <?php
                    $customers = $pdo->query("SELECT id, customer_name FROM customer")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($customers as $customer) {
                        echo "<option value='{$customer['id']}'>{$customer['customer_name']}</option>";
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
        <p><strong>Customer:</strong> <span id="view-customer"></span></p>
        <p><strong>Car Model:</strong> <span id="view-car"></span></p>
        <p><strong>Location:</strong> <span id="view-location"></span></p>
        <p><strong>Booking Date:</strong> <span id="view-date"></span></p>
        <p><strong>Return Date:</strong> <span id="view-return"></span></p>
        <p><strong>Status:</strong> <span id="view-status"></span></p>
        <button onclick="closeModal('viewModal')">Close</button>
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
        document.getElementById('edit-customer').value = data.customer_id;
        document.getElementById('edit-car-id').value = data.car_id;
        document.getElementById('edit-location').value = data.location;
        document.getElementById('edit-date').value = data.booking_date;
        document.getElementById('edit-return').value = data.return_date;
        document.getElementById('edit-status').value = data.status;
        openModal('editModal');
    }
    function openViewModal(data) {
        document.getElementById('view-id').innerText = data.id;
        document.getElementById('view-customer').innerText = data.customer_name;
        document.getElementById('view-car').innerText = data.car_model;
        document.getElementById('view-location').innerText = data.location;
        document.getElementById('view-date').innerText = data.booking_date;
        document.getElementById('view-return').innerText = data.return_date;
        document.getElementById('view-status').innerText = data.status;
        openModal('viewModal');
    }
    
    // Set default dates in the add form
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('add-booking-date').value = today;
        
        // Set return date to tomorrow by default
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('add-return-date').value = tomorrow.toISOString().split('T')[0];
    });
</script>

<script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>
</body>
</html>