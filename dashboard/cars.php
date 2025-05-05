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
        $seats = $_POST["seats"];
        $transmission = trim($_POST["transmission"]);
        $mileage = $_POST["mileage"];
        $features = trim($_POST["features"]);

        // Validate input
        if (empty($model) || empty($plate_no) || empty($price) || empty($status) || empty($seats) || empty($transmission) || empty($mileage) || empty($features)) {
            $error = "All fields are required!";
        } else {
            // Check for duplicate plate_no
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM car WHERE plate_no = ?");
            $stmt->execute([$plate_no]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $error = "A car with this plate number already exists!";
            } else {
                // Handle file upload
                if (!empty($_FILES["image"]["name"])) {
                    $image = uniqid() . "_" . basename($_FILES["image"]["name"]);
                    $target_dir = "../uploads/";
                    $target_file = $target_dir . $image;

                    // Check if the uploads directory exists
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
                    }

                    // Check file type (only allow images)
                    $allowed_types = ["image/jpeg", "image/png", "image/gif"];
                    if (!in_array($_FILES["image"]["type"], $allowed_types)) {
                        $error = "Invalid file type! Please upload JPEG, PNG, or GIF.";
                    } elseif ($_FILES["image"]["size"] > 5000000) { // Limit: 5MB
                        $error = "File size too large! Max 5MB.";
                    } else {
                        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                            $error = "Error uploading file!";
                        }
                    }
                }

                if (empty($error)) {
                    // Insert into database using PDO
                    $stmt = $pdo->prepare("INSERT INTO car (model, plate_no, price, status, image, seats, transmission, mileage, features) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$model, $plate_no, $price, $status, $image, $seats, $transmission, $mileage, $features])) {
                        header("Location: cars.php");
                        exit();
                    } else {
                        $error = "Error adding car.";
                    }
                }
            }
        }
    }
}
$plate_no = isset($_POST["plate_no"]) ? trim($_POST["plate_no"]) : null;
if (empty($plate_no)) {
    $error = "Plate number is required.";
}
$stmt = $pdo->prepare("SELECT COUNT(*) FROM car WHERE plate_no = ?");
$stmt->execute([$plate_no]);
$count = $stmt->fetchColumn();
if ($count > 0) {
    $error = "A car with this plate number already exists!";
}

   // Handle Edit Car Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "edit_car") {
    $car_id = $_POST["car_id"];
    $model = trim($_POST["model"]);
    $plate_no = trim($_POST["plate_no"]);
    $price = trim($_POST["price"]);
    $status = $_POST["status"];
    $seats = $_POST["seats"];
    $transmission = trim($_POST["transmission"]);
    $mileage = $_POST["mileage"];
    $features = trim($_POST["features"]);

    // Validate input
    if (empty($car_id) || empty($model) || empty($plate_no) || empty($price) || empty($status) || empty($seats) || empty($transmission) || empty($mileage) || empty($features)) {
        $error = "All fields are required!";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Price must be a positive number.";
    } elseif (!is_numeric($seats) || $seats <= 0) {
        $error = "Seats must be a positive number.";
    } elseif (!is_numeric($mileage) || $mileage < 0) {
        $error = "Mileage must be a non-negative number.";
    } else {
        try {
            // Update the car in the database
            $stmt = $pdo->prepare("UPDATE car SET model = ?, plate_no = ?, price = ?, status = ?, seats = ?, transmission = ?, mileage = ?, features = ? WHERE id = ?");
            $stmt->execute([$model, $plate_no, $price, $status, $seats, $transmission, $mileage, $features, $car_id]);

            // Redirect on success
            $_SESSION['success'] = "Car updated successfully.";
            header("Location: cars.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error updating car: " . $e->getMessage();
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
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/cars.css">
</head>
<body>
    <div class="car-list-page">
        <?php include 'includes/sidebar.php'; ?>
        <?php include 'includes/topbar.php'; ?>

        <div class="content">
            <h2>Car List</h2>

            <div class="add-car-button-container">
                <button id="openModal" class="btn btn-add">Add Car</button>
            </div>
            
            <div class="table-container">
                <table class="car-table">
                    <thead class="table-header">
                        <tr>
                            <th>Car ID</th>
                            <th>Image</th>
                            <th>Car Model</th>
                            <th>Plate No.</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Seats</th>
                            <th>Transmission</th>
                            <th>Mileage</th>
                            <th>Features</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?= htmlspecialchars($car['id']) ?></td>
                                <td><img src="../uploads/<?= htmlspecialchars($car['image']) ?>" alt="Car Image"></td>
                                <td><?= htmlspecialchars($car['model']) ?></td>
                                <td><?= htmlspecialchars($car['plate_no']) ?></td>
                                <td><?= htmlspecialchars($car['price']) ?>/Day</td>
                                <td><?= htmlspecialchars($car['status']) ?></td>
                                <td><?= htmlspecialchars($car['seats']) ?></td>
                                <td><?= htmlspecialchars($car['transmission']) ?></td>
                                <td><?= htmlspecialchars($car['mileage']) ?></td>
                                <td><?= htmlspecialchars($car['features']) ?></td>
                                <td>
                                    <button class="btn btn-edit" 
                                        onclick="openEditModal(
                                            <?= htmlspecialchars($car['id']) ?>, 
                                            '<?= htmlspecialchars($car['model']) ?>', 
                                            '<?= htmlspecialchars($car['plate_no']) ?>', 
                                            <?= htmlspecialchars($car['price']) ?>, 
                                            '<?= htmlspecialchars($car['status']) ?>', 
                                            <?= htmlspecialchars($car['seats']) ?>, 
                                            '<?= htmlspecialchars($car['transmission']) ?>', 
                                            <?= htmlspecialchars($car['mileage']) ?>, 
                                            '<?= htmlspecialchars($car['features']) ?>'
                                        )">
                                        Edit
                                    </button>
                                    <a href="cars.php?delete_id=<?= htmlspecialchars($car['id']) ?>" class="btn btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        

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
                    
                    <label>Number of Seats:</label>
                    <input type="number" name="seats" required>
                    
                    <label>Transmission:</label>
                    <input type="text" name="transmission" required>
                    
                    <label>Mileage (miles):</label>
                    <input type="number" name="mileage" required>
                    
                    <label>Features:</label>
                    <textarea name="features" rows="4" required></textarea>
                    
                    <label>Upload Car Image:</label>
                    <input type="file" name="image" accept="image/*" required>

                    <button type="submit" class="form button">Add Car</button>
                </form>
            </div>
        </div>

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
                    
                    <label>Number of Seats:</label>
                    <input type="number" name="seats" id="editCarSeats" required>
                    
                    <label>Transmission:</label>
                    <input type="text" name="transmission" id="editCarTransmission" required>
                    
                    <label>Mileage (miles):</label>
                    <input type="number" name="mileage" id="editCarMileage" required>
                    
                    <label>Features:</label>
                    <textarea name="features" id="editCarFeatures" rows="4" required></textarea>

                    <button type="submit" class="form button">Update Car</button>
                </form>
            </div>
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
        function openEditModal(id, model, plate_no, price, status, seats, transmission, mileage, features) {
            document.getElementById("editCarId").value = id;
            document.getElementById("editCarModel").value = model;
            document.getElementById("editCarPlateNo").value = plate_no;
            document.getElementById("editCarPrice").value = price;
            document.getElementById("editCarStatus").value = status;
            document.getElementById("editCarSeats").value = seats;
            document.getElementById("editCarTransmission").value = transmission;
            document.getElementById("editCarMileage").value = mileage;
            document.getElementById("editCarFeatures").value = features;
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

<script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>
</body>
</html>