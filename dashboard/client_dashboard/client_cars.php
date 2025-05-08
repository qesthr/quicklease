<?php
require_once realpath(__DIR__ . '/../../db.php');
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Cars</title>
    <link rel="stylesheet" href="client_cars.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>
    <style>
        .search-container {
            flex-grow: 1;
            margin: 0 20px;
        }

        .search-container form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-container input[type="text"] {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-container button {
            padding: 8px 20px;
            background: #2216e2;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
        }

        .clear-search {
            color: #e74c3c;
            text-decoration: none;
            font-size: 0.9em;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2>Quick<span>Lease</span></h2>
        </div>
        <a href="client_profile.userdetails.php" class="nav-btn">PROFILE</a>
        <a href="client_cars.php" class="nav-btn active">CARS</a>
        <a href="client_booking.php" class="nav-btn">BOOKINGS</a>
        <div class="logout-btn">
            <button>LOGOUT</button>
        </div>
    </div>

    <div class="main">
        <header>
            <h1>CARS</h1>
            <div class="search-container">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Search car models..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Search</button>
                    <?php if(!empty($search)): ?>
                        <a href="client_cars.php" class="clear-search">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="header-icons">
                <i class="bell-icon">🔔</i>
                <img src="../images/car.jpg" class="profile-pic">
            </div>
        </header>

        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php
                // Fetch cars using PDO
                if (!empty($search)) {
                    $stmt = $pdo->prepare("SELECT * FROM car WHERE model LIKE :search OR transmission LIKE :search");
                    $stmt->execute(['search' => "%$search%"]);
                } else {
                    $stmt = $pdo->query("SELECT * FROM car");
                }

                // Loop through results and display each car
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="swiper-slide car-card">
                        <h3><?php echo htmlspecialchars($row['model']); ?></h3>
                        <img src="../../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Car Image">
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
