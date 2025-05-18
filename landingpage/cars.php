<!DOCTYPE html>
<html lang="en">
    
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Our Cars | QuickLease</title>
  <link rel="stylesheet" href="/quicklease/css/landingpage.css">
  <link rel="stylesheet" href="/quicklease/css/cars.css">

</head>

<body>
  <?php include 'includes/navbar.php'; ?>
  
  <link rel="stylesheet" href="../css/cars.css">
  <script src="../javascript/cars.js" defer></script>

  <section class="catalogue">
    <h2>Car Catalogue</h2>
    <p>Explore out cars you might like!</p>
    <div class="filters">
      <select id="priceFilter"><option>Price</option></select>
      <select id="manufacturerFilter"><option>Manufacturer</option></select>
      <select id="typeFilter"><option>Type</option></select>
      <select id="ratingFilter"><option>Rating</option></select>
    </div>

    <div class="car-grid">
      <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="car-card" data-price="5k" data-manufacturer="Toyota" data-type="SUV" data-rating="5">
          <h3>Toyota New Yaris</h3>
          <p class="price">Php <span>5k</span> /day</p>
          <img src="../images/car<?= $i+1 ?>.jpg" alt="Toyota Car">
          <div class="specs">
            <span>⚙ Manual</span>
            <span>🧍 5 Seats</span>
            <span>⛽ 24 MPG</span>
          </div>
          <button class="book-now hidden">Book Now</button>
        </div>
      <?php endfor; ?>
    </div>

    <div class="brands">
      <img src="../images/audi.png" alt="Audi">
      <img src="../images/benz.png" alt="Mercedes">
      <img src="../images/toyota.png" alt="Toyota">
      <img src="../images/jaguar.png" alt="Jaguar">
    </div>

    <div class="promo-banner">
      <h2>Seamless Rentals for Every Adventure</h2>
      <p>Reserve your car with just a few clicks!</p>
      <a href="#" class="btn-book">Book Now</a>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

</body>
</html>
