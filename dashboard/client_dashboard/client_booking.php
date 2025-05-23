<?php
// client_booking.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/session_handler.php';
require_once '../../db.php';

// Start client session
startClientSession();

// Check if user is logged in and is a client
if (!isClient()) {
    $_SESSION['error'] = "Please log in as a client.";
    header("Location: ../../loginpage/login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;

// Fetch client data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: ../../loginpage/login.php");
    exit();
}

// Check if user is logged in as client
requireClient();

// Handle booking submission via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_booking') {
    header('Content-Type: application/json');
    
    try {
        // Debug: Log the received POST data
        error_log("Received POST data: " . print_r($_POST, true));

        // Validate input
        $car_id = isset($_POST['car_id']) ? intval($_POST['car_id']) : null;
        $location = isset($_POST['location']) ? trim($_POST['location']) : null;
        $booking_date = isset($_POST['booking_date']) ? trim($_POST['booking_date']) : null;
        $return_date = isset($_POST['return_date']) ? trim($_POST['return_date']) : null;
        $status = 'Pending';
        $users_id = $_SESSION['user_id'] ?? null;

        // Debug: Log the processed variables
        error_log("Processed variables:");
        error_log("car_id: $car_id");
        error_log("location: $location");
        error_log("booking_date: $booking_date");
        error_log("return_date: $return_date");
        error_log("users_id: $users_id");

        // Check each field individually and create detailed error message
        $missing_fields = [];
        if (!$car_id) $missing_fields[] = 'Car';
        if (!$location) $missing_fields[] = 'Location';
        if (!$booking_date) $missing_fields[] = 'Booking Date';
        if (!$return_date) $missing_fields[] = 'Return Date';
        if (!$users_id) $missing_fields[] = 'User ID';

        if (!empty($missing_fields)) {
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }

        // Validate dates
        if (!strtotime($booking_date) || !strtotime($return_date)) {
            throw new Exception("Invalid booking or return date.");
        }

        if (strtotime($return_date) <= strtotime($booking_date)) {
            throw new Exception("Return date must be after the booking date.");
        }

        if (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
            throw new Exception("Booking date cannot be in the past.");
        }

        // Check if car is available
        $stmt = $pdo->prepare("SELECT status FROM car WHERE id = ?");
        $stmt->execute([$car_id]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car || $car['status'] !== 'Available') {
            throw new Exception("Selected car is not available for booking.");
        }

        // Start transaction
        $pdo->beginTransaction();

        // Insert booking
        $stmt = $pdo->prepare("INSERT INTO bookings (users_id, car_id, location, booking_date, return_date, status) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$users_id, $car_id, $location, $booking_date, $return_date, $status]);

        // Insert notification for the new booking
        $booking_id = $pdo->lastInsertId();
        $notification_message = "Your booking for this car has been received and is pending approval.";
        $notification_type = "booking_status_update";

        $stmt = $pdo->prepare("INSERT INTO notifications (users_id, booking_id, message, notification_type, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        $stmt->execute([
            $users_id,
            $booking_id,
            $notification_message,
            $notification_type
        ]);
        if ($stmt->rowCount() === 0) {
            error_log('Notification insert failed for booking_id: ' . $booking_id);
        }

        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Booking submitted successfully! Please wait for approval.'
        ]);
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction if active
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Test database connection
try {
    $test_query = $pdo->query("SELECT 1");
    error_log("Database connection successful");
    
    // Test bookings table
    $test_bookings = $pdo->query("SHOW TABLES LIKE 'bookings'")->rowCount();
    error_log("Bookings table exists: " . ($test_bookings > 0 ? 'yes' : 'no'));
    
    if ($test_bookings > 0) {
        // Check table structure
        $columns = $pdo->query("SHOW COLUMNS FROM bookings")->fetchAll(PDO::FETCH_COLUMN);
        error_log("Bookings table columns: " . implode(', ', $columns));
    }
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection failed. Please check the error logs.");
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
header('Access-Control-Allow-Origin: '); // Replace * with your frontend domain in production
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Booking</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
  <link rel="stylesheet" href="../../css/client.css">
  <link rel="stylesheet" href="../../css/client_booking.css">


</head>
<body class="client-booking">
  <?php include __DIR__ . '/../client_dashboard/includes/sidebar.php'; ?>

  <div class="client-booking-topbar">
    <?php include __DIR__ . '/../client_dashboard/includes/topbar.php'; ?>
  </div>

    <div class="content-wrapper">
      <?php
      // Fetch user's booking history
      $user_id = $_SESSION['user_id'] ?? 1;
      try {
          $stmt = $pdo->prepare("
              SELECT 
                  b.id,
                  b.car_id,
                  c.model AS car_model,
                  b.location,
                  b.booking_date,
                  b.return_date,
                  b.status,
                  c.price,
                  DATEDIFF(b.return_date, b.booking_date) * c.price as total_cost
              FROM bookings b
              JOIN car c ON b.car_id = c.id
              WHERE b.users_id = ?
              ORDER BY b.booking_date DESC
          ");
          
          $stmt->execute([$user_id]);
          $booking_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
      } catch (PDOException $e) {
          error_log("Database error: " . $e->getMessage());
          echo '<div class="alert alert-danger">Error fetching booking history. Please try again later.</div>';
      }
      ?>

      <div class="content-header">
        <h2>My Bookings</h2>
        <button class="add-booking-btn" onclick="openAddBookingModal()">
          <i class="fas fa-plus"></i> Add New Booking
        </button>
      </div>

      <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
      <?php endif; ?>

      <div class="table-container">
        <table class="booking-table">
          <thead>
            <tr>
              <th>Booking ID</th>
              <th>Car Model</th>
              <th>Location</th>
              <th>Booking Date</th>
              <th>Return Date</th>
              <th>Total Cost</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($booking_history)): ?>
              <tr>
                <td colspan="8" class="no-data">No booking history found.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($booking_history as $booking): ?>
                <tr>
                  <td>#<?= htmlspecialchars($booking['id']) ?></td>
                  <td><?= htmlspecialchars($booking['car_model']) ?></td>
                  <td><?= htmlspecialchars($booking['location']) ?></td>
                  <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                  <td><?= date('M d, Y', strtotime($booking['return_date'])) ?></td>
                  <td>₱<?= number_format($booking['total_cost'], 2) ?></td>
                  <td>
                    <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                      <?= htmlspecialchars($booking['status']) ?>
                    </span>
                  </td>
                  <td class="action-buttons">
                    <?php if ($booking['status'] !== 'Cancelled' && $booking['status'] !== 'Completed'): ?>
                      <button class="edit-btn" onclick='openEditModal(<?= json_encode($booking, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                      <button class="cancel-btn" onclick="cancelBooking(<?= htmlspecialchars($booking['id']) ?>)">Cancel</button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal for Booking Confirmation -->
  <div id="bookingModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h2>Booking Overview</h2>
      <div id="booking-details"></div>
      <div class="modal-footer">
        <button class="book-now-btn" onclick="submitBooking()">Confirm</button>
        <button class="cancel-modal-btn" onclick="closeModal()">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Add Booking Modal -->
  <div id="addBookingModal" class="modal">
    <div class="modal-content booking-modal">
      <span class="close" onclick="closeAddBookingModal()">&times;</span>
      <h2>BOOK A CAR</h2>
      
      <div class="booking-form">
        <div class="location-section">
          <div class="input-group">
            <span class="icon">📍</span>
            <select name="location" id="location" required>
              <option value="">Select location</option>
              <?php
              // Read locations from JSON file
              $locations_file = __DIR__ . '/../data/locations.json';
              try {
                  $json_data = file_get_contents($locations_file);
                  $data = json_decode($json_data, true);
                  
                  // Filter and display only active locations
                  foreach ($data['locations'] as $location) {
                      if ($location['status'] === 'Active') {
                          $display_text = $location['name'] . ', ' . $location['address'] . ', ' . $location['city'];
                          echo '<option value="' . htmlspecialchars($location['name']) . '">' . 
                               htmlspecialchars($display_text) . '</option>';
                      }
                  }
              } catch (Exception $e) {
                  error_log("Error loading locations: " . $e->getMessage());
              }
              ?>
            </select>
          </div>
          <label class="return-checkbox">
            <input type="checkbox" checked> Return at the same address
          </label>
        </div>

        <div class="date-sections">
          <div class="date-section start-date">
            <h3>Start Date & Time</h3>
            <div class="input-group">
              <span class="icon">📅</span>
              <input type="date" name="start_date" id="start_date" required>
            </div>
            <div class="input-group">
              <span class="icon">🕒</span>
              <select name="start_hour" id="start_hour" required>
                <?php
                for($i = 0; $i < 24; $i++) {
                    $hour = str_pad($i, 2, "0", STR_PAD_LEFT);
                    echo "<option value='$hour'>$hour:00</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="date-section return-date">
            <h3>Return Date & Time</h3>
            <div class="input-group">
              <span class="icon">📅</span>
              <input type="date" name="return_date" id="return_date" required>
            </div>
            <div class="input-group">
              <span class="icon">🕒</span>
              <select name="return_hour" id="return_hour" required>
                <?php
                for($i = 0; $i < 24; $i++) {
                    $hour = str_pad($i, 2, "0", STR_PAD_LEFT);
                    echo "<option value='$hour'>$hour:00</option>";
                }
                ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="cars-grid">
        <?php
        // Update query to show only available cars
        $query = "SELECT * FROM car WHERE status = 'Available'";
        if (!empty($search)) {
            $query .= " AND (model LIKE :search 
                       OR transmission LIKE :search 
                       OR features LIKE :search 
                       OR CONCAT(model, ' ', transmission, ' ', features) LIKE :search)";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt = $pdo->query($query);
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="car-card">
          <div class="car-header">
            <h3><?php echo htmlspecialchars($row['model']); ?></h3>
            <span class="plate-number">Plate No: <?php echo htmlspecialchars($row['plate_no']); ?></span>
          </div>
          <img src="../../uploads/<?php echo htmlspecialchars($row['image']) ?: 'default.jpg'; ?>" alt="Car Image">
          <div class="car-features">
            <div class="feature">
              <span class="icon">⚙️</span>
              <span><?php echo $row['transmission']; ?></span>
            </div>
            <div class="feature">
              <span class="icon">👥</span>
              <span><?php echo $row['seats']; ?> Seats</span>
            </div>
            <div class="feature">
              <span class="icon">⛽</span>
              <span><?php echo $row['mileage']; ?> MPG</span>
            </div>
            <div class="feature">
              <span class="icon">💰</span>
              <span>₱<?php echo number_format($row['price'], 2); ?>/day</span>
            </div>
          </div>
          <form id="bookingForm-<?php echo $row['id']; ?>" method="POST">
            <input type="hidden" name="car_id" value="<?php echo $row['id']; ?>">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'] ?? 1; ?>">
            <button type="button" class="confirm-button" data-car-id="<?php echo $row['id']; ?>" data-rate="<?php echo $row['price']; ?>">
              Book Now
            </button>
          </form>
          <p class="booking-status">Confirm your booking</p>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>

  <!-- Add Edit Booking Modal -->
  <div id="editBookingModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">&times;</span>
      <h2>Edit Booking</h2>
      <div class="booking-form-panel">
        <form id="editBookingForm">
          <input type="hidden" name="booking_id" id="edit_booking_id">
          <input type="hidden" name="car_id" id="edit_car_id">
          
          <div class="form-group">
            <label>Location:</label>
            <input list="pickup-locations" name="location" id="edit_location" required>
          </div>
          
          <div class="form-group">
            <label>Booking Date:</label>
            <input type="date" name="booking_date" id="edit_booking_date" required>
          </div>
          
          <div class="form-group">
            <label>Return Date:</label>
            <input type="date" name="return_date" id="edit_return_date" required>
          </div>
          
          <div class="button-group">
            <button type="button" onclick="submitEditBooking()" class="save-btn">Save Changes</button>
            <button type="button" onclick="closeEditModal()" class="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Success Modal -->
  <div id="successModal" class="modal">
    <div class="modal-content success-modal">
      <div class="success-icon">✓</div>
      <h2>Booking Successful!</h2>
      <p>Your booking has been submitted successfully.</p>
      <p>Please wait for approval from our team.</p>
      <button class="ok-button" onclick="closeSuccessModal()">OK</button>
    </div>
  </div>
  
  <!-- Add Flatpickr for date range picker -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      let selectedCarId = null;
      let selectedCarRate = null;

      // Function to open booking modal
      window.openAddBookingModal = function() {
        document.getElementById('addBookingModal').style.display = 'block';
      };

      // Check URL parameters
      const urlParams = new URLSearchParams(window.location.search);
      const shouldOpenModal = urlParams.get('open_modal');
      const selectedCarFromUrl = urlParams.get('selected_car');

      // If redirected from car details, open the modal
      if (shouldOpenModal === 'true') {
        // First open the modal
        openAddBookingModal();
        
        // If a specific car was selected
        if (selectedCarFromUrl) {
          // Wait for modal content to load
          setTimeout(() => {
            // Find the car's confirm button
            const confirmButton = document.querySelector(`.confirm-button[data-car-id="${selectedCarFromUrl}"]`);
            if (confirmButton) {
              // Scroll to the car
              confirmButton.scrollIntoView({ behavior: 'smooth', block: 'center' });
              
              // Add highlight effect to the car card
              const carCard = confirmButton.closest('.car-card');
              if (carCard) {
                carCard.classList.add('highlighted');
                setTimeout(() => {
                  carCard.classList.remove('highlighted');
                }, 2000);
              }
            }
          }, 300);
        }
      }

      // Add showToast function
      function showToast(message) {
        // Close all modals first
        document.querySelectorAll('.modal').forEach(modal => {
          modal.style.display = 'none';
        });

        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
          toast.remove();
        }, 3000);
      }

      // Function to handle car selection
      const handleCarSelection = (carId, rate) => {
        selectedCarId = carId;
        selectedCarRate = rate;
        
        // Get booking details
        const location = document.getElementById('location').value;
        const startDate = document.getElementById('start_date').value;
        const startHour = document.getElementById('start_hour').value;
        const returnDate = document.getElementById('return_date').value;
        const returnHour = document.getElementById('return_hour').value;

        // Validate inputs
        if (!location || !startDate || !startHour || !returnDate || !returnHour) {
          showToast('Please fill in all booking details first');
          return;
        }

        // Format dates for display
        const bookingDateTime = `${startDate} ${startHour}:00`;
        const returnDateTime = `${returnDate} ${returnHour}:00`;

        // Calculate total days and cost
        const start = new Date(startDate);
        const end = new Date(returnDate);
        const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
        const totalCost = days * rate;

        // Get car details
        const carCard = document.querySelector(`#bookingForm-${carId}`).closest('.car-card');
        const carModel = carCard.querySelector('h3').textContent;
        const plateNo = carCard.querySelector('.plate-number').textContent;

        // Show booking modal with details
        const bookingDetails = document.getElementById('booking-details');
        bookingDetails.innerHTML = `
          <div class="booking-summary">
            <div class="summary-item">
              <strong>Car Model:</strong> ${carModel}
            </div>
            <div class="summary-item">
              <strong>Plate Number:</strong> ${plateNo}
            </div>
            <div class="summary-item">
              <strong>Pick-up Location:</strong> ${location}
            </div>
            <div class="summary-item">
              <strong>Booking Date:</strong> ${bookingDateTime}
            </div>
            <div class="summary-item">
              <strong>Return Date:</strong> ${returnDateTime}
            </div>
            <div class="summary-item">
              <strong>Duration:</strong> ${days} day${days > 1 ? 's' : ''}
            </div>
            <div class="summary-item total-cost">
              <strong>Total Cost:</strong> ₱${totalCost.toFixed(2)}
            </div>
          </div>
        `;

        // Show the booking modal
        document.getElementById('bookingModal').style.display = 'block';
        // Hide the add booking modal
        document.getElementById('addBookingModal').style.display = 'none';
      };

      // Add click event listeners to all confirm buttons
      document.querySelectorAll('.confirm-button').forEach(button => {
        button.addEventListener('click', () => {
          handleCarSelection(button.dataset.carId, parseFloat(button.dataset.rate));
        });
      });

      // Update submitBooking function
      window.submitBooking = function() {
        if (!selectedCarId) {
          showToast('Please select a car first');
          return;
        }

        // Get all form values
        const location = document.getElementById('location').value;
        const startDate = document.getElementById('start_date').value;
        const startHour = document.getElementById('start_hour').value;
        const returnDate = document.getElementById('return_date').value;
        const returnHour = document.getElementById('return_hour').value;

        // Validate all fields are filled
        if (!location || !startDate || !startHour || !returnDate || !returnHour) {
          showToast('Please fill in all booking details');
          return;
        }

        // Create booking datetime strings
        const bookingDateTime = `${startDate} ${startHour}:00:00`;
        const returnDateTime = `${returnDate} ${returnHour}:00:00`;

        const formData = new FormData();
        formData.append('action', 'submit_booking');
        formData.append('car_id', selectedCarId);
        formData.append('location', location);
        formData.append('booking_date', bookingDateTime);
        formData.append('return_date', returnDateTime);

        // Send booking request
        fetch('client_booking.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          // Close all modals before showing the success modal
          document.querySelectorAll('.modal').forEach(modal => {
            if (modal.id !== 'successModal') {
              modal.style.display = 'none';
            }
          });

          if (data.success) {
            // Show success modal
            document.getElementById('successModal').style.display = 'block';
            
            // Reload page after clicking OK button
            window.closeSuccessModal = function() {
              document.getElementById('successModal').style.display = 'none';
              location.reload();
            };
          } else {
            showToast(data.message || 'Error creating booking');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error creating booking. Please try again.');
        });
      };

      // Update closeModal function
      window.closeModal = function() {
        const bookingModal = document.getElementById('bookingModal');
        const addBookingModal = document.getElementById('addBookingModal');
        
        bookingModal.style.display = 'none';
        addBookingModal.style.display = 'block'; // Show the car selection modal again
        
        // Clear any existing form data
        selectedCarId = null;
        selectedCarRate = null;
      };

      // Close modal when clicking outside
      window.onclick = function(event) {
        if (event.target === addBookingModal) {
          closeAddBookingModal();
        } else if (event.target === bookingModal) {
          closeModal();
        }
      };

      // Close modal when clicking the close button
      document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.onclick = function() {
          this.closest('.modal').style.display = 'none';
        };
      });

      // Edit booking functions
      window.openEditModal = function(bookingData) {
        const editModal = document.getElementById('editBookingModal');
        document.getElementById('edit_booking_id').value = bookingData.id;
        document.getElementById('edit_car_id').value = bookingData.car_id;
        document.getElementById('edit_location').value = bookingData.location;
        
        // Format dates for input fields (YYYY-MM-DD)
        const bookingDate = new Date(bookingData.booking_date);
        const returnDate = new Date(bookingData.return_date);
        
        document.getElementById('edit_booking_date').value = bookingDate.toISOString().split('T')[0];
        document.getElementById('edit_return_date').value = returnDate.toISOString().split('T')[0];
        
        editModal.style.display = 'block';
      };

      window.closeEditModal = function() {
        document.getElementById('editBookingModal').style.display = 'none';
      };

      window.submitEditBooking = function() {
        const form = document.getElementById('editBookingForm');
        const formData = new FormData(form);
        
        // Validate dates
        const startDate = new Date(formData.get('booking_date'));
        const endDate = new Date(formData.get('return_date'));
        
        if (endDate <= startDate) {
          showToast('Return date must be after booking date');
          return;
        }

        // Send AJAX request
        fetch('update_booking.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast(data.message);
            closeEditModal();
            setTimeout(() => {
              location.reload();
            }, 1500);
          } else {
            showToast(data.message || 'Error updating booking');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error updating booking. Please try again.');
        });
      };

      // Cancel booking function
      window.cancelBooking = function(bookingId) {
        if (!confirm('Are you sure you want to cancel this booking?')) {
          return;
        }

        const formData = new FormData();
        formData.append('booking_id', bookingId);

        fetch('cancel_booking.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast(data.message);
            setTimeout(() => {
              location.reload();
            }, 1500);
          } else {
            showToast(data.message || 'Error cancelling booking');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error cancelling booking. Please try again.');
        });
      };

      // Get modal elements
      const addBookingModal = document.getElementById('addBookingModal');
      const bookingModal = document.getElementById('bookingModal');
      
      // Function to open add booking modal
      window.openAddBookingModal = function() {
        addBookingModal.style.display = 'block';
      };

      // Function to close add booking modal
      window.closeAddBookingModal = function() {
        addBookingModal.style.display = 'none';
      };

      // Add styles for toast notification
      const style = document.createElement('style');
      style.textContent = `
        .toast-notification {
          position: fixed;
          top: 20px;
          right: 20px;
          background-color: #4CAF50;
          color: white;
          padding: 15px 25px;
          border-radius: 8px;
          z-index: 10000;
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
          animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
          from {
            transform: translateX(100%);
            opacity: 0;
          }
          to {
            transform: translateX(0);
            opacity: 1;
          }
        }
      `;
    });
  </script>
</body>
</html>