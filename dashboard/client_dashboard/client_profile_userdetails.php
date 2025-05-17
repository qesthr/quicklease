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

// Example invoice data (replace with DB query in production)
$invoices = [
    [
        'car_name' => 'Toyota Camry',
        'car_image' => '../../images/camry-black.png', // Place your car image here
        'date_from' => 'April-28-2025',
        'date_to' => 'April-29-2025',
        'price' => 5000,
        'total' => 5000,
        'invoice_id' => 1
    ],
    // Add more invoices as needed
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Client Profile</title>
    <link rel="stylesheet" href="../../css/client.css">
    <link rel="stylesheet" href="../../css/client-account.css">
 
</head>
<body class="client-body">
    
    <div class="container">
        <?php include __DIR__ . '/../client_dashboard/includes/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="content">

            <header>
                <?php include __DIR__ . '/../client_dashboard/includes/topbar.php'; ?>
            </header>

            <!-- Profile Details -->
            <section class="profile-container">
                <div class="profile-details">

                    <div class="profile-card">

                        <h2>Personal Information</h2>

                        <div class="profile-header">
                            <img src="../../images/profile.jpg" alt="Profile Picture" class="profile-image">
                            <div class="profile-header-info">        
                                <h3 class="name"><?= htmlspecialchars($user['firstname'] ?? '') ?></h3>
                                <p class="username"> @<?= htmlspecialchars($user['username'] ?? '') ?></p>
                            </div>
                            <div class="edit-icon-container">
                                <button class="edit-icon-button">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                            </div>
                        </div>

                    
                        <div class="tab-menu">
                            <div class="tab-menu-container">
                                <button class="tab-link active" data-tab="detailsTab">User Details</button>
                                <button class="tab-link" data-tab="notificationsTab">Notifications</button>
                                <button class="tab-link" data-tab="invoicesTab">Invoices</button>
                            </div>
                        </div>

                        <!-- details tab -->
                        <div id="detailsTab" class="tab-content active-tab">
                            <div class="details-card">
                                <h3>Personal Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>First Name:</label>
                                        <p><?= htmlspecialchars($user['firstname'] ?? '') ?></p>
                                    </div>
                                    <div class="info-item">
                                        <label>Last Name:</label>
                                        <p><?= htmlspecialchars($user['lastname'] ?? '') ?></p>
                                    </div>
                                    <div class="info-item">
                                        <label>Email:</label>
                                        <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
                                    </div>
                                    <div class="info-item">
                                        <label>Phone Number:</label>
                                        <p><?= htmlspecialchars($user['customer_phone'] ?? '') ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="details-card">
                                <h3>Account Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>Username:</label>
                                        <p><?= htmlspecialchars($user['username'] ?? '') ?></p>
                                    </div>
                                    <div class="info-item">
                                        <label>Account Type:</label>
                                        <p><?= ucfirst($user['user_type'] ?? '') ?></p>
                                    </div>
                                    <div class="info-item">
                                        <label>Member Since:</label>
                                        <p><?= date('F j, Y', strtotime($user['created_at'] ?? '')) ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="details-card">
                                <h3>ID Verification</h3>
                                <div class="id-verification-container">
                                    <?php if ($success === "ID successfully uploaded!"): ?>
                                        <div class="alert-success">
                                            <p><?= htmlspecialchars($success) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="id-verification-content">
                                        <div class="id-preview-section">
                                            <?php if (!empty($user['submitted_id'])): ?>
                                                <div class="id-image-container">
                                                    <img id="idPreview" 
                                                         src="../../uploads/ids/<?= htmlspecialchars($user['submitted_id']) ?>" 
                                                         alt="Submitted ID" 
                                                         class="submitted-id">
                                                </div>
                                                
                                                <?php
                                                $status = isset($user['verification_status']) ? $user['verification_status'] : 'Pending';
                                                $statusClass = $status === 'Verified' ? 'accepted' : ($status === 'Rejected' ? 'rejected' : 'pending');
                                                ?>
                                                <div class="verification-status <?= $statusClass ?>">
                                                    <?php
                                                    switch($status) {
                                                        case 'Verified':
                                                            echo '<p><i class="fas fa-check-circle"></i> Your ID has been verified</p>';
                                                            break;
                                                        case 'Rejected':
                                                            echo '<p><i class="fas fa-times-circle"></i> Your ID verification was rejected</p>';
                                                            break;
                                                        default:
                                                            echo '<p><i class="fas fa-clock"></i> Your ID is pending verification</p>';
                                                    }
                                                    ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="no-id-message">
                                                    <i class="fas fa-id-card"></i>
                                                    <p>Please upload a valid ID image to verify your account</p>
                                                    <p class="id-requirements">Accepted formats: JPG, PNG (Max size: 5MB)</p>
                                                </div>
                                                <img id="idPreview" src="#" alt="ID Preview" class="submitted-id hidden">
                                            <?php endif; ?>
                                        </div>

                                        <div class="id-upload-section">
                                            <form method="POST" enctype="multipart/form-data" class="id-upload-form">
                                                <div class="file-input-container">
                                                    <label for="idImage" class="file-label">Upload New ID:</label>
                                                    <input type="file" 
                                                           id="idImage" 
                                                           name="submitted_id" 
                                                           accept="image/*" 
                                                           class="file-input">
                                                </div>
                                                <button type="submit" id="saveIdBtn" class="save-id-btn">
                                                    <i class="fas fa-upload"></i> Save ID
                                                </button>
                                            </form>
                                        </div>

                                        <?php 
                                        $isAdmin = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
                                        if ($isAdmin): 
                                        ?>
                                        <div class="admin-controls">
                                            <span id="statusText" 
                                                  class="status-indicator <?= $user['verification_status'] === 'Verified' ? 'verified' : 'not-verified' ?>">
                                                <?= $user['verification_status'] ?>
                                            </span>
                                            <button id="verifyBtn" class="verify-button">
                                                <?= $user['verification_status'] === 'Verified' ? 'Mark as Not Verified' : 'Mark as Verified' ?>
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <!-- notifications tab -->
                        <div id="notificationsTab" class="tab-content hidden">
                            <div class="notifications-container">
                                <div class="notification-details">
                                    <div class="notification-card">
                                        <div class="notification-header">
                                            <p class="notification-greeting">Hi, <?= htmlspecialchars($user['firstname'] ?? '') ?>!</p>
                                        </div>
                                        <div class="notification-body">
                                            <p class="notification-message">
                                                Good news! Your booking for Toyota Camry from April-28-2025 to April-29-2025 has been approved by our admin.
                                            </p>
                                        </div>
                                        <div class="notification-footer">
                                            <button class="view-booking-btn">View Booking</button>
                                        </div>
                                    </div>
                                    <div class="date-indicator">
                                        <div class="notification-dot"></div>
                                        <div class="date-stack">
                                            <span class="date-day">27</span>
                                            <span class="date-month-year">Apr 2025</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- invoices tab -->
                        <div id="invoicesTab" class="tab-content hidden">
                            <div class="invoices-list">
                                <?php foreach ($invoices as $invoice): ?>
                                    <div class="invoice-card">
                                        <div class="invoice-car-img">
                                            <img src="<?= htmlspecialchars($invoice['car_image']) ?>" alt="<?= htmlspecialchars($invoice['car_name']) ?>">
                                        </div>
                                        <div class="invoice-details">
                                            <h2><?= htmlspecialchars($invoice['car_name']) ?></h2>
                                            <div class="invoice-dates">
                                                <?= htmlspecialchars($invoice['date_from']) ?> to <?= htmlspecialchars($invoice['date_to']) ?>
                                            </div>
                                            <div class="invoice-price">
                                                <span>₱ <?= number_format($invoice['price'], 2) ?></span>
                                            </div>
                                            <div class="invoice-total">
                                                <span>Total Amount</span>
                                                <span class="total-amount">₱ <?= number_format($invoice['total'], 2) ?></span>
                                            </div>
                                            <a href="print_invoice.php?id=<?= $invoice['invoice_id'] ?>" class="print-invoice-link" target="_blank">PRINT INVOICE</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        

                    
                </div>
            </section>
        </main>
    </div>

    
    <!-- Hidden file input for profile picture update 
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
    -->
    
    <!-- JavaScript -->
    <script src="../../javascript/client-account.js"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>

    <div id="editProfileModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" id="closeEditModal">&times;</span>
            <h2>Edit Profile</h2>
            <form id="editProfileForm" method="POST">
                <h2>Personal Information</h2>
                <div class="form-group">
                    <label for="editFirstName">First Name</label>
                    <input type="text" id="editFirstName" name="firstname" 
                           value="<?= htmlspecialchars($user['firstname'] ?? '') ?>"
                           required>
                </div>
                <div class="form-group">
                    <label for="editLastName">Last Name</label>
                    <input type="text" id="editLastName" name="lastname" 
                           value="<?= htmlspecialchars($user['lastname'] ?? '') ?>"
                           required>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" 
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                           required>
                </div>
                <div class="form-group">
                    <label for="editPhone">Phone Number</label>
                    <input type="text" id="editPhone" name="phone"
                           value="<?= htmlspecialchars($user['customer_phone'] ?? '') ?>"
                           required>
                </div>

                <h2>Account Information</h2>
                <div class="form-group">
                    <label for="editUsername">Username</label>
                    <input type="text" id="editUsername" name="username" 
                           value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                           readonly>
                </div>
                <button type="submit" class="btn" id="saveChangesBtn">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>
