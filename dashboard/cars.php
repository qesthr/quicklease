<?php
require_once '../db.php'; // Ensure this initializes $pdo

// Initialize variables
$error = "";

// Handle Add Car Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    if ($_POST["action"] === "add_car") {
        $model = trim($_POST["model"]);
        $plate_no = trim($_POST["plate_no"]);
        $price = trim($_POST["price"]);
        $status = $_POST["status"];

        // Validate input
        if (empty($model) || empty($plate_no) || empty($price) || empty($status)) {
            $error = "All fields are required!";
        } else {
            // Handle file upload
            if (!empty($_FILES["car_image"]["name"])) {
                $image_name = uniqid() . "_" . basename($_FILES["car_image"]["name"]);
                $target_dir = "../uploads/";
                $target_file = $target_dir . $image_name;

                // Check if the uploads directory exists
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
                }

                // Check file type (only allow images)
                $allowed_types = ["image/jpeg", "image/png", "image/gif"];
                if (!in_array($_FILES["car_image"]["type"], $allowed_types)) {
                    $error = "Invalid file type! Please upload JPEG, PNG, or GIF.";
                } elseif ($_FILES["car_image"]["size"] > 5000000) { // Limit: 5MB
                    $error = "File size too large! Max 5MB.";
                } else {
                    if (!move_uploaded_file($_FILES["car_image"]["tmp_name"], $target_file)) {
                        $error = "Error uploading file!";
                    }
                }
            }

            if (empty($error)) {
                // Insert into database using PDO
                $stmt = $pdo->prepare("INSERT INTO car (model, plate_no, price, status, image) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$model, $plate_no, $price, $status, $image_name])) {
                    header("Location: cars.php");
                    exit();
                } else {
                    $error = "Error adding car.";
                }
            }
        }
    }

    // Handle Edit Car Form Submission
    if ($_POST["action"] === "edit_car") {
        $car_id = $_POST["car_id"];
        $model = trim($_POST["model"]);
        $plate_no = trim($_POST["plate_no"]);
        $price = trim($_POST["price"]);
        $status = $_POST["status"];

        // Update the car in the database
        $stmt = $pdo->prepare("UPDATE car SET model = ?, plate_no = ?, price = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$model, $plate_no, $price, $status, $car_id])) {
            header("Location: cars.php");
            exit();
        } else {
            $error = "Error updating car.";
        }
    }
}

// Handle Delete Car
if (isset($_GET["delete_id"])) {
    $car_id = $_GET["delete_id"];
    $stmt = $pdo->prepare("DELETE FROM car WHERE id = ?");
    if ($stmt->execute([$car_id])) {
        header("Location: cars.php");
        exit();
    } else {
        $error = "Error deleting car.";
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
        img { width: 100px; height: auto; border-radius: 5px; }
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.4); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 5px; width: 50%; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover, .close:focus { color: black; text-decoration: none; cursor: pointer; }
        .error { color: red; }
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
    <button class="btn btn-add" id="openModal">+ Add Car</button>
</div>

<div class="content">
    <h2>Car List</h2>
    <table>
        <thead>
            <tr>
                <th>Car ID</th>
                <th>Image</th>
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
                <td>
                    <img src="../uploads/<?= htmlspecialchars($car['image']) ?>" alt="Car Image">
                </td>
                <td><?= htmlspecialchars($car['model']) ?></td>
                <td><?= htmlspecialchars($car['plate_no']) ?></td>
                <td><?= htmlspecialchars($car['price']) ?>/Day</td>
                <td><?= htmlspecialchars($car['status']) ?></td>
                <td>
                    <button class="btn btn-edit" onclick="openEditModal(<?= htmlspecialchars($car['id']) ?>, '<?= htmlspecialchars($car['model']) ?>', '<?= htmlspecialchars($car['plate_no']) ?>', <?= htmlspecialchars($car['price']) ?>, '<?= htmlspecialchars($car['status']) ?>')">Edit</button>
                    <a href="cars.php?delete_id=<?= htmlspecialchars($car['id']) ?>" class="btn btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Add Car -->
<div id="addCarModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Add a New Car</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_car">
            <label>Car Model:</label>
            <input type="text" name="model" required>
            
            <label>Plate No:</label>
            <input type="text" name="plate_no" required>
            
            <label>Price Per Day:</label>
            <input type="number" name="price" step="0.01" required>
            
            <label>Status:</label>
            <select name="status">
                <option value="Available">Available</option>
                <option value="Rented">Rented</option>
                <option value="Maintenance">Maintenance</option>
            </select>
            
            <label>Upload Car Image:</label>
            <input type="file" name="car_image" accept="image/*" required>

            <button type="submit" class="btn btn-add">Add Car</button>
        </form>
    </div>
</div>

<!-- Modal for Edit Car -->
<div id="editCarModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditModal">&times;</span>
        <h2>Edit Car</h2>
        <form method="post">
            <input type="hidden" name="action" value="edit_car">
            <input type="hidden" name="car_id" id="editCarId">
            
            <label>Car Model:</label>
            <input type="text" name="model" id="editCarModel" required>
            
            <label>Plate No:</label>
            <input type="text" name="plate_no" id="editCarPlateNo" required>
            
            <label>Price Per Day:</label>
            <input type="number" name="price" id="editCarPrice" step="0.01" required>
            
            <label>Status:</label>
            <select name="status" id="editCarStatus">
                <option value="Available">Available</option>
                <option value="Rented">Rented</option>
                <option value="Maintenance">Maintenance</option>
            </select>

            <button type="submit" class="btn btn-add">Update Car</button>
        </form>
    </div>
</div>

<script>
    // Get modal elements for Add Car
    const modal = document.getElementById("addCarModal");
    const openModalBtn = document.getElementById("openModal");
    const closeModalBtn = document.getElementById("closeModal");

    // Open Add Car Modal
    openModalBtn.onclick = function() {
        modal.style.display = "block";
    };

    // Close Add Car Modal
    closeModalBtn.onclick = function() {
        modal.style.display = "none";
    };

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };

    // Get modal elements for Edit Car
    const editModal = document.getElementById("editCarModal");
    const closeEditModalBtn = document.getElementById("closeEditModal");

    // Open Edit Modal
    function openEditModal(id, model, plateNo, price, status) {
        document.getElementById("editCarId").value = id;
        document.getElementById("editCarModel").value = model;
        document.getElementById("editCarPlateNo").value = plateNo;
        document.getElementById("editCarPrice").value = price;
        document.getElementById("editCarStatus").value = status;
        editModal.style.display = "block";
    }

    // Close Edit Modal
    closeEditModalBtn.onclick = function() {
        editModal.style.display = "none";
    };

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target === editModal) {
            editModal.style.display = "none";
        }
    };
</script>

</body>
</html>