<?php
echo realpath('../../db.php');
exit; ?>
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
      <li><a href="client_profile.userdetails.html">Profile</a></li>
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

  <script>
    // Fetch available cars and render them
    function fetchCars() {
      fetch('fetch_cars.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const carOptions = document.getElementById('carOptions');
            carOptions.innerHTML = ''; // Clear existing cars

            data.data.forEach(car => {
              const carCard = document.createElement('div');
              carCard.classList.add('car-card');
              carCard.dataset.carModel = car.model;

              carCard.innerHTML = `
                <h3>${car.model}</h3>
                <img src="${car.image_url || 'https://via.placeholder.com/150'}" alt="${car.model}">
                <ul class="car-info">
                  <li>${car.transmission}</li>
                  <li>${car.seats} Seats</li>
                  <li>${car.mpg} MPG</li>
                </ul>
                <button class="book-now">Confirm</button>
              `;

              carOptions.appendChild(carCard);
            });

            // Attach event listeners to the new buttons
            attachBookingListeners();
          } else {
            alert('Failed to fetch cars: ' + data.error);
          }
        })
        .catch(error => {
          console.error('Error fetching cars:', error);
        });
    }

    // Attach event listeners to booking buttons
    function attachBookingListeners() {
      document.querySelectorAll('.book-now').forEach(button => {
        button.addEventListener('click', function () {
          const carModel = this.closest('.car-card').dataset.carModel;
          const location = document.getElementById('location').value;
          const bookingDate = document.getElementById('bookingDate').value;
          const returnDate = document.getElementById('returnDate').value;
          const preferences = document.getElementById('preferences').value;

          if (!location || !bookingDate || !returnDate) {
            alert("Please complete all booking fields.");
            return;
          }

          const formData = new FormData();
          formData.append('location', location);
          formData.append('car_model', carModel);
          formData.append('booking_date', bookingDate);
          formData.append('return_date', returnDate);
          formData.append('preferences', preferences);

          fetch('submit_booking.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.text())
          .then(response => {
            if (response.trim() === 'success') {
              alert("Booking successful!");
              location.reload();
            } else {
              alert("Booking failed: " + response);
            }
          })
          .catch(error => {
            alert("Error submitting booking: " + error);
          });
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