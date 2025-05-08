<?php
// Start session to manage user login state
session_start();
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection - use correct path
require_once realpath(__DIR__ . '/../../db.php');

// Verify connection exists
if(!isset($pdo) || !($pdo instanceof PDO)) {
    die("Database connection failed! Check your db.php settings");
}

// Test connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM car");
    $carCount = $stmt->fetchColumn();
    echo "<!-- Debug: Found $carCount cars in database -->";
} catch(PDOException $e) {
    die("Connection valid but query failed: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>QuickLease Booking Dashboard</title>
  <style>
    /* Styles remain unchanged */
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      height: 100vh;
      background-color: #f3f1e7;
    }

    .sidebar {
      width: 200px;
      background-color: #2216e2;
      color: white;
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .logo {
      font-size: 20px;
      font-weight: bold;
    }

    .logo span {
      color: #ffd500;
    }

    .nav {
      list-style: none;
      padding: 0;
    }

    .nav li {
      margin: 15px 0;
    }

    .nav li a {
      color: white;
      text-decoration: none;
      display: block;
      padding: 8px 12px;
      border-radius: 6px;
      transition: background 0.3s;
    }

    .nav li a:hover {
      background-color: #3c31e5;
    }

    .nav .active a {
      background-color: #ffd500;
      color: #2216e2;
      font-weight: bold;
    }

    .logout {
      background-color: orange;
      color: white;
      border: none;
      padding: 10px;
      border-radius: 20px;
      cursor: pointer;
    }

    .main {
      flex-grow: 1;
      padding: 20px;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .user-icon {
      width: 40px;
      height: 40px;
      background-color: #ccc;
      border-radius: 50%;
    }

    .booking-form {
      margin-top: 20px;
    }

    .form-controls {
      background: white;
      padding: 20px;
      display: grid;
      gap: 15px;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }

    textarea, input {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .car-options {
      display: flex;
      gap: 20px;
      margin-top: 30px;
      flex-wrap: wrap;
    }

    .car-card {
      background-color: white;
      padding: 15px;
      border-radius: 10px;
      width: 200px;
      text-align: center;
    }

    .car-card h3 {
      margin: 10px 0;
    }

    .car-info {
      list-style: none;
      padding: 0;
      margin: 10px 0;
    }

    .car-info li {
      margin: 5px 0;
    }

    .car-card button {
      background-color: #ffc400;
      border: none;
      padding: 10px;
      border-radius: 20px;
      margin-top: 10px;
      cursor: pointer;
    }

    .car-card.unavailable button {
      background-color: #ccc;
      cursor: not-allowed;
    }
     /* Add new modal styles */
     .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 10px;
      width: 400px;
      position: relative;
    }

    .modal-close {
      position: absolute;
      top: 10px;
      right: 10px;
      cursor: pointer;
      font-weight: bold;
    }

    .modal-details {
      margin: 15px 0;
    }

    .modal-details img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }

    .modal-buttons {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 20px;
    }

    .modal-buttons button {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .confirm-btn {
      background-color: #4CAF50;
      color: white;
    }

    .cancel-btn {
      background-color: #f44336;
      color: white;
    }

    /* Add price display */
    .car-price {
      font-weight: bold;
      color: #2ecc71;
      margin: 10px 0;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="logo">Quick<span>Lease</span></div>
    <ul class="nav">
      <li><a href="client_profile.userdetails.php">Profile</a></li>
      <li><a href="client_cars.php">Cars</a></li>
      <li class="active"><a href="#">Bookings</a></li>
    </ul>
    <button class="logout" onclick="logout()">Logout</button>
  </div>

  <div class="main">
    <header>
      <h1>BOOKING</h1>
      <div class="user-icon"></div>
    </header>

    <section class="booking-form">
      <h2>BOOK A CAR</h2>
      <div class="form-controls">
        <input type="text" name="location" id="location" placeholder="Location" required>
        <div>
          <input type="date" name="booking_date" id="bookingDate" required>
          <input type="date" name="return_date" id="returnDate" required>
        </div>
        <textarea name="preferences" id="preferences" placeholder="Default Car or Preferences"></textarea>
      </div>
    </section>

    <!-- Add modal structure -->
  <div class="modal-overlay" id="bookingModal">
    <div class="modal-content">
      <span class="modal-close" onclick="closeModal()">&times;</span>
      <h2>Confirm Booking</h2>
      <div class="modal-details" id="modalDetails">
        <!-- Dynamically populated -->
      </div>
      <div class="modal-buttons">
        <button class="cancel-btn" onclick="closeModal()">Cancel</button>
        <button class="confirm-btn" id="finalConfirm">Confirm</button>
      </div>
    </div>
  </div>

    <section class="car-options" id="carOptions">
      <!-- Car cards will be dynamically inserted here -->
    </section>

  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
     // Modified fetchCars function
     async function fetchCars() {
    try {
        const response = await fetch('/quicklease/dashboard/client_dashboard/fetch_cars.php');
        const data = await response.json();
        
        console.log('API Response:', data); // Debug output

        if (data.status !== 'success') {
            console.error('Server Error:', data.message);
            alert('Error loading cars: ' + (data.message || 'Unknown error'));
            return;
        }

        const carOptions = document.getElementById('carOptions');
        carOptions.innerHTML = '';
        
        if(data.data.length === 0) {
            carOptions.innerHTML = '<p>No available cars found</p>';
            return;
        }

        data.data.forEach(car => {
            const carCard = document.createElement('div');
            carCard.classList.add('car-card');
            carCard.dataset.carId = car.id;
            carCard.dataset.dailyPrice = car.price;

            carCard.innerHTML = `
                <h3>${car.model}</h3>
                <img src="/uploads/${car.image}" alt="${car.model}" style="height:150px;object-fit:cover;">
                <div class="car-price">₱${car.price.toFixed(2)}/day</div>
                <ul class="car-info">
                    <li>${car.transmission}</li>
                    <li>${car.seats} Seats</li>
                    <li>${car.mileage} km</li>
                </ul>
                <button class="book-now">Book Now</button>
            `;
            
            carOptions.appendChild(carCard);
        });
        attachBookingListeners();
    } catch (error) {
        console.error('Network Error:', error);
        alert('Failed to load cars. Check console for details.');
    }
}

    // Modified booking handler
    function attachBookingListeners() {
      document.querySelectorAll('.book-now').forEach(button => {
        button.addEventListener('click', function() {
          const carCard = this.closest('.car-card');
          const bookingData = {
            carId: carCard.dataset.carId,
            model: carCard.querySelector('h3').textContent,
            image: carCard.querySelector('img').src,
            dailyPrice: carCard.dataset.dailyPrice,
            location: document.getElementById('location').value,
            bookingDate: document.getElementById('bookingDate').value,
            returnDate: document.getElementById('returnDate').value,
            preferences: document.getElementById('preferences').value
          };

          if (!validateBooking(bookingData)) return;
          
          const totalDays = calculateDays(bookingData.bookingDate, bookingData.returnDate);
          bookingData.totalPrice = totalDays * bookingData.dailyPrice;
          
          showBookingModal(bookingData);
        });
      });
    }

    function validateBooking(data) {
      if (!data.location || !data.bookingDate || !data.returnDate) {
        alert('Please fill all required fields');
        return false;
      }
      if (new Date(data.returnDate) <= new Date(data.bookingDate)) {
        alert('Return date must be after booking date');
        return false;
      }
      return true;
    }

    function calculateDays(start, end) {
      const oneDay = 24 * 60 * 60 * 1000;
      return Math.round(Math.abs((new Date(end) - new Date(start)) / oneDay));
    }

    function showBookingModal(data) {
      const modalDetails = document.getElementById('modalDetails');
      modalDetails.innerHTML = `
        <img src="${data.image}" alt="${data.model}">
        <h3>${data.model}</h3>
        <p><strong>Pickup Location:</strong> ${data.location}</p>
        <p><strong>Booking Dates:</strong> ${data.bookingDate} to ${data.returnDate}</p>
        <p><strong>Total Days:</strong> ${calculateDays(data.bookingDate, data.returnDate)}</p>
        <p><strong>Total Price:</strong> ₱${data.totalPrice.toFixed(2)}</p>
        ${data.preferences ? `<p><strong>Preferences:</strong> ${data.preferences}</p>` : ''}
      `;
      
      document.getElementById('bookingModal').style.display = 'flex';
      
      // Handle final confirmation
      document.getElementById('finalConfirm').onclick = () => submitBooking(data);
    }

    async function submitBooking(data) {
      const formData = new FormData();
      formData.append('car_id', data.carId);
      formData.append('location', data.location);
      formData.append('booking_date', data.bookingDate);
      formData.append('return_date', data.returnDate);
      formData.append('preferences', data.preferences);
      formData.append('total_price', data.totalPrice);

      try {
        const response = await fetch('submit_booking.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.text();
        
        if (result === 'success') {
          alert('Booking confirmed!');
          closeModal();
          fetchCars(); // Refresh available cars
        } else {
          alert('Booking failed: ' + result);
        }
      } catch (error) {
        alert('Error: ' + error.message);
      }
    }

    function closeModal() {
      document.getElementById('bookingModal').style.display = 'none';
    }

    // Initialize on load
    fetchCars();
  </script>

  <script>
    async function fetchClientBookings() {
      try {
        const response = await fetch('/dashboard/client_dashboard/fetch_client_bookings.php');
        const result = await response.json();

        if (result.status !== 'success') {
          console.error('Error fetching bookings:', result.message);
          document.getElementById('clientBookingsContainer').innerHTML = '<p>Error loading bookings.</p>';
          return;
        }

        const bookings = result.data;
        const container = document.getElementById('clientBookingsContainer');
        container.innerHTML = '';

        if (bookings.length === 0) {
          container.innerHTML = '<p>No bookings found.</p>';
          return;
        }

        bookings.forEach(booking => {
          const bookingDiv = document.createElement('div');
          bookingDiv.classList.add('booking-card');
          bookingDiv.innerHTML = `
            <h3>${booking.car_model}</h3>
            <p><strong>Location:</strong> ${booking.location}</p>
            <p><strong>Booking Date:</strong> ${booking.booking_date}</p>
            <p><strong>Return Date:</strong> ${booking.return_date}</p>
            <p><strong>Status:</strong> ${booking.status}</p>
            <p><strong>Preferences:</strong> ${booking.preferences || 'None'}</p>
            <p><strong>Total Price:</strong> ₱${booking.total_price.toFixed(2)}</p>
          `;
          container.appendChild(bookingDiv);
        });
      } catch (error) {
        console.error('Fetch error:', error);
        document.getElementById('clientBookingsContainer').innerHTML = '<p>Error loading bookings.</p>';
      }
    }

    // Fetch client bookings on page load
    fetchClientBookings();
  </script>
</body>
</html>
