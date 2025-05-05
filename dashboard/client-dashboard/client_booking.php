<?php include '../../db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Booking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>
    <style>
        /* Sidebar */
        .sidebar {
            width: 200px;
            background-color: #1e2a78;
            color: white;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px 0;
        }
        .sidebar .logo {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .sidebar .logo span {
            color: #ffcc00;
        }
        .sidebar a {
            text-decoration: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            margin: 10px 0;
            display: block;
        }
        .sidebar a.active, .sidebar a:hover {
            background-color: #ffcc00;
            color: black;
        }
        .logout-btn {
            text-align: center;
            margin-bottom: 20px;
        }
        .logout-btn button {
            background-color: orange;
            color: white;
            border: none;
            border-radius: 50%;
            padding: 10px 15px;
            cursor: pointer;
        }

        /* Main Content */
        .main {
            margin-left: 200px;
            padding: 20px;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        header h1 {
            font-size: 28px;
            font-weight: bold;
            text-decoration: underline;
        }
        .header-icons {
            display: flex;
            align-items: center;
        }
        .header-icons i {
            font-size: 24px;
            margin-right: 15px;
        }
        .header-icons img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        /* Booking Form */
        .booking-form {
            background-color: #2d2ba1;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .booking-form h2 {
            margin-bottom: 20px;
        }
        .booking-form label {
            display: block;
            margin-bottom: 5px;
        }
        .booking-form input, .booking-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
        }

        /* Car Swiper */
        .car-swiper {
            margin-top: 20px;
        }
        .swiper-slide {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .swiper-slide img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .car-details {
            display: flex;
            justify-content: space-around;
            margin: 10px 0;
        }
        .car-details p {
            margin: 0;
            font-size: 14px;
        }
        .swiper-slide button {
            background-color: #ffcc00;
            color: black;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .swiper-slide button[disabled] {
            background-color: gray;
            cursor: not-allowed;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .modal-content h2 {
            margin-bottom: 20px;
        }
        .modal-content button {
            background-color: #2d2ba1;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            Quick<span>Lease</span>
        </div>
        <a href="client_profile.php" class="nav-btn">PROFILE</a>
        <a href="client_cars.php" class="nav-btn">CARS</a>
        <a href="client_bookings.php" class="nav-btn active">BOOKINGS</a>
        <div class="logout-btn">
            <button>🔓</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Header -->
        <header>
            <h1>BOOKING</h1>
            <div class="header-icons">
                <i class="bell-icon">🔔</i>
                <img src="../images/user.png" class="profile-pic">
            </div>
        </header>

        <!-- Booking Form -->
        <div class="booking-form">
            <h2>BOOK A CAR</h2>
            <form id="bookingForm">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" placeholder="Enter your location" required>

                <label>
                    <input type="checkbox" name="return_same_address" checked>
                    Return to the same address
                </label>

                <label for="booking_date">Booking Date</label>
                <input type="date" id="booking_date" name="booking_date" required>

                <label for="return_date">Return Date</label>
                <input type="date" id="return_date" name="return_date" required>

                <label for="preferences">Preferences</label>
                <textarea id="preferences" name="preferences" placeholder="Enter any preferences or car requirements"></textarea>
            </form>
        </div>

        <!-- Car Swiper -->
        <div class="car-swiper">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php
                    $stmt = $pdo->query("SELECT * FROM car WHERE status = 'Available'");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <div class="swiper-slide car-card">
                            <h3><?php echo htmlspecialchars($row['model']); ?></h3>
                            <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Car Image">
                            <div class="car-details">
                                <p>₱<?php echo htmlspecialchars($row['price']); ?>/day</p>
                                <p><?php echo htmlspecialchars($row['seats']); ?> Seats</p>
                                <p>Transmission: <?php echo htmlspecialchars($row['transmission']); ?></p>
                                <p>Mileage: <?php echo htmlspecialchars($row['mileage']); ?> MPG</p>                            </div>
                            <button class="confirm-btn" data-car-id="<?php echo $row['id']; ?>" data-car-model="<?php echo htmlspecialchars($row['model']); ?>">Confirm</button>
                        </div>
                    <?php } ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="successModal">
        <div class="modal-content">
            <h2>Successfully Booked!</h2>
            <p>Your booking has been confirmed. Proceed to the chosen car rental.</p>
            <button onclick="closeModal()">Close</button>
        </div>
    </div>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 3,
            spaceBetween: 30,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });

        // Handle Confirm Button Click
        document.querySelectorAll('.confirm-btn').forEach(button => {
            button.addEventListener('click', function () {
                const carId = this.getAttribute('data-car-id');
                const carModel = this.getAttribute('data-car-model');
                const location = document.getElementById('location').value;
                const bookingDate = document.getElementById('booking_date').value;
                const returnDate = document.getElementById('return_date').value;
                const preferences = document.getElementById('preferences').value;

                if (!location || !bookingDate || !returnDate) {
                    alert('Please fill out all required fields.');
                    return;
                }

                // Send booking data to the server
                fetch('../../bookings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add',
                        car_id: carId,
                        car_model: carModel,
                        location: location,
                        booking_date: bookingDate,
                        return_date: returnDate,
                        preferences: preferences,
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success modal
                        document.getElementById('successModal').style.display = 'flex';
                    } else {
                        alert('Failed to book the car. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });

        // Close Modal
        function closeModal() {
            document.getElementById('successModal').style.display = 'none';
        }
    </script>
</body>
</html>