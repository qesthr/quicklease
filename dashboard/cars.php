<?php
require_once('../db.php');
require_once('../includes/session_handler.php');

// Start admin session and check access
startAdminSession();
requireAdmin();

// Get the current user's information
$user_id = $_SESSION['user_id'] ?? null;
$user = null;

if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'admin'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

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
        <?php include './includes/topbar.php'; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <div class="content">

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Car List</h2>
                <button id="openModal" class="add-btn">
                    <div class="btn-content">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add New Car</span>
                    </div>
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
                        <div class="file-upload-container">
                            <div class="file-upload-area" id="dropZone">
                                <input type="file" name="image" id="image" accept="image/*" class="file-input" required>
                                <div class="file-upload-content">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <div class="upload-text">
                                        <span class="main-text">Drag & Drop your image here</span>
                                        <span class="sub-text">or</span>
                                        <button type="button" class="browse-btn">Browse Files</button>
                                    </div>
                                    <span class="file-info">Maximum file size: 2MB</span>
                                </div>
                            </div>
                            <div class="image-preview" id="imagePreview"></div>
                        </div>
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

        document.addEventListener('DOMContentLoaded', function() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.querySelector('.file-input');
            const imagePreview = document.getElementById('imagePreview');

            // Drag and drop functionality
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                dropZone.classList.add('dragover');
            }

            function unhighlight(e) {
                dropZone.classList.remove('dragover');
            }

            dropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                fileInput.files = files;
                handleFiles(files);
            }

            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });

            function handleFiles(files) {
                if (files.length > 0) {
                    const file = files[0];
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                            imagePreview.classList.add('has-image');
                        }
                        reader.readAsDataURL(file);
                    }
                }
            }

            // Make the browse button functional
            document.querySelector('.browse-btn').addEventListener('click', function() {
                fileInput.click();
            });
        });
    </script>

    <style>
        /* Base styles for the layout */
        .main {
            padding: 10px;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Header and Search Styles */
        .header {
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .header .left {
            flex: 1;
            min-width: 250px;
            max-width: 100%;
        }

        .header h1 {
            margin: 0;
            color: #333;
            font-size: clamp(18px, 2vw, 24px);
            white-space: nowrap;
        }

        .search-container {
            margin-top: 10px;
            width: 100%;
        }

        .search-wrapper {
            width: 100%;
            max-width: 300px;
        }

        .header .right {
            flex-shrink: 0;
        }

        /* Table Container Styles */
        .table-container {
            margin: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: auto;
            max-width: calc(100vw - 20px);
        }

        .car-table {
            width: 100%;
            min-width: max-content;
            border-collapse: collapse;
        }

        .car-table th,
        .car-table td {
            padding: 8px;
            text-align: left;
            font-size: clamp(12px, 1.5vw, 14px);
        }

        /* Column widths */
        .car-table th:nth-child(1), .car-table td:nth-child(1) { min-width: 50px; }  /* ID */
        .car-table th:nth-child(2), .car-table td:nth-child(2) { min-width: 100px; } /* Image */
        .car-table th:nth-child(3), .car-table td:nth-child(3) { min-width: 120px; } /* Model */
        .car-table th:nth-child(4), .car-table td:nth-child(4) { min-width: 100px; } /* Plate */
        .car-table th:nth-child(5), .car-table td:nth-child(5) { min-width: 80px; }  /* Price */
        .car-table th:nth-child(6), .car-table td:nth-child(6) { min-width: 100px; } /* Status */
        .car-table th:nth-child(7), .car-table td:nth-child(7) { min-width: 60px; }  /* Seats */
        .car-table th:nth-child(8), .car-table td:nth-child(8) { min-width: 100px; } /* Transmission */
        .car-table th:nth-child(9), .car-table td:nth-child(9) { min-width: 80px; }  /* Mileage */
        .car-table th:nth-child(10), .car-table td:nth-child(10) { min-width: 150px; } /* Features */
        .car-table th:nth-child(11), .car-table td:nth-child(11) { min-width: 100px; } /* Actions */

        /* Image styles */
        .car-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: clamp(12px, 1.5vw, 14px);
            white-space: nowrap;
        }

        /* Improved Header Button Styles */
        .add-btn {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .add-btn .btn-content {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-btn i {
            font-size: 16px;
        }

        .add-btn:hover {
            background: linear-gradient(45deg, #45a049, #4CAF50);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .add-btn:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Improved Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            animation: modalFadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            width: min(95%, 600px);
            margin: 20px auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }

        .modal h2 {
            color: #333;
            margin: 0 0 20px 0;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal h2::before {
            content: '';
            width: 4px;
            height: 24px;
            background: #4CAF50;
            border-radius: 2px;
            display: inline-block;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close:hover {
            background: #f5f5f5;
            color: #333;
            transform: rotate(90deg);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            background: white;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon .currency-symbol,
        .input-with-icon .unit {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 14px;
        }

        .input-with-icon .currency-symbol {
            left: 12px;
        }

        .input-with-icon .unit {
            right: 12px;
        }

        .input-with-icon input {
            padding-left: 30px;
            padding-right: 50px;
        }

        /* File Upload Styles */
        .file-upload-container {
            margin-top: 10px;
        }

        .file-upload-area {
            position: relative;
            width: 92%;
            min-height: 200px;
            border: 2px dashed #4CAF50;
            border-radius: 12px;
            background: #f8fdf8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-area:hover {
            background: #f0f9f0;
            border-color: #45a049;
        }

        .file-upload-area.dragover {
            background: #e8f5e9;
            border-color: #2e7d32;
        }

        .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-content {
            text-align: center;
            color: #2e7d32;
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #4CAF50;
        }

        .upload-text {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .main-text {
            font-size: 16px;
            font-weight: 500;
        }

        .sub-text {
            font-size: 14px;
            color: #666;
        }

        .browse-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px 0;
        }

        .browse-btn:hover {
            background: #45a049;
            transform: translateY(-1px);
        }

        .file-info {
            display: block;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }

        .image-preview {
            margin-top: 15px;
            border-radius: 12px;
            overflow: hidden;
            display: none;
        }

        .image-preview.has-image {
            display: block;
            padding: 10px;
            background: white;
            border: 1px solid #e0e0e0;
        }

        .image-preview img {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 8px;
        }

        /* Submit Button */
        .btn-submit {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(45deg, #45a049, #4CAF50);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        /* Modal Animations */
        @keyframes modalFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive Modal */
        @media screen and (max-width: 768px) {
            .modal-content {
                margin: 10px;
                padding: 20px;
            }

            .modal h2 {
                font-size: 20px;
            }

            .form-group label {
                font-size: 14px;
            }

            .btn-submit {
                padding: 10px 20px;
                font-size: 14px;
            }
        }

        /* Responsive breakpoints */
        @media screen and (max-width: 1366px) {
            .header {
                flex-direction: row;
                justify-content: space-between;
            }

            .search-wrapper {
                max-width: 250px;
            }

            .table-container {
                font-size: 13px;
            }
        }

        @media screen and (max-width: 992px) {
            .header {
                flex-direction: column;
                align-items: stretch;
            }

            .header .left,
            .header .right {
                width: 100%;
            }

            .search-wrapper {
                max-width: 100%;
            }

            .add-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media screen and (max-width: 768px) {
            .main {
                padding: 5px;
            }

            .header,
            .table-container,
            .alert {
                margin: 5px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
                text-align: center;
            }
        }

        /* Zoom handling */
        @media screen and (max-width: 480px), screen and (zoom: 150%), screen and (zoom: 175%) {
            .header {
                padding: 10px;
            }

            .header h1 {
                font-size: 16px;
            }

            .search-input,
            .add-btn {
                font-size: 12px;
                padding: 6px 10px;
            }

            .table-container {
                font-size: 11px;
            }

            .car-image {
                width: 60px;
                height: 45px;
            }
        }

        /* Print styles */
        @media print {
            .main {
                padding: 0;
            }

            .header,
            .action-buttons,
            .modal {
                display: none;
            }

            .table-container {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</body>
</html>