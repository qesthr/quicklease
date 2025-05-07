<?php
require_once '../../db.php';
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

    <section class="car-options" id="carOptions">
      <!-- Car cards will be dynamically inserted here -->
    </section>
  </div>
  <!-- Booking Confirmation Modal -->
<div class="modal fade" id="bookingConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="bookingConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Your Booking</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>Car Model:</strong> <span id="confirmCarModel"></span></p>
        <p><strong>Location:</strong> <span id="confirmLocation"></span></p>
        <p><strong>Booking Date:</strong> <span id="confirmBookingDate"></span></p>
        <p><strong>Return Date:</strong> <span id="confirmReturnDate"></span></p>
        <p><strong>Preferences:</strong> <span id="confirmPreferences"></span></p>
        <p><strong>Total Price:</strong> ₱<span id="confirmTotalPrice"></span></p>
        <p><strong>Plate No:</strong> <span id="confirmPlateNo"></span></p>
        <p><strong>Transmission:</strong> <span id="confirmTransmission"></span></p>
        <p><strong>Seats:</strong> <span id="confirmSeats"></span></p>
        <p><strong>Features:</strong> <span id="confirmFeatures"></span></p>
      

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmBookingBtn">Confirm Booking</button>
      </div>
    </div>
  </div>
</div>

  <script>
    // Fetch available cars and render them
    function fetchCars() {
    fetch('fetch_cars.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const carOptions = document.getElementById('carOptions');
          carOptions.innerHTML = '';
  
          data.data.forEach(car => {
            const carCard = document.createElement('div');
            carCard.classList.add('car-card');
            carCard.dataset.carId = car.id;
            carCard.dataset.carModel = car.model;
            carCard.dataset.carRate = car.price;
  
            carCard.innerHTML = `
              <h3>${car.model}</h3>
              <img src="${car.image || 'https://via.placeholder.com/150'}" alt="${car.model}" style="width: 100%; height: 120px; object-fit: cover;">
              <ul class="car-info">
                <li><strong>Plate:</strong> ${car.plate_no}</li>
                <li><strong>Seats:</strong> ${car.seats}</li>
                <li><strong>Transmission:</strong> ${car.transmission}</li>
                <li><strong>Mileage:</strong> ${car.mileage} km</li>
                <li><strong>Features:</strong> ${car.features}</li>
                <li><strong>Price:</strong> ₱${parseFloat(car.price).toFixed(2)}/day</li>
              </ul>
              <button class="book-now">Confirm</button>
            `;
  
            carOptions.appendChild(carCard);
          });
  
          attachBookingListeners();
        } else {
          alert('Failed to fetch cars: ' + data.error);
        }
      })
      .catch(error => {
        console.error('Error fetching cars:', error);
      });
  }
  
    // Function to calculate the total price based on booking dates and car rate
function calculateTotalPrice(startDate, endDate, dailyRate) {
  const start = new Date(startDate);
  const end = new Date(endDate);
  const timeDiff = end - start;
  const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
  return days * dailyRate;
}

// Attach event listeners to booking buttons
function attachBookingListeners() {
  document.querySelectorAll('.book-now').forEach(button => {
    button.addEventListener('click', function () {
      const carCard = this.closest('.car-card');
      const carModel = carCard.dataset.carModel;
      const plateNo = carCard.querySelector('li:nth-child(1)').textContent.replace('Plate:', '').trim();
      const transmission = carCard.querySelector('li:nth-child(3)').textContent.replace('Transmission:', '').trim();
      const seats = carCard.querySelector('li:nth-child(2)').textContent.replace('Seats:', '').trim();
      const features = carCard.querySelector('li:nth-child(5)').textContent.replace('Features:', '').trim();


      if (!location || !bookingDate || !returnDate) {
        alert("Please complete all booking fields.");
        return;
      }

      const totalPrice = calculateTotalPrice(bookingDate, returnDate, dailyRate);

      // Populate modal with booking details
      document.getElementById('confirmCarModel').textContent = carModel;
      document.getElementById('confirmPlateNo').textContent = plateNo;
      document.getElementById('confirmTransmission').textContent = transmission;
      document.getElementById('confirmSeats').textContent = seats;
      document.getElementById('confirmFeatures').textContent = features;


      // Store booking data for submission
      const bookingData = {
        car_model: carModel,
        plate_no: plateNo,
        transmission: transmission,
        seats: seats,
        features: features,
        location: location,
        booking_date: bookingDate,
        return_date: returnDate,
        preferences: preferences,
        total_price: totalPrice
      };

      // Show the confirmation modal
      $('#bookingConfirmationModal').modal('show');

      // Handle confirmation button click
      document.getElementById('confirmBookingBtn').onclick = function () {
        // Submit booking data to the server
        fetch('submit_booking.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(bookingData)
        })
        .then(response => response.text())
        .then(responseText => {
          $('#bookingConfirmationModal').modal('hide');
          if (responseText.trim() === 'success') {
            alert("Booking successful!");
            location.reload();
          } else {
            alert("Booking failed: " + responseText);
          }
        })
        .catch(error => {
          $('#bookingConfirmationModal').modal('hide');
          alert("Error submitting booking: " + error);
        });
      };
    });
  });
}

    // Fetch cars on page load
    fetchCars();

    function logout() {
      window.location.href = 'logout.php';
    }
  </script>
</body>
</html>