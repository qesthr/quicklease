<?php
require_once realpath(__DIR__ . '/../../db.php');
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client | Cars Catalogue</title>

    <link rel="stylesheet" href="../../css/client.css">
    <link rel="stylesheet" href="../../css/client_cars.css">
    
    <link rel="preload" href="https://kit.fontawesome.com/b7bdbf86fb.js" as="script">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>

    <!--lenk to fontawesome-->
    <script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>
</head>

        <div class="content">
            <h2>Car List</h2>
            
            <div class="table-container">

                <div class="add-car-button-container">
                    <button id="openModal" class="btn btn-add">Add Car</button>
                </div>                
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
            <div class="modal-content-edit">
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

    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1, // Default for mobile
            spaceBetween: 20,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                // When window width is >= 768px
                768: {
                    slidesPerView: 2,
                    spaceBetween: 25
                },
                // When window width is >= 1024px
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            }
        });
    </script>
    
    
</body>
</html>
