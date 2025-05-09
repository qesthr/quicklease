<?php
require_once realpath(__DIR__ . '/../../db.php');
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client | Cars Catalogue</title>

    <link rel="stylesheet" href="../../css/client.css">
    <link rel="stylesheet" href="../../css/client_cars.css">
    
    <link rel="preload" href="https://kit.fontawesome.com/b7bdbf86fb.js" as="script">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>

    <!--lenk to fontawesome-->
    <script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>
</head>

<body class="client-cars-body">
    <?php include '../client_dashboard/includes/sidebar.php'; ?>

    <div class="main">
        <header>
            <?php include '../client_dashboard/includes/topbar.php'; ?>
        </header>

        <div class="card-container">
            <div class="swiper mySwiper">
                
                <div class="swiper-wrapper"> <!-- Changed from 'card' to 'swiper-wrapper' for proper Swiper.js structure -->
                    <?php
                    // Fetch cars using PDO
                    if (!empty($search)) {
                        $stmt = $pdo->prepare("SELECT * FROM car WHERE model LIKE :search OR transmission LIKE :search");
                        $stmt->execute(['search' => "%$search%"]);
                    } else {
                        $stmt = $pdo->query("SELECT * FROM car");
                    }

                    // Loop through results and display each car
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="swiper-slide car-card">
                            <!-- Card Header -->
                            <div class="car-card-header">
                                <h3 class="car-model"><?= htmlspecialchars($row['model']) ?></h3>
                                <span class="availability-badge <?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </div>
                            
                            <!-- Main Image -->
                            <div class="car-image-wrapper">
                                <img src="../../uploads/<?= htmlspecialchars($row['image']) ?>" 
                                    alt="<?= htmlspecialchars($row['model']) ?>" 
                                    class="car-main-image">
                            </div>
                            
                            <!-- Specifications Grid -->
                            <div class="car-specs-grid">
                                <div class="spec-item">
                                    <span class="spec-icon"><i class="fas fa-car"></i></span>
                                    <span class="spec-text"><?= htmlspecialchars($row['seats']) ?> seats</span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-icon"><i class="fas fa-cogs"></i></span>
                                    <span class="spec-text"><?= htmlspecialchars($row['transmission']) ?></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-icon"><i class="fa-solid fa-gas-pump"></i></span>
                                    <span class="spec-text"><?= htmlspecialchars($row['mileage']) ?> miles</span>
                                </div>
                            </div>
                            
                            <!-- Pricing Section -->
                            <div class="car-pricing">
                                <span class="price-amount">₱<?= number_format($row['price'], 2) ?></span>
                                <span class="price-period">per day</span>
                            </div>
                            
                            <!-- Features Section -->
                            <div class="car-features">
                                <h4 class="features-heading">Features</h4>
                                <p class="features-text"><?= htmlspecialchars($row['features']) ?></p>
                            </div>
                            
                            <!-- Action Button -->
                            <button class="view-details-btn" data-car-id="<?= $row['id'] ?>">
                                View Details
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>              
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1, // Default for mobile
            spaceBetween: 20,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                // When window width is >= 768px
                768: {
                    slidesPerView: 2,
                    spaceBetween: 25
                },
                // When window width is >= 1024px
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            }
        });
    </script>
    
    
</body>
</html>
