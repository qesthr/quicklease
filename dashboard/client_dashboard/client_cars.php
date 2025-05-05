<?php include '../../db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Cars</title>
    <link rel="stylesheet" href="client_cars.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2>Quick<span>Lease</span></h2>
        </div>
        <a href="client_profile.php" class="nav-btn">PROFILE</a>
        <a href="client_cars.php" class="nav-btn active">CARS</a>
        <a href="client_bookings.php" class="nav-btn">BOOKINGS</a>
        <div class="logout-btn">
            <button>LOGOUT</button>
        </div>
    </div>

    <div class="main">
        <header>
            <h1>CARS</h1>
            <div class="header-icons">
                <i class="bell-icon">🔔</i>
                <img src="../images/car.jpg" class="profile-pic">
            </div>
        </header>

        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php
                // Fetch cars using PDO
                $stmt = $pdo->query("SELECT * FROM car");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="swiper-slide car-card">
                        <h3><?php echo htmlspecialchars($row['model']); ?></h3>
                        <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Car Image">
                        <p>🚗 <?php echo htmlspecialchars($row['seats']); ?> seats</p>
                        <p>🛞 Transmission: <?php echo htmlspecialchars($row['transmission']); ?></p>
                        <p>📍 Mileage: <?php echo htmlspecialchars($row['mileage']); ?> miles</p>
                        <p>₱ <?php echo htmlspecialchars($row['price']); ?>/day</p>
                        <p><strong>Features:</strong> <?php echo htmlspecialchars($row['features']); ?></p>
                        <p>
                            <strong>Availability:</strong>
                            <span class="<?php echo $row['status'] == 'Available' ? 'available' : 'not-available'; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </p>
                        <!-- View Details Button -->
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

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
    </script>
</body>
</html>