<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us | QuickLease</title>
  <link rel="stylesheet" href="../css/landingpage.css" />
</head>
<body>
  <?php include 'navbar.php'; ?>

  <section class="contact-hero">
    <h1>Need Help?</h1>
    <p>Contact our support team</p>
  </section>

  <section class="contact-form-section">
    <form action="send-message.php" method="POST">
      <label for="name">Your Name</label>
      <input type="text" id="name" name="name" required>

      <label for="email">Your Email</label>
      <input type="email" id="email" name="email" required>

      <label for="message">Message</label>
      <textarea id="message" name="message" rows="5" required></textarea>

      <button type="submit">Send Message</button>
    </form>
  </section>

  <?php include 'footer.php'; ?>
</body>
</html>
