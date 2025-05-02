<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>QuickLease - Online Car Rental System</title>

    <!-- css lenk -->
    <link rel="stylesheet" href="css/landingpage.css">
    <!-- gogel fonts-->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>

    <!-- navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <img src="images/logo2.png" alt="">
        </div>

        <div class="nav-container">
            <ul class="navbar-nav">
                <li><a href="#cars" class="nav-link">Cars</a></li>
                <li><a href="#brands" class="nav-link">Features</a></li>
                <li><a href="#contact" class="nav-link">Help</a></li>
            </ul>
        <a href="/login-page/signup.php" class="btn-signup">Signup</a>
    </div>
       
        
    </nav>

    <!--big header section-->
    <section class="hero">
        
        <h1>Explore the world’s largest car sharing <br> & rental marketplace</h1>

        <div class="search-form">
            <select>
                <option>Choose Location</option>
            </select>
            <input type="date" placeholder="Departure Date">
            <input type="date" placeholder="Return Date">
            <button class="btn-book">Book Now</button>
        </div>
        <img src="images/suv.png" alt="Main Car" style="width:40%; margin-top:1rem;">
    
    </section>

    <!-- old rent flow -->
    <!--
    <section class="rent-flow" id="features">
        <h2>Rent Flow</h2>

        <svg viewBox="0 0 500 150" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: auto;">
            <path d="M0,75 Q50,0 100,75 T200,75 T300,75 T400,75 T500,75" 
                fill="transparent" 
                stroke="#3232D0" 
                stroke-width="3" 
                stroke-dasharray="5,10" />
        </svg>


        <div class="timeline">
            <div class="timeline-step"></div>
            <div class="timeline-step"></div>
            <div class="timeline-step"></div>
            <div class="timeline-step"></div>
        </div>
        <div style="display: flex; justify-content: space-around; flex-wrap: wrap; margin-top:2rem;">
            <div class="timeline-text">Choose a Location</div>
            <div class="timeline-text">Choose Departure</div>
            <div class="timeline-text">Choose Pickup</div>
            <div class="timeline-text">Choose Return</div>
        </div>
    </section> -->

    <!-- rent flow new -->

    <section class="rent-flow" id="features">
        <h1>Rent Flow</h1>
        <p class="subtitle">Complete your rental in 4 simple steps</p>

        <div class="timeline-container">
            <!-- Wavy dotted line SVG -->
            <svg class="wavy-line" viewBox="0 0 1000 100" preserveAspectRatio="none">
            <path d="M0,50 C150,0 200,100 350,50 S500,0 650,50 S800,100 950,50" 
                    fill="none" 
                    stroke="#1818CA" 
                    stroke-width="3" 
                    stroke-dasharray="5,5" />
            </svg>
            
            <div class="timeline-steps">
                <!-- Step 1 -->
                <div class="step" style="left: 5%;">
                    <div class="step-marker">1</div>
                    <div class="step-text">Choose Location</div>
                    <p>Select your preferred pickup location from our network of stations. <br> We’re available in cities nationwide!"</p>
                </div>
                
                <!-- Step 2 -->
                <div class="step" style="left: 35%;">
                    <div class="step-marker">2</div>
                    <div class="step-text">Select Dates</div>
                    <p>Choose your rental period. Minimum rental: 6 hours. <br> Need     a long-term deal? Ask about weekly rates!</p>
                </div>
                
                <!-- Step 3 -->
                <div class="step" style="left: 65%;">
                    <div class="step-marker">3</div>
                    <div class="step-text">Pick Your Car</div>
                    <p>Browse cars available</p>
                </div>
                
                <!-- Step 4 -->
                <div class="step" style="left: 95%;">
                    <div class="step-marker">4</div>
                    <div class="step-text">Confirm & Drive</div>
                    <p>Review your rental details and submit. <br> We’ll email your confirmation and pickup instructions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- end of rent flow new -->


    <!-- cars section -->
    <section class="vehicles" id="cars">
        <h2>Our Vehicles Ready For Drive</h2>
        <p>Whether you're planning a weekend getaway or a business trip, <br> find the perfect car for your journey in just a few clicks.</p>

        <div class="vehicle-tabs">
            <button class="vehicle-tab active" data-category="sedan">Toyota Vios</button>
            <button class="vehicle-tab" data-category="luxury">BMW 3 Series</button>
            <button class="vehicle-tab" data-category="suv">Toyota Fortuner</button>
            <button class="vehicle-tab" data-category="truck">Ford Ranger</button>
        </div>

        <div class="vehicle-cards">
            <div class="card" style="width: 18rem;">
                <img src="images/fortuner.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h3>Toyota Vios</h3>
                    <p class="price">$45/day</p>
                </div>
            </div>

            <div class="card" style="width: 18rem;">
                <img src="images/hiace.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h3>Toyota Vios</h3>
                    <p class="price">$45/day</p>
                </div>
            </div>

            <div class="card" style="width: 18rem;">
                <img src="images/mini-van.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h3>Toyota Vios</h3>
                    <p class="price">$45/day</p>
                </div>
            </div>

            <div class="card" style="width: 18rem;">
                <img src="images/montero.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h3>Toyota Vios</h3>
                    <p class="price">$45/day</p>
                </div>
            </div>

            <div class="card" style="width: 18rem;">
                <img src="images/strada.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h3>Toyota Vios</h3>
                    <p class="price">$45/day</p>
                </div>
            </div>

            <div class="card" style="width: 18rem;">
                <img src="images/vios.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h3>Toyota Vios</h3>
                    <p class="price">$45/day</p>
                </div>
            </div>
        
        </div>

        <button class="show-all-btn" data-filter="all" >Show all (10 models)</button>
    </section> 
    
    <!--end of cars-->

    <!-- start of  cars -->
    <section class="car-brands" id="brands">
        <img src="audi-logo.png" alt="Audi">
        <img src="mercedes-logo.png" alt="Mercedes-Benz">
        <img src="toyota-logo.png" alt="Toyota">
        <img src="jaguar-logo.png" alt="Jaguar">
    </section>

    <!--end of cars -->

    <!-- start of promotion banner -->
    <section class="promo-banner" id="contact">
        <h2>Seamless Rentals for Every Adventure</h2>
        <p>Reserve your car with just a few clicks!</p>
        <button class="btn-book-now">Book Now</button>
    </section>

    <!--end of promotion banner -->

    <!-- footer -->
    <footer class="footer" id="help">
        <div>
            <a href="#">Terms</a>
            <a href="#">Privacy</a>
        </div>
        <div>
            <a href="#">About Us</a>
            <a href="#">Features</a>
            <a href="#">Help</a>
        </div>
        <div class="footer-brand">
            <img src="images/logo2.png" alt="">
        </div>
    </footer>

    <!-- end of promotion banner -->

    <script type="module" src="./javascript/landingpage.js"></script>

</body>
</html>
