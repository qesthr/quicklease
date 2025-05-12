<?php
include '../../db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../../login.php");
    exit();
}

// Initialize variables
$success = '';
$error = '';

// Fetch client data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'client'");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found or not a client.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
    $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        try {
            // Update user data
            $stmt = $pdo->prepare("UPDATE users SET 
                firstname = ?, 
                lastname = ?, 
                email = ?, 
                customer_phone = ? 
                WHERE id = ?");
            
            $stmt->execute([$firstname, $lastname, $email, $phone, $user_id]);
            
            $success = "Profile updated successfully!";
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['submitted_id'])) {
    $target_dir = "../../uploads/";
    $file_name = time() . "_" . basename($_FILES['submitted_id']['name']);
    $target_file = $target_dir . $file_name;
    
    // Validate file
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_type, $allowed_types)) {
        $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
    } elseif ($_FILES['submitted_id']['size'] > 2 * 1024 * 1024) {
        $error = "File size must not exceed 2MB.";
    } else {
        if (move_uploaded_file($_FILES['submitted_id']['tmp_name'], $target_file)) {
            // Update database with file name
            $stmt = $pdo->prepare("UPDATE users SET submitted_id = ? WHERE id = ?");
            if ($stmt->execute([$file_name, $user_id])) {
                $success = "ID successfully uploaded!";
                // Refresh user data
                $user['submitted_id'] = $file_name;
            } else {
                $error = "Database update failed.";
            }
        } else {
            $error = "File upload failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Client Profile</title>
    <link rel="stylesheet" href="../../css/client_profile_invoice.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/header.css">
</head>
<body>
 <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-nav">
        <a href="client_profile_userdetails.php" class="nav-item">Profile</a>
        <a href="client_cars.php" class="nav-item">Cars</a>
        <a href="client_booking.php" class="nav-item active">Bookings</a>
      </div>
      <div class="logout">
        <a href="/loginpage/login.php">Logout</a>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="content">
        <!-- Header -->
        <header class="header">
            <div class="header-top">
                <h1>PROFILE</h1>
                <div class="icons">
                    <span class="bell">🔔</span>
                    <img src="profile-pic.png" alt="Profile" class="profile-icon" id="headerProfileIcon">
                </div>
            </div>
            <div class="header-user-info">
                <div class="header-left">
                    <div class="user-thumb-clickable" id="userThumb">
                        <img src="profile-pic.png" alt="User Profile Image" id="userProfileImage">
                    </div>
                </div>
                <div class="header-right">
                    <div class="sub-line" id="userName"><?= htmlspecialchars($user['firstname'] ?? 'John Doe') ?></div>
                    <div class="sub-line" id="userProfession">Client</div>
                </div>
            </div>
            <nav class="secondary-nav">
                <a href="client_profile_userdetails.php" class="active">User Details</a>
                <a href="client_profile_notification.html">Notifications</a>
                <a href="client_profile_invoice.html">Invoices</a>
            </nav>
        </header>

        <!-- Profile Details -->
        <section class="booking-cards">
            <div class="profile-container">
                <?php if ($success): ?>
                    <div class="alert-success"><?= htmlspecialchars($success) ?></div>
                <?php elseif ($error): ?>
                    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="profile-card">
                    <h2>Personal Information</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" id="firstname" name="firstname" 
                                   value="<?= htmlspecialchars($user['firstname'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" id="lastname" name="lastname" 
                                   value="<?= htmlspecialchars($user['lastname'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($user['customer_phone'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="submitted_id">Verification ID (Upload/Update)</label>
                            <?php if (!empty($user['submitted_id'])): ?>
                                <div>
                                    <img src="../../uploads/<?= htmlspecialchars($user['submitted_id']) ?>" 
                                         alt="Current ID" class="profile-image">
                                </div>
                            <?php endif; ?>
                            <input type="file" id="submitted_id" name="submitted_id" accept="image/*">
                        </div>

                        <button type="submit" class="btn">Update Profile</button>
                    </form>
                </div>

                <div class="profile-card">
                    <h2>Account Information</h2>
                    <p><strong>Username:</strong> <?= htmlspecialchars($user['username'] ?? '') ?></p>
                    <p><strong>Account Type:</strong> <?= htmlspecialchars(ucfirst($user['user_type'] ?? '')) ?></p>
                    <p><strong>Member Since:</strong> <?= date('F j, Y', strtotime($user['created_at'] ?? '')) ?></p>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Hidden file input for profile picture update -->
<input type="file" accept="image/*" id="profileUpload">

<script>
    const headerProfileIcon = document.getElementById("headerProfileIcon");
    const userProfileImage = document.getElementById("userProfileImage");
    const userThumb = document.getElementById("userThumb");
    const profileUpload = document.getElementById("profileUpload");

    headerProfileIcon.addEventListener("click", () => profileUpload.click());
    userThumb.addEventListener("click", () => profileUpload.click());

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
</script>
</body>
</html>
