<?php
require_once '../../includes/session_handler.php';
require_once '../../db.php';

// Start client session and check access
startClientSession();
requireClient();

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

    
</head>

<body class="client-cars-body">
    <?php include __DIR__ . '/../client_dashboard/includes/sidebar.php'; ?>

    <div class="main">
        <header>
            <?php include __DIR__ . '/../client_dashboard/includes/topbar.php'; ?>
        </header>

        <!-- Improved Search Bar -->
        <div class="search-container">
            <form method="GET" class="search-form">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" 
                        placeholder="Search cars by model, transmission, or features..." 
                        value="<?php echo htmlspecialchars($search ?? ''); ?>"
                        class="enhanced-search">
                    <?php if (!empty($search)): ?>
                        <button type="button" class="clear-search" onclick="window.location.href=window.location.pathname">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="quick-filters">
                    <button type="button" class="filter-chip" data-filter="Automatic">Automatic</button>
                    <button type="button" class="filter-chip" data-filter="Manual">Manual</button>
                    <button type="button" class="filter-chip" data-filter="SUV">SUV</button>
                    <button type="button" class="filter-chip" data-filter="Sedan">Sedan</button>
                </div>
            </form>
        </div>

        <!--
        <div class="filter-container">
            <select id="statusFilter" class="status-filter">
                <option value="all">All Cars</option>
                <option value="Available">Available</option>
                <option value="Rented">Rented</option>
                <option value="Maintenance">Maintenance</option>
            </select>
        </div> -->

        <!--lenk to fontawesome-->
        <script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>

        <div class="card-container">
            <div class="swiper mySwiper">
                
                <div class="swiper-wrapper"> <!-- Changed from 'card' to 'swiper-wrapper' for proper Swiper.js structure -->
                    <?php
                    // Fetch cars using PDO - exclude Pending status
                    if (!empty($search)) {
                        $stmt = $pdo->prepare("SELECT * FROM car WHERE (model LIKE :search OR transmission LIKE :search) AND status != 'Pending'");
                        $stmt->execute(['search' => "%$search%"]);
                    } else {
                        $stmt = $pdo->query("SELECT * FROM car WHERE status != 'Pending'");
                    }

                    // Loop through results and display each car
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="swiper-slide car-card" 
                             data-status="<?= strtolower(htmlspecialchars($row['status'])) ?>"
                             data-transmission="<?= strtolower(htmlspecialchars($row['transmission'])) ?>"
                             data-type="<?= strtolower(htmlspecialchars($row['type'] ?? '')) ?>">
                            <!-- Card Header -->
                            <div class="car-card-header">
                                <h3 class="car-model"><?= htmlspecialchars($row['model']) ?></h3>
                                <span class="plate-number">Plate No: <?= htmlspecialchars($row['plate_no']) ?></span>
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

    <!-- Add Car Details Modal -->
    <div id="carDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div id="carDetailsContent"></div>
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

        // View Details Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('carDetailsModal');
            const closeBtn = document.querySelector('.close-modal');
            const buttons = document.querySelectorAll('.view-details-btn');

            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const carId = this.getAttribute('data-car-id');
                    const card = this.closest('.car-card');
                    
                    // Get car details from the card
                    const model = card.querySelector('.car-model').textContent;
                    const plate = card.querySelector('.plate-number').textContent;
                    const image = card.querySelector('.car-main-image').src;
                    const status = card.querySelector('.availability-badge').textContent.trim();
                    const statusClass = card.querySelector('.availability-badge').className.replace('availability-badge', '').trim();
                    const specs = Array.from(card.querySelectorAll('.spec-text')).map(spec => spec.textContent);
                    const features = card.querySelector('.features-text').textContent;
                    const price = card.querySelector('.price-amount').textContent;

                    // Populate modal with details
                    const modalContent = `
                        <div class="car-details">
                            <div class="car-details-header">
                                <div>
                                    <h2>${model}</h2>
                                    <span class="plate-number">${plate}</span>
                                </div>
                                <span class="availability-badge ${statusClass}">${status}</span>
                            </div>
                            
                            <div class="car-details-image">
                                <img src="${image}" alt="${model}">
                            </div>
                            
                            <div class="car-details-info">
                                <div class="details-section">
                                    <h3>Specifications</h3>
                                    <div class="specs-grid">
                                        <div class="spec-detail">
                                            <i class="fas fa-car"></i>
                                            <span>${specs[0]}</span>
                                        </div>
                                        <div class="spec-detail">
                                            <i class="fas fa-cogs"></i>
                                            <span>${specs[1]}</span>
                                        </div>
                                        <div class="spec-detail">
                                            <i class="fa-solid fa-gas-pump"></i>
                                            <span>${specs[2]}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="details-section">
                                    <h3>Features</h3>
                                    <p>${features}</p>
                                </div>
                                
                                <div class="details-section pricing-section">
                                    <h3>Pricing</h3>
                                    <div class="price-detail">
                                        <span class="amount">${price}</span>
                                        <span class="period">per day</span>
                                    </div>
                                </div>

                                ${status.toLowerCase() === 'available' ? `
                                    <button class="book-now-btn" onclick="redirectToBooking(${carId})">
                                        Book Now
                                    </button>
                                ` : `
                                    <div class="unavailable-notice">
                                        <i class="fas fa-info-circle"></i>
                                        <p>This car is currently ${status.toLowerCase()}. Please check back later or choose another vehicle.</p>
                                    </div>
                                `}
                            </div>
                        </div>
                    `;

                    document.getElementById('carDetailsContent').innerHTML = modalContent;
                    modal.style.display = 'block';
                });
            });

            // Close modal functionality
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            window.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // Add this function before your existing script
        function redirectToBooking(carId) {
            // Store the car ID in session storage
            sessionStorage.setItem('selectedCarId', carId);
            // Redirect with parameters
            window.location.href = 'client_booking.php?open_modal=true&selected_car=' + carId;
        }

        // Add this after your existing script
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('statusFilter');
            const carCards = document.querySelectorAll('.car-card');

            statusFilter.addEventListener('change', function() {
                const selectedStatus = this.value.toLowerCase();
                
                carCards.forEach(card => {
                    const cardStatus = card.getAttribute('data-status');
                    
                    if (selectedStatus === 'all' || cardStatus === selectedStatus) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });

                // Reinitialize Swiper to update layout
                swiper.update();
            });
        });

        // Quick Filters Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Quick filter buttons
            const filterChips = document.querySelectorAll('.filter-chip');
            const searchInput = document.querySelector('.enhanced-search');
            const carCards = document.querySelectorAll('.car-card');

            filterChips.forEach(chip => {
                chip.addEventListener('click', function() {
                    const filter = this.dataset.filter.toLowerCase();

                    carCards.forEach(card => {
                        const transmission = card.getAttribute('data-transmission');
                        const type = card.getAttribute('data-type');
                        // Show card if matches filter (transmission or type)
                        if (transmission === filter || type === filter) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    swiper.update();
                });
            });

            // Search functionality (already works server-side, but let's add client-side for instant feedback)
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                carCards.forEach(card => {
                    const model = card.querySelector('.car-model').textContent.toLowerCase();
                    const transmission = card.getAttribute('data-transmission');
                    const features = card.querySelector('.features-text').textContent.toLowerCase();
                    if (
                        model.includes(query) ||
                        transmission.includes(query) ||
                        features.includes(query)
                    ) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
                swiper.update();
            });

            // Clear search button
            const clearBtn = document.querySelector('.clear-search');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    carCards.forEach(card => card.style.display = '');
                    swiper.update();
                });
            }
        });
    </script>
</body>
</html>
