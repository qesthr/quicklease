<?php
// filepath: c:\xamppss\htdocs\quicklease\dashboard\client_dashboard\client_profile_userdetails.php
session_start();
include_once '../../db.php';

// Assuming the client is logged in and their ID is stored in the session
$client_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    echo "You must be logged in to view this page.";
    exit();
}

// Fetch client details from the database
$stmt = $pdo->prepare("SELECT * FROM customer WHERE id = ?");
$stmt->execute([$customer_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo "Client not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Client User Details</title>
  <link rel="stylesheet" href="client_profile_invoice.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-nav">
        <div class="nav-item active">Profile</div>
        <div class="nav-item">Cars</div>
        <div class="nav-item">Bookings</div>
      </div>
      <div class="logout"><a href="#">Logout</a></div>
    </aside>

    <!-- Main Content -->
    <main class="content">
      <header class="header">
        <div class="header-top">
          <h1>PROFILE</h1>
          <div class="icons">
            <span class="bell">🔔</span>
            <img src="profile-pic.png" alt="Profile" class="profile-icon" id="headerProfileIcon">
          </div>
        </div>

        <div class="header-user-info">
          <div class="user-thumb-clickable" id="userThumb">
            <img src="profile-pic.png" alt="User Profile Image" id="userProfileImage">
          </div>

          <div class="user-details">
            <div id="userName"><?= htmlspecialchars($client['customer_name']) ?></div>
            <div id="userProfession">University Professor</div> <!-- Placeholder -->
          </div>
        </div>

        <nav class="secondary-nav">
          <a href="client_profile_userdetails.php" class="active">User Details</a>
          <a href="client_profile_notification.html">Notifications</a>
          <a href="client_profile_invoice.html">Invoices</a>
        </nav>
      </header>

      <!-- Profile Info -->
      <section class="profile-details">
        <form id="uploadForm" enctype="multipart/form-data">
          <!-- Row 1 -->
          <div class="row">
            <div class="col">
              <label for="first-name">First Name</label>
              <input id="first-name" type="text" value="<?= htmlspecialchars(explode(' ', $client['customer_name'])[0]) ?>" readonly>
            </div>
            <div class="col">
              <label for="last-name">Last Name</label>
              <input id="last-name" type="text" value="<?= htmlspecialchars(explode(' ', $client['customer_name'])[1] ?? '') ?>" readonly>
            </div>
          </div>

          <!-- Row 2: Email -->
          <div class="row full-width">
            <label for="email">Email</label>
            <div class="email-container">
              <input id="email" type="email" value="<?= htmlspecialchars($client['customer_email']) ?>" readonly>
              <span class="verified">verified</span>
            </div>
          </div>

          <!-- Row 3: Address -->
          <div class="row full-width">
            <label for="address">Address</label>
            <input id="address" class="full" type="text" value="Sumpong, Malaybalay City Bukidnon" readonly> <!-- Placeholder -->
          </div>

          <!-- Row 4: Phone and DOB -->
          <div class="row">
            <div class="col">
              <label for="phone-number">Phone Number</label>
              <input id="phone-number" type="text" value="<?= htmlspecialchars($client['customer_phone']) ?>" readonly>
            </div>
            <div class="col">
              <label for="dob">Date of Birth</label>
              <input id="dob" type="text" value="(date of birth)" readonly> <!-- Placeholder -->
            </div>
          </div>

          <!-- Image Upload Section -->
          <div class="row full-width">
            <label for="submitted_id">Upload Verification Image</label>
            <input type="file" name="submitted_id" id="submitted_id" accept="image/*" required>
            <button type="button" id="submitImage">Submit</button>
            <p id="uploadMessage" style="color: green; display: none;">Successfully submitted!</p>
          </div>
        </form>
      </section>
    </main>
  </div>

  <!-- Hidden file input for profile image changes -->
  <input type="file" accept="image/*" id="profileUpload" style="display:none">

  <script>
    // Function to toggle notifications (if needed)
    const headerProfileIcon = document.getElementById("headerProfileIcon");
    const userProfileImage = document.getElementById("userProfileImage");
    const profileUpload = document.getElementById("profileUpload");

    headerProfileIcon.addEventListener("click", () => profileUpload.click());
    document.getElementById("userThumb").addEventListener("click", () => profileUpload.click());

    profileUpload.addEventListener("change", function(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          headerProfileIcon.src = e.target.result;
          userProfileImage.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });

    // AJAX for Image Upload
    document.getElementById("submitImage").addEventListener("click", function () {
      const fileInput = document.getElementById("submitted_id");
      const uploadMessage = document.getElementById("uploadMessage");

      if (fileInput.files.length === 0) {
        alert("Please select an image to upload.");
        return;
      }

      const formData = new FormData();
      formData.append("submitted_id", fileInput.files[0]);

      // Send the image to the server using AJAX
      fetch("client_account.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.text())
        .then((data) => {
          if (data.trim() === "success") {
            uploadMessage.style.display = "block"; // Show success message
            fileInput.value = ""; // Clear the file input
          } else {
            alert("Error: " + data);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred while uploading the image.");
        });
    });
  </script>
</body>
</html>