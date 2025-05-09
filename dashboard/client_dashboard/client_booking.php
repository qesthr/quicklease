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
    <a href="client_profile_userdetails.php" class="nav-btn">PROFILE</a>
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
      <input list="pickup-locations" name="location" placeholder="Pickup Location" required>
<datalist id="pickup-locations">
  <option value="DS CAR RENTAL SERVICES, NATIONAL HIGH WAY, ZONE 1, Malaybalay, 8700 Bukidnon">
  <option value="JJL CAR RENTAL SERVICES AND CARWASH, Magsaysay Ext, Malaybalay, Bukidnon">
  <option value="Shyne's Car Rental, 44PG+V8Q, Malaybalay, Bukidnon">
  <option value="Bukidnon Car Rental, Malaybalay, Bukidnon">
  <option value="Horhe Car Rental and Carwash, Km. 4 Sayre Hwy, Malaybalay, 8700 Bukidnon">
  <option value="BukidnonWheels Car Rental, Grema Village, Malaybalay, 8700 Bukidnon">
  <option value="R-Niel's Car Rental, Purok 8, Sayre Hwy, Barangay 9, Malaybalay, 8700 Bukidnon">
  <option value="ZV Car Rental, P5, Malaybalay, 8700 Bukidnon">
  <option value="Revned Car Rental Services, Malaybalay, Bukidnon">
  <option value="Pren's Car Rental Services, Km. 4 Sayre Hwy, Malaybalay, Bukidnon">
  <option value="CARELLE'S CAR RENTAL, Block 1 Lot 21 Kubayan, Malaybalay, Bukidnon">
  <option value="XZZ Car Rental & Car Care Services - Malaybalay City, Bukidnon (BESIDE BUSECO Malaybalay), Buseco, Malaybalay, Bukidnon">
  <option value="AJC Rides and Car Rental Services, Propia St, Malaybalay, 8700 Bukidnon">
  <option value="KLB Car Rental Malaybalay, 34MW+7GV, P1, Malaybalay, Bukidnon">
</datalist>
        <label><input type="checkbox" checked> Return at the same address</label>
        <input type="date" name="start_date" required>
        <input type="date" name="end_date" required>
        <input type="time" name="start_time" value="09:00">
        <input type="time" name="end_time" value="18:00">
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
            location: document.querySelector('input[placeholder="Pickup Location"]').value,
            bookingDate: document.querySelector('input[name="start_date"]').value,
            bookingTime: document.querySelector('input[name="start_time"]').value,
            returnDate: document.querySelector('input[name="end_date"]').value,
            returnTime: document.querySelector('input[name="end_time"]').value,
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
           `;

          modal.style.display = 'block';
        }
      });

      // Close the modal
      window.closeModal = closeModal;

      // Submit the booking form when the user confirms
      window.submitBooking = function () {
        if (formData) {
          // Get values first
          const location = document.querySelector('input[placeholder="Pickup Location"]').value;
          const startDate = document.querySelector('input[name="start_date"]').value;
          const endDate = document.querySelector('input[name="end_date"]').value;
          const bookingTime = document.querySelector('input[name="start_time"]').value;
          const returnTime = document.querySelector('input[name="end_time"]').value;
          const rate = parseFloat(document.querySelector(`.confirm-button[data-car-id="${currentCarId}"]`).dataset.rate);

          const userId = formData.get('user_id');
          const carId = formData.get('car_id');

          // Validation
          if (!userId || !startDate || !endDate || !location) {
            alert("Missing required fields.");
            return;
          }

          // Add to FormData
          formData.set('location', location);
          formData.set('booking_date', startDate);
          formData.set('return_date', endDate);
          formData.set('booking_time', bookingTime);
          formData.set('return_time', returnTime);
          formData.set('price', rate);

          // Send via AJAX
          const xhr = new XMLHttpRequest();
          xhr.open('POST', 'book_car.php', true);
          xhr.onload = function () {
            console.log('XHR status:', xhr.status);
            console.log('XHR response:', xhr.responseText);
            if (xhr.status === 200) {
              alert('Booking Confirmed');
              closeModal();
              location.reload();
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
