<?php
require_once __DIR__ . '/../includes/session_handler.php';

// Start admin session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startAdminSession();
}

requireAdmin(); // Ensure only admins can access this page

$locations_file = __DIR__ . '/data/locations.json';

// Handle Location Management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        // Read current locations
        $json_data = file_get_contents($locations_file);
        $data = json_decode($json_data, true);
        
        switch ($_POST['action']) {
            case 'add_location':
                $name = trim($_POST['name']);
                $address = trim($_POST['address']);
                $city = trim($_POST['city']);
                $service_area = trim($_POST['service_area']);
                $pickup_instructions = trim($_POST['pickup_instructions']);
                $dropoff_instructions = trim($_POST['dropoff_instructions']);
                $branch_phone = trim($_POST['branch_phone']);
                $branch_email = trim($_POST['branch_email']);
                $operating_hours = trim($_POST['operating_hours']);
                
                // Validate inputs
                if (empty($name) || empty($address) || empty($city)) {
                    throw new Exception("Name, address, and city are required.");
                }

                // Generate new ID
                $max_id = 0;
                foreach ($data['locations'] as $location) {
                    $max_id = max($max_id, $location['id']);
                }
                $new_id = $max_id + 1;

                // Add new location with extended information
                $data['locations'][] = [
                    'id' => $new_id,
                    'name' => $name,
                    'address' => $address,
                    'city' => $city,
                    'service_area' => $service_area,
                    'pickup_instructions' => $pickup_instructions,
                    'dropoff_instructions' => $dropoff_instructions,
                    'branch_phone' => $branch_phone,
                    'branch_email' => $branch_email,
                    'operating_hours' => $operating_hours,
                    'status' => 'Active'
                ];

                file_put_contents($locations_file, json_encode($data, JSON_PRETTY_PRINT));
                $_SESSION['success'] = "Location added successfully.";
                break;

            case 'update_location':
                $location_id = (int)$_POST['location_id'];
                $name = trim($_POST['name']);
                $address = trim($_POST['address']);
                $city = trim($_POST['city']);
                $service_area = trim($_POST['service_area']);
                $pickup_instructions = trim($_POST['pickup_instructions']);
                $dropoff_instructions = trim($_POST['dropoff_instructions']);
                $branch_phone = trim($_POST['branch_phone']);
                $branch_email = trim($_POST['branch_email']);
                $operating_hours = trim($_POST['operating_hours']);
                $status = $_POST['status'];

                foreach ($data['locations'] as &$location) {
                    if ($location['id'] === $location_id) {
                        $location['name'] = $name;
                        $location['address'] = $address;
                        $location['city'] = $city;
                        $location['service_area'] = $service_area;
                        $location['pickup_instructions'] = $pickup_instructions;
                        $location['dropoff_instructions'] = $dropoff_instructions;
                        $location['branch_phone'] = $branch_phone;
                        $location['branch_email'] = $branch_email;
                        $location['operating_hours'] = $operating_hours;
                        $location['status'] = $status;
                        break;
                    }
                }

                file_put_contents($locations_file, json_encode($data, JSON_PRETTY_PRINT));
                $_SESSION['success'] = "Location updated successfully.";
                break;

            case 'delete_location':
                $location_id = (int)$_POST['location_id'];
                
                $data['locations'] = array_filter($data['locations'], function($location) use ($location_id) {
                    return $location['id'] !== $location_id;
                });

                file_put_contents($locations_file, json_encode($data, JSON_PRETTY_PRINT));
                $_SESSION['success'] = "Location deleted successfully.";
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: location_settings.php");
    exit;
}

// Read locations
$json_data = file_get_contents($locations_file);
$data = json_decode($json_data, true);
$locations = $data['locations'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Management | QuickLease Admin</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 3% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close:hover {
            color: #333;
        }

        .location-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .location-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }

        .location-card h3 {
            color: #1818CA;
            margin: 0 0 10px 0;
            font-size: 1.2rem;
        }

        .location-info {
            color: #666;
            margin-bottom: 15px;
        }

        .location-info p {
            margin: 5px 0;
        }

        .location-details {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin: 10px 0;
        }

        .location-details h4 {
            color: #333;
            margin: 5px 0;
            font-size: 1rem;
        }

        .location-details p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #666;
        }

        .location-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .location-actions {
            display: flex;
            gap: 10px;
        }

        .btn-edit, .btn-delete {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #1818CA;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-edit:hover, .btn-delete:hover {
            transform: translateY(-2px);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-add {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .btn-save {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .manage-locations-btn {
            background: #1818CA;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .manage-locations-btn:hover {
            background: #1515A0;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="settings-content">
        <?php include 'includes/topbar.php'; ?>

        <div class="content-wrapper">
            <button class="manage-locations-btn" onclick="openLocationsModal()">
                <i class="fas fa-map-marker-alt"></i> Manage Locations
            </button>

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
        </div>
    </div>

    <!-- Locations Management Modal -->
    <div id="locationsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLocationsModal()">&times;</span>
            <div class="modal-header">
                <h2>Location Management</h2>
                <button class="btn-add" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add New Location
                </button>
            </div>

            <div class="location-grid">
                <?php foreach ($locations as $location): ?>
                    <div class="location-card">
                        <h3><?php echo htmlspecialchars($location['name']); ?></h3>
                        
                        <div class="location-status status-<?php echo strtolower($location['status']); ?>">
                            <?php echo htmlspecialchars($location['status']); ?>
                        </div>

                        <div class="location-info">
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($location['address']); ?></p>
                            <p><i class="fas fa-city"></i> <?php echo htmlspecialchars($location['city']); ?></p>
                        </div>

                        <?php if (!empty($location['service_area'])): ?>
                        <div class="location-details">
                            <h4>Service Area</h4>
                            <p><?php echo htmlspecialchars($location['service_area']); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($location['pickup_instructions']) || !empty($location['dropoff_instructions'])): ?>
                        <div class="location-details">
                            <h4>Instructions</h4>
                            <?php if (!empty($location['pickup_instructions'])): ?>
                                <p><strong>Pickup:</strong> <?php echo htmlspecialchars($location['pickup_instructions']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($location['dropoff_instructions'])): ?>
                                <p><strong>Drop-off:</strong> <?php echo htmlspecialchars($location['dropoff_instructions']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="location-details">
                            <h4>Branch Information</h4>
                            <?php if (!empty($location['branch_phone'])): ?>
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($location['branch_phone']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($location['branch_email'])): ?>
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($location['branch_email']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($location['operating_hours'])): ?>
                                <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($location['operating_hours']); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="location-actions">
                            <button class="btn-edit" onclick='openEditModal(<?php echo json_encode($location); ?>)'>
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-delete" onclick="deleteLocation(<?php echo $location['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Add Location Modal -->
    <div id="addLocationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addLocationModal')">&times;</span>
            <h2>Add New Location</h2>
            <form action="location_settings.php" method="POST">
                <input type="hidden" name="action" value="add_location">
                
                <div class="form-group">
                    <label for="name">Branch Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" required>
                </div>

                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" required>
                </div>

                <div class="form-group">
                    <label for="service_area">Service Area Coverage:</label>
                    <input type="text" id="service_area" name="service_area" placeholder="e.g., Within 10km radius of branch">
                </div>

                <div class="form-group">
                    <label for="pickup_instructions">Pickup Instructions:</label>
                    <textarea id="pickup_instructions" name="pickup_instructions" rows="3" placeholder="Special instructions for car pickup"></textarea>
                </div>

                <div class="form-group">
                    <label for="dropoff_instructions">Drop-off Instructions:</label>
                    <textarea id="dropoff_instructions" name="dropoff_instructions" rows="3" placeholder="Special instructions for car return"></textarea>
                </div>

                <div class="form-group">
                    <label for="branch_phone">Branch Phone:</label>
                    <input type="tel" id="branch_phone" name="branch_phone" placeholder="Contact number">
                </div>

                <div class="form-group">
                    <label for="branch_email">Branch Email:</label>
                    <input type="email" id="branch_email" name="branch_email" placeholder="Branch email address">
                </div>

                <div class="form-group">
                    <label for="operating_hours">Operating Hours:</label>
                    <input type="text" id="operating_hours" name="operating_hours" placeholder="e.g., Mon-Fri: 9AM-6PM">
                </div>

                <div class="form-actions">
                    <button type="button" onclick="closeModal('addLocationModal')" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-save">Save Location</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Location Modal -->
    <div id="editLocationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editLocationModal')">&times;</span>
            <h2>Edit Location</h2>
            <form action="location_settings.php" method="POST">
                <input type="hidden" name="action" value="update_location">
                <input type="hidden" name="location_id" id="edit_location_id">
                
                <div class="form-group">
                    <label for="edit_name">Branch Name:</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="edit_address">Address:</label>
                    <input type="text" id="edit_address" name="address" required>
                </div>

                <div class="form-group">
                    <label for="edit_city">City:</label>
                    <input type="text" id="edit_city" name="city" required>
                </div>

                <div class="form-group">
                    <label for="edit_service_area">Service Area Coverage:</label>
                    <input type="text" id="edit_service_area" name="service_area">
                </div>

                <div class="form-group">
                    <label for="edit_pickup_instructions">Pickup Instructions:</label>
                    <textarea id="edit_pickup_instructions" name="pickup_instructions" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_dropoff_instructions">Drop-off Instructions:</label>
                    <textarea id="edit_dropoff_instructions" name="dropoff_instructions" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_branch_phone">Branch Phone:</label>
                    <input type="tel" id="edit_branch_phone" name="branch_phone">
                </div>

                <div class="form-group">
                    <label for="edit_branch_email">Branch Email:</label>
                    <input type="email" id="edit_branch_email" name="branch_email">
                </div>

                <div class="form-group">
                    <label for="edit_operating_hours">Operating Hours:</label>
                    <input type="text" id="edit_operating_hours" name="operating_hours">
                </div>

                <div class="form-group">
                    <label for="edit_status">Status:</label>
                    <select id="edit_status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="closeModal('editLocationModal')" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-save">Update Location</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openLocationsModal() {
            document.getElementById('locationsModal').style.display = 'block';
        }

        function closeLocationsModal() {
            document.getElementById('locationsModal').style.display = 'none';
        }

        function openAddModal() {
            closeLocationsModal();
            document.getElementById('addLocationModal').style.display = 'block';
        }

        function openEditModal(location) {
            closeLocationsModal();
            const modal = document.getElementById('editLocationModal');
            document.getElementById('edit_location_id').value = location.id;
            document.getElementById('edit_name').value = location.name;
            document.getElementById('edit_address').value = location.address;
            document.getElementById('edit_city').value = location.city;
            document.getElementById('edit_service_area').value = location.service_area || '';
            document.getElementById('edit_pickup_instructions').value = location.pickup_instructions || '';
            document.getElementById('edit_dropoff_instructions').value = location.dropoff_instructions || '';
            document.getElementById('edit_branch_phone').value = location.branch_phone || '';
            document.getElementById('edit_branch_email').value = location.branch_email || '';
            document.getElementById('edit_operating_hours').value = location.operating_hours || '';
            document.getElementById('edit_status').value = location.status;
            modal.style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            openLocationsModal();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                if (event.target.id === 'locationsModal') {
                    closeLocationsModal();
                } else {
                    closeModal(event.target.id);
                }
            }
        }

        function deleteLocation(locationId) {
            if (confirm('Are you sure you want to delete this location?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'location_settings.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_location">
                    <input type="hidden" name="location_id" value="${locationId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 