<?php
// client_booking.php
require_once realpath(__DIR__ . '/../../db.php');
session_start();
$search = isset($_GET['search']) ? $_GET['search'] : '';
header('Access-Control-Allow-Origin: *'); // Replace * with your frontend domain in production
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Booking</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
  <link rel="stylesheet" href="../../css/client_booking.css">
  <link rel="stylesheet" href="../../css/sidebar.css">
  <link rel="stylesheet" href="../../css/header.css">

</head>
<body>
  <div class="sidebar">
    <div class="logo">
      <h2>Quick<span>Lease</span></h2>
    </div>
    <a href="client_profile.userdetails.php" class="nav-btn">PROFILE</a>
    <a href="client_cars.php" class="nav-btn">CARS</a>
    <a href="client_booking.php" class="nav-btn active">BOOKINGS</a>
    <div class="logout-btn">
      <button>LOGOUT</button>
    </div>
  </div>

  <div class="main">
    <header>
      <h1>BOOKING</h1>
      <div class="header-icons">
        <span>🔔</span>
        <img src="../images/car.jpg" alt="Profile" style="width: 40px; border-radius: 50%;">
      </div>
    </header>

    <div class="booking-section">
      <div class="booking-form-panel">
        <input type="text" placeholder="Location">
        <label><input type="checkbox" checked> Return at the same address</label>
        <input type="date" name="start_date" required>
        <input type="date" name="end_date" required>
        <input type="time" name="start_time" value="09:00">
        <input type="time" name="end_time" value="22:00">
        <textarea placeholder="Default Car or Preferences"></textarea>
      </div>

      <div class="cars-display">
        <?php
        // Update query to show only available cars
        $query = "SELECT * FROM car WHERE status = 'Available'";
        if (!empty($search)) {
          $query .= " AND (model LIKE :search OR transmission LIKE :search)";
          $stmt = $pdo->prepare($query);
          $stmt->execute(['search' => "%$search%"]);
        } else {
          $stmt = $pdo->query($query);
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="car-card">
          <h3><?php echo htmlspecialchars($row['model']); ?></h3>
          <img src="../../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Car Image">
          <div class="icon-row">
            <span>⚙ <?php echo $row['transmission']; ?></span>
            <span>👥 <?php echo $row['seats']; ?> Seats</span>
            <span>⛽ <?php echo $row['mileage']; ?> MPG</span>
            <span>₱  <?php echo $row['price'];?> Rate </span>
          </div>
          <form id="bookingForm-<?php echo $row['id']; ?>" method="POST">
            <input type="hidden" name="car_id" value="<?php echo $row['id']; ?>">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'] ?? 1; ?>">
            <button type="button" class="confirm-button" data-car-id="<?php echo $row['id']; ?>" data-rate="<?php echo $row['price']; ?>">
               Book Now
              Confirm
            </button>
          </form>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>

  <!-- Modal for Booking Confirmation -->
  <div id="bookingModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h2>Booking Overview</h2>
      <p id="booking-details"></p>
      <button onclick="submitBooking()">Confirm</button>
      <button onclick="closeModal()">Cancel</button>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      let currentCarId = null;
      let formData = null;
      // Modal handling
      const modal = document.getElementById('bookingModal');
      const closeModal = () => modal.style.display = 'none';

      // Event delegation for confirm buttons
      document.querySelector('.cars-display').addEventListener('click', e => {
        if (e.target.classList.contains('confirm-button')) {
          currentCarId = e.target.dataset.carId;
          const form = document.getElementById(`bookingForm-${currentCarId}`);

          // Create new FormData from form
          formData = new FormData(form);

          // Collect all booking data for modal display
          const bookingData = {
            carModel: form.closest('.car-card').querySelector('h3').textContent,
            location: document.querySelector('input[placeholder="Location"]').value,
            bookingDate: document.querySelector('input[name="start_date"]').value,
            bookingTime: document.querySelector('input[name="start_time"]').value,
            returnDate: document.querySelector('input[name="end_date"]').value,
            returnTime: document.querySelector('input[name="end_time"]').value,
            preferences: document.querySelector('textarea').value,
            userId: form.querySelector('[name="user_id"]').value,
            carId: form.querySelector('[name="car_id"]').value
          };
          // Calculate total days and total amount
            const rate = parseFloat(e.target.dataset.rate);
            const start = new Date(`${bookingData.bookingDate}T${bookingData.bookingTime}`);
            const end = new Date(`${bookingData.returnDate}T${bookingData.returnTime}`);
            const timeDiff = end - start;
            const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
            const totalAmount = days * rate;


          // Populate modal
          document.getElementById('booking-details').innerHTML = `
            <strong>Car:</strong> ${bookingData.carModel}<br>
            <strong>Location:</strong> ${bookingData.location}<br>
            <strong>Pickup:</strong> ${bookingData.bookingDate} ${bookingData.bookingTime}<br>
            <strong>Return:</strong> ${bookingData.returnDate} ${bookingData.returnTime}<br>
            <strong>Total:</strong> ₱ ${totalAmount.toFixed(2)} (${days} day${days > 1 ? 's' : ''})<br>
            <strong>Preferences:</strong> ${bookingData.preferences}
          `;

          modal.style.display = 'block';
        }
      });

      // Close the modal
      window.closeModal = closeModal;

      // Submit the booking form when the user confirms
      window.submitBooking = function () {
        if (formData) {
          // Capture additional data
          const location = document.querySelector('input[placeholder="Location"]').value;
          const preferences = document.querySelector('textarea').value;
          const startDate = formData.get('start_date');
          const endDate = formData.get('end_date');
          const userId = formData.get('user_id');

          const price = formData.get('price');  // Get rate set on button as data attribute

  
          // Instead of getting dates from formData, get directly from inputs
          const startDateInput = document.querySelector('input[name="start_date"]').value;
          const endDateInput = document.querySelector('input[name="end_date"]').value;
  
          // Validate required fields
          if (!userId) {
            alert('User not logged in or user ID missing.');
            return;
          }
          if (!startDateInput) {
            alert('Please select a booking start date.');
            return;
          }
          if (!endDateInput) {
            alert('Please select a booking end date.');
            return;
          }
          if (!location) {
            alert('Please enter a location.');
            return;
          }
  
          console.log('Submitting booking for user ID:', userId);
  
          // Add these to the FormData object
          formData.set('location', location);
          formData.set('preferences', preferences);
          formData.set('booking_date', startDateInput); // Use direct input value
          formData.set('return_date', endDateInput);
  
          // Append booking_time and return_time
          const bookingTime = document.querySelector('input[name="start_time"]').value;
          const returnTime = document.querySelector('input[name="end_time"]').value;
          formData.set('booking_time', bookingTime);
          formData.set('return_time', returnTime);
  
          // Make AJAX request to save the booking
          const xhr = new XMLHttpRequest();
          xhr.open('POST', 'book_car.php', true);
          xhr.onload = function () {
            console.log('XHR status:', xhr.status);
            console.log('XHR response:', xhr.responseText);
            if (xhr.status === 200) {
              alert('Booking Confirmed');
              closeModal(); // Close the modal after confirmation
              // Optionally, refresh the page or update UI here
            } else {
              alert('Something went wrong. Try again!');
            }
          };
          xhr.send(formData);
        }
      };
    });
  </script>
</body>
</html>
