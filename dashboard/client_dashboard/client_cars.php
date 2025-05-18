<?php
require_once '../../includes/session_handler.php';
require_once '../../db.php';

// Start client session and check access
startClientSession();
requireClient();

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
    <?php include __DIR__ . '/../client_dashboard/includes/sidebar.php'; ?>

    <div class="main">
        <header>
            <?php include __DIR__ . '/../client_dashboard/includes/topbar.php'; ?>
        </header>

        <!-- Add Filter Dropdown -->
        <div class="filter-container">
            <select id="statusFilter" class="status-filter">
                <option value="all">All Cars</option>
                <option value="Available">Available</option>
                <option value="Rented">Rented</option>
                <option value="Maintenance">Maintenance</option>
            </select>
        </div>

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
                        <div class="swiper-slide car-card" data-status="<?= strtolower(htmlspecialchars($row['status'])) ?>">
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
                    const image = card.querySelector('.car-main-image').src;
                    const status = card.querySelector('.availability-badge').textContent.trim();
                    const specs = Array.from(card.querySelectorAll('.spec-text')).map(spec => spec.textContent);
                    const features = card.querySelector('.features-text').textContent;
                    const price = card.querySelector('.price-amount').textContent;

                    // Populate modal with details
                    const modalContent = `
                        <div class="car-details">
                            <div class="car-details-header">
                                <h2>${model}</h2>
                                <span class="status-badge ${status.toLowerCase()}">${status}</span>
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
    </script>
    
    <style>
        /* Add these new styles */
        .plate-number {
            display: inline-block;
            margin: 5px 0;
            padding: 4px 8px;
            background: #e9ecef;
            color: #495057;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .car-card-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .car-card-header h3.car-model {
            margin: 0;
            padding: 0;
        }

        /* Update existing styles */
        .availability-badge {
            margin-left: auto;
            align-self: flex-end;
            margin-top: -30px; /* Pull the badge up to align with the car model */
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background: white;
            margin: 50px auto;
            padding: 0;
            width: 90%;
            max-width: 800px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
            z-index: 1;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: #333;
        }

        /* Car Details Styles */
        .car-details {
            padding: 0;
        }

        .car-details-header {
            background: #f8f9fa;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .car-details-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.8rem;
        }

        .car-details-image {
            width: 100%;
            height: 300px;
            overflow: hidden;
            border-bottom: 1px solid #eee;
        }

        .car-details-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .car-details-info {
            padding: 20px;
        }

        .details-section {
            margin-bottom: 25px;
        }

        .details-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .spec-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .spec-detail i {
            color: #4CAF50;
            font-size: 1.2rem;
        }

        .pricing-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .price-detail {
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .price-detail .amount {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4CAF50;
        }

        .price-detail .period {
            color: #666;
        }

        .book-now-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .book-now-btn:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .modal-content {
                margin: 20px;
                width: calc(100% - 40px);
            }

            .car-details-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .car-details-image {
                height: 200px;
            }

            .specs-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Add these styles */
        .unavailable-notice {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .unavailable-notice i {
            font-size: 1.2rem;
        }

        .unavailable-notice p {
            margin: 0;
            font-size: 0.95rem;
        }

        /* Add these new styles */
        .filter-container {
            padding: 20px;
            display: flex;
            justify-content: flex-end;
            max-width: 1400px;
            margin: 0 auto;
        }

        .status-filter {
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: white;
            font-size: 16px;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .status-filter:hover {
            border-color: #4CAF50;
        }

        .status-filter:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }

        /* Add transition for smooth filtering */
        .car-card {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .car-card.hidden {
            display: none;
            opacity: 0;
            transform: scale(0.95);
        }
    </style>
</body>
</html>
