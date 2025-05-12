<?php
require_once('../db.php');

$success = '';
$error = '';

// Handle delete car
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM car WHERE id = ?");
    if ($stmt->execute([$deleteId])) {
        $success = "Car deleted successfully.";
    } else {
        $error = "Failed to delete car.";
    }
}

// Handle add car
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_car') {
    $model = $_POST['model'] ?? '';
    $plate_no = $_POST['plate_no'] ?? '';
    $price = $_POST['price'] ?? 0;
    $status = $_POST['status'] ?? '';
    $seats = $_POST['seats'] ?? 0;
    $transmission = $_POST['transmission'] ?? '';
    $mileage = $_POST['mileage'] ?? 0;
    $features = $_POST['features'] ?? '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if ($fileSize <= 2 * 1024 * 1024) { // 2MB limit
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = '../uploads/';
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Insert car with image
                    $stmt = $pdo->prepare("INSERT INTO car (model, plate_no, price, status, seats, transmission, mileage, features, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$model, $plate_no, $price, $status, $seats, $transmission, $mileage, $features, $newFileName])) {
                        $success = "Car added successfully.";
                    } else {
                        $error = "Failed to add car to database.";
                    }
                } else {
                    $error = "Error moving the uploaded file.";
                }
            } else {
                $error = "Image file size exceeds 2MB limit.";
            }
        } else {
            $error = "Upload failed. Allowed file types: " . implode(", ", $allowedfileExtensions);
        }
    } else {
        $error = "Image upload error or no image uploaded.";
    }
}

// Handle edit car
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_car') {
    $car_id = $_POST['car_id'] ?? 0;
    $model = $_POST['model'] ?? '';
    $plate_no = $_POST['plate_no'] ?? '';
    $price = $_POST['price'] ?? 0;
    $status = $_POST['status'] ?? '';
    $seats = $_POST['seats'] ?? 0;
    $transmission = $_POST['transmission'] ?? '';
    $mileage = $_POST['mileage'] ?? 0;
    $features = $_POST['features'] ?? '';

    $imageUpdated = false;
    $newFileName = '';

    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if ($fileSize <= 2 * 1024 * 1024) { // 2MB limit
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = '../uploads/';
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $imageUpdated = true;
                } else {
                    $error = "Error moving the uploaded file.";
                }
            } else {
                $error = "Image file size exceeds 2MB limit.";
            }
        } else {
            $error = "Upload failed. Allowed file types: " . implode(", ", $allowedfileExtensions);
        }
    }

    if (!$error) {
        if ($imageUpdated) {
            $stmt = $pdo->prepare("UPDATE car SET model = ?, plate_no = ?, price = ?, status = ?, seats = ?, transmission = ?, mileage = ?, features = ?, image = ? WHERE id = ?");
            $params = [$model, $plate_no, $price, $status, $seats, $transmission, $mileage, $features, $newFileName, $car_id];
        } else {
            $stmt = $pdo->prepare("UPDATE car SET model = ?, plate_no = ?, price = ?, status = ?, seats = ?, transmission = ?, mileage = ?, features = ? WHERE id = ?");
            $params = [$model, $plate_no, $price, $status, $seats, $transmission, $mileage, $features, $car_id];
        }

        if ($stmt->execute($params)) {
            $success = "Car updated successfully.";
        } else {
            $error = "Failed to update car.";
        }
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch cars data
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM car WHERE model LIKE :search OR transmission LIKE :search OR plate_no LIKE :search OR status LIKE :search");
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM car");
}
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Cars Catalogue</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/cars.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="car-list-page">
    <?php include './includes/sidebar.php'; ?>
    
    <div class="main">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/quicklease/dashboard/includes/topbar.php';;?>

        <form class="search-container" action="" method="GET">
            <input class="searchbar" type="text" name="search" placeholder="Search cars..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <div class="content">
            <h2>Car List</h2>

            <div class="add-car-button-container">
                <button id="openModal" class="btn btn-add">
                    <i class="fas fa-plus"></i>
                </button>
                
            </div>

            <div class="table-container">               
                <table class="car-table">
                    <thead class="table-header">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Model</th>
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
                            <td>
                                <img src="../uploads/<?= htmlspecialchars($car['image']) ?>" 
                                     alt="<?= htmlspecialchars($car['model']) ?>" 
                                     class="car-image">
                            </td>
                            <td><?= htmlspecialchars($car['model']) ?></td>
                            <td><?= htmlspecialchars($car['plate_no']) ?></td>
                            <td>₱<?= number_format($car['price'], 2) ?>/day</td>
                            <td>
                                <span class="status-badge <?= strtolower($car['status']) ?>">
                                    <?= htmlspecialchars($car['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($car['seats']) ?></td>
                            <td><?= htmlspecialchars($car['transmission']) ?></td>
                            <td><?= number_format($car['mileage']) ?> miles</td>
                            <td class="features-cell">
                                <?= htmlspecialchars($car['features']) ?>
                            </td>
                            <td class="action-buttons">
                                <button class="btn btn-edit" onclick="openEditModal(
                                    '<?= $car['id'] ?>',
                                    '<?= addslashes($car['model']) ?>',
                                    '<?= addslashes($car['plate_no']) ?>',
                                    '<?= $car['price'] ?>',
                                    '<?= $car['status'] ?>',
                                    '<?= $car['seats'] ?>',
                                    '<?= addslashes($car['transmission']) ?>',
                                    '<?= $car['mileage'] ?>',
                                    '<?= addslashes($car['features']) ?>'
                                )">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="cars.php?delete_id=<?= $car['id'] ?>" 
                                   class="btn btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this car?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Car Modal -->
        <div id="addCarModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeModal">&times;</span>
                <h2>Add New Car</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_car">
                    
                    <div class="form-group">
                        <label for="model">Car Model:</label>
                        <input type="text" name="model" id="model" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="plate_no">Plate No:</label>
                        <input type="text" name="plate_no" id="plate_no" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price/Day:</label>
                        <div class="input-with-icon">
                            <span class="currency-symbol">₱</span>
                            <input type="number" name="price" id="price" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select name="status" id="status" required>
                            <option value="Available">Available</option>
                            <option value="Rented">Rented</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="seats">Seats:</label>
                        <input type="number" name="seats" id="seats" min="1" max="10" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="transmission">Transmission:</label>
                        <select name="transmission" id="transmission" required>
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mileage">Mileage:</label>
                        <div class="input-with-icon">
                            <input type="number" name="mileage" id="mileage" required>
                            <span class="unit">miles</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="features">Features:</label>
                        <textarea name="features" id="features" rows="3" placeholder="Enter car features (e.g., GPS, Bluetooth, etc.)" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image:</label>
                        <input type="file" name="image" id="image" accept="image/*" required>
                        <div class="image-preview" id="imagePreview"></div>
                    </div>

                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save"></i> Save Car
                    </button>
                </form>
            </div>
        </div>

        <!-- Edit Car Modal -->
        <div id="editCarModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeEditModal">&times;</span>
                <h2>Edit Car</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit_car">
                    <input type="hidden" name="car_id" id="editCarId">
                    
                    <div class="form-group">
                        <label for="editCarModel">Car Model:</label>
                        <input type="text" name="model" id="editCarModel" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editPlateNo">Plate No:</label>
                        <input type="text" name="plate_no" id="editPlateNo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editPrice">Price/Day:</label>
                        <div class="input-with-icon">
                            <span class="currency-symbol">₱</span>
                            <input type="number" name="price" id="editPrice" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editStatus">Status:</label>
                        <select name="status" id="editStatus" required>
                            <option value="Available">Available</option>
                            <option value="Rented">Rented</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editSeats">Seats:</label>
                        <input type="number" name="seats" id="editSeats" min="1" max="10" required>
                    </div>

                    <div class="form-group">
                        <label for="editTransmission">Transmission:</label>
                        <select name="transmission" id="editTransmission" required>
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editMileage">Mileage:</label>
                        <div class="input-with-icon">
                            <input type="number" name="mileage" id="editMileage" required>
                            <span class="unit">miles</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editFeatures">Features:</label>
                        <textarea name="features" id="editFeatures" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="editImage">New Image (optional):</label>
                        <input type="file" name="image" id="editImage" accept="image/*">
                        <div class="image-preview" id="editImagePreview"></div>
                    </div>

                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save"></i> Update Car
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal handling
        const addModal = document.getElementById('addCarModal');
        const editModal = document.getElementById('editCarModal');
        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementById('closeModal');
        const closeEditModalBtn = document.getElementById('closeEditModal');
        
        openModalBtn.onclick = function() {
            addModal.style.display = 'block';
        }
        
        closeModalBtn.onclick = function() {
            addModal.style.display = 'none';
        }
        
        closeEditModalBtn.onclick = function() {
            editModal.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
        }
        
        function openEditModal(id, model, plateNo, price, status, seats, transmission, mileage, features) {
            document.getElementById('editCarId').value = id;
            document.getElementById('editCarModel').value = model;
            document.getElementById('editPlateNo').value = plateNo;
            document.getElementById('editPrice').value = price;
            document.getElementById('editStatus').value = status;
            document.getElementById('editSeats').value = seats;
            document.getElementById('editTransmission').value = transmission;
            document.getElementById('editMileage').value = mileage;
            document.getElementById('editFeatures').value = features;
            
            editModal.style.display = 'block';
        }

        // Image preview functionality
        function handleImagePreview(input, previewId) {
            const preview = document.getElementById(previewId);
            input.addEventListener('change', function() {
                preview.innerHTML = '';
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });
        }

        handleImagePreview(document.getElementById('image'), 'imagePreview');
        handleImagePreview(document.getElementById('editImage'), 'editImagePreview');
    </script>
</body>
</html>