<?php
require_once '../../includes/session_handler.php';
require_once '../../db.php';

// Start client session
startClientSession();

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

// Initialize variables
$success = '';
$error = '';

// Helper function for input sanitization
function sanitize_input($input) {
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    // Convert special characters to HTML entities
    $input = htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
    // Remove any null bytes
    $input = str_replace(chr(0), '', $input);
    return $input;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs using the new helper function
    $firstname = sanitize_input($_POST['firstname'] ?? '');
    $lastname = sanitize_input($_POST['lastname'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = sanitize_input($_POST['phone'] ?? '');
    $username = sanitize_input($_POST['username'] ?? '');

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
                customer_phone = ?, 
                username = ?
                WHERE id = ?");
            
            $stmt->execute([$firstname, $lastname, $email, $phone, $username, $user_id]);
            
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
                // Set status to Pending Approval only if not already Approved or Rejected
                $statusCheck = $pdo->prepare("SELECT status FROM users WHERE id = ?");
                $statusCheck->execute([$user_id]);
                $currentStatus = $statusCheck->fetchColumn();
                if ($currentStatus !== 'Approved' && $currentStatus !== 'Rejected') {
                    $pdo->prepare("UPDATE users SET status = 'Pending Approval' WHERE id = ?")->execute([$user_id]);
                }
                $success = "ID successfully uploaded!";
                $user['submitted_id'] = $file_name;
                $user['status'] = 'Pending Approval';
            } else {
                $error = "Database update failed.";
            }
        } else {
            $error = "File upload failed.";
        }
    }
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "../../uploads/profile_pictures/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . "_" . basename($_FILES['profile_picture']['name']);
    $target_file = $target_dir . $file_name;
    
    // Validate file
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_type, $allowed_types)) {
        $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
    } elseif ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
        $error = "File size must not exceed 2MB.";
    } else {
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            // Update database with file name
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            if ($stmt->execute([$file_name, $user_id])) {
                $success = "Profile picture updated successfully!";
                // Refresh user data
                $user['profile_picture'] = $file_name;
            } else {
                $error = "Database update failed.";
            }
        } else {
            $error = "File upload failed.";
        }
    }
}

// Example invoice data (replace with DB query in production)
// $invoices = [
//     [
//         'car_name' => 'Toyota Camry',
//         'car_image' => '../../images/camry-black.png', // Place your car image here
//         'date_from' => 'April-28-2025',
//         'date_to' => 'April-29-2025',
//         'price' => 5000,
//         'total' => 5000,
//         'invoice_id' => 1
//     ],
//     // Add more invoices as needed
// ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Client Profile</title>
    <link rel="stylesheet" href="../../css/client.css">
    <link rel="stylesheet" href="../../css/client-account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

                        <!-- Profile Header with Pencil Icon -->
                        <div class="profile-header">
                            <div class="profile-image-container">
                                <img src="<?= !empty($user['profile_picture']) ? '../../uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']) : '../../images/default-profile.jpg' ?>" 
                                     alt="Profile Picture" 
                                     class="profile-image">
                            </div>
                            <div class="profile-header-info">        
                                <h3 class="name"><?= htmlspecialchars($user['firstname'] ?? '') . ' ' . htmlspecialchars($user['lastname'] ?? '') ?></h3>
                                <p class="username">@<?= htmlspecialchars($user['username'] ?? '') ?></p>
                            </div>
                            <button class="edit-icon-button" onclick="openEditProfileModal()" title="Edit Profile">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                        </div>

                        <style>
                            .profile-image-container {
                                position: relative;
                                width: 120px;
                                height: 120px;
                                margin-right: 20px;
                            }
                            .profile-image {
                                width: 120px;
                                height: 120px;
                                border-radius: 50%;
                                object-fit: cover;
                                border: 2px solid #ccc;
                                display: block;
                            }
                            .profile-image-overlay {
                                position: absolute;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                background: rgba(0, 0, 0, 0.5);
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                opacity: 0;
                                transition: opacity 0.3s;
                                cursor: pointer;
                            }
                            .profile-image-container:hover .profile-image-overlay {
                                opacity: 1;
                            }
                            .upload-icon {
                                color: white;
                                font-size: 24px;
                                cursor: pointer;
                            }
                            .upload-icon i {
                                color: white;
                            }
                            .tab-content.hidden {
                                display: none;
                            }
                            .tab-content.active-tab {
                                display: block;
                            }
                        </style>

                        <!-- Font Awesome (in your <head> if not already present) -->
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

                        <div class="tab-menu">
                            <div class="tab-menu-container">
                                <button class="tab-link active" data-tab="detailsTab">User Details</button>
                                <button class="tab-link" data-tab="notificationsTab">Notifications</button>
                                <button class="tab-link" data-tab="invoicesTab">Invoices</button>
                            </div>
                        </div>

                        <div id="detailsTab" class="tab-content active-tab">
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
                                                    <button type="button" onclick="openIdModal()" class="view-id-btn">View ID</button>
                                                    <!-- Modal for viewing ID -->
                                                    <div id="idModal" class="modal" style="display:none;">
                                                        <div class="modal-content" style="max-width: 400px; margin: 10% auto; background: #fff; border-radius: 10px; padding: 20px; position: relative;">
                                                            <span class="close" onclick="closeIdModal()" style="position:absolute;top:10px;right:20px;font-size:2rem;cursor:pointer;">&times;</span>
                                                            <img src="../../uploads/<?= htmlspecialchars($user['submitted_id']) ?>" 
                                                                 alt="Submitted ID" 
                                                                 style="width:100%;border-radius:8px;">
                                                        </div>
                                                    </div>
                                                    <style>
                                                        .modal {
                                                            display: none;
                                                            position: fixed;
                                                            z-index: 9999;
                                                            left: 0; top: 0; width: 100%; height: 100%;
                                                            overflow: auto;
                                                            background: rgba(0,0,0,0.5);
                                                        }
                                                    </style>
                                                    <script>
                                                        function openIdModal() {
                                                            document.getElementById('idModal').style.display = 'block';
                                                        }
                                                        function closeIdModal() {
                                                            document.getElementById('idModal').style.display = 'none';
                                                        }
                                                        window.onclick = function(event) {
                                                            var modal = document.getElementById('idModal');
                                                            if (event.target == modal) {
                                                                modal.style.display = 'none';
                                                            }
                                                        }
                                                    </script>
                                                    <?php
                                                    $status = $user['status'];
                                                    if ($status === 'Approved') {
                                                        echo '<span class="verified"><i class="fas fa-check-circle"></i> Verified</span>';
                                                    } elseif ($status === 'Pending Approval') {
                                                        echo '<span class="pending"><i class="fas fa-clock"></i> Your ID is pending verification</span>';
                                                    } elseif ($status === 'Rejected') {
                                                        echo '<span class="rejected"><i class="fas fa-times-circle"></i> Your ID verification was rejected</span>';
                                                    }
                                                    ?>
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
                                                              class="status-indicator <?= $user['status'] === 'Verified' ? 'verified' : 'not-verified' ?>">
                                                            <?= $user['status'] ?>
                                                        </span>
                                                        <button id="verifyBtn" class="verify-button">
                                                            <?= $user['status'] === 'Verified' ? 'Mark as Not Verified' : 'Mark as Verified' ?>
                                                        </button>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <!-- notifications tab -->
                        <div id="notificationsTab" class="tab-content hidden">
                            <div class="notifications-container">
                                <?php
                                try {
                                    // Fetch notifications for the user with booking details
                                    $notifications_stmt = $pdo->prepare("
                                        SELECT 
                                            n.*,
                                            DATE_FORMAT(n.created_at, '%d') as notification_day,
                                            DATE_FORMAT(n.created_at, '%b %Y') as notification_month_year,
                                            b.booking_date,
                                            b.return_date,
                                            b.status as booking_status,
                                            c.model as car_model
                                        FROM notifications n
                                        LEFT JOIN bookings b ON n.booking_id = b.id
                                        LEFT JOIN car c ON b.car_id = c.id
                                        WHERE n.users_id = ?
                                        ORDER BY n.created_at DESC
                                    ");
                                    $notifications_stmt->execute([$user_id]);
                                    $notifications = $notifications_stmt->fetchAll(PDO::FETCH_ASSOC);

                                    if (empty($notifications)) {
                                        ?>
                                        <div class="no-notifications">
                                            <i class="fas fa-bell-slash"></i>
                                            <p>No notifications yet</p>
                                            <p class="sub-text">You will see notifications here when there are updates to your bookings.</p>
                                        </div>
                                        <?php
                                    } else {
                                        foreach ($notifications as $notification) {
                                            $statusClass = '';
                                            switch($notification['notification_type']) {
                                                case 'booking_pending':
                                                    $statusClass = 'pending';
                                                    break;
                                                case 'booking_approved':
                                                    $statusClass = 'approved';
                                                    break;
                                                case 'booking_completed':
                                                    $statusClass = 'completed';
                                                    break;
                                                case 'booking_cancelled':
                                                    $statusClass = 'cancelled';
                                                    break;
                                            }
                                            ?>
                                            <div class="notification-details">
                                                <div class="notification-card <?= $notification['is_read'] ? '' : 'unread' ?> <?= $statusClass ?>" 
                                                     data-notification-id="<?= $notification['id'] ?>">
                                                    <div class="notification-header">
                                                        <p class="notification-greeting">Hi, <?= htmlspecialchars($user['firstname']) ?>!</p>
                                                        <?php if ($notification['booking_status']): ?>
                                                        <span class="booking-status <?= strtolower($notification['booking_status']) ?>">
                                                            <?= $notification['booking_status'] ?>
                                                        </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="notification-body">
                                                        <p class="notification-message">
                                                            <?= htmlspecialchars($notification['message']) ?>
                                                        </p>
                                                    </div>
                                                    <?php if ($notification['booking_id']): ?>
                                                    <div class="notification-footer">
                                                        <button class="view-booking-btn" data-booking-id="<?= $notification['booking_id'] ?>">
                                                            View Booking
                                                        </button>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="date-indicator">
                                                    <div class="notification-dot <?= $statusClass ?>"></div>
                                                    <div class="date-stack">
                                                        <span class="date-day"><?= $notification['notification_day'] ?></span>
                                                        <span class="date-month-year"><?= $notification['notification_month_year'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                } catch (PDOException $e) {
                                    error_log("Notification error: " . $e->getMessage());
                                    echo '<div class="error-message">Error loading notifications.</div>';
                                }
                                ?>
                            </div>
                        </div>
                        
                    <div id="invoicesTab" class="tab-content hidden">
                        <div class="invoices-list">
                            <?php
                            try {
                                // Get all bookings for the user with car details
                                $invoices_stmt = $pdo->prepare("
                                    SELECT 
                                        b.*,
                                        c.model,
                                        c.image,
                                        c.price,
                                        c.plate_no
                                    FROM bookings b
                                    LEFT JOIN car c ON b.car_id = c.id
                                    WHERE b.users_id = ?
                                    ORDER BY b.booking_date DESC
                                ");
                                
                                $invoices_stmt->execute([$user_id]);
                                $invoices = $invoices_stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($invoices)) {
                                    ?>
                                    <div class="no-invoices">
                                        <i class="fas fa-file-invoice"></i>
                                        <p>No bookings found</p>
                                        <p class="sub-text">Your booking invoices will appear here once you make a reservation.</p>
                                    </div>
                                    <?php
                                } else {
                                    foreach ($invoices as $invoice) {
                                        // Calculate rental duration and cost
                                        $start_date = new DateTime($invoice['booking_date']);
                                        $end_date = new DateTime($invoice['return_date']);
                                        $interval = $start_date->diff($end_date);
                                        $days = $interval->days ?: 1; // Minimum 1 day
                                        $price_per_day = floatval($invoice['price'] ?? 0);
                                        $total_amount = $days * $price_per_day;
                                        $status_class = strtolower($invoice['status']);
                                        ?>
                                        <div class="invoice-card-custom">
                                            <div class="invoice-card-content">
                                                <div class="invoice-car-image">
                                                    <?php if (!empty($invoice['image'])): ?>
                                                        <!-- Debug: Image path is ../../uploads/cars/<?= htmlspecialchars($invoice['image']) ?> -->
                                                        <img src="../../uploads/<?= htmlspecialchars($invoice['image']) ?>" 
                                                             alt="<?= htmlspecialchars($invoice['model']) ?>">
                                                    <?php else: ?>
                                                        <div class="no-image">No Image Available</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="invoice-details">
                                                    <div class="invoice-header-row">
                                                        <span class="invoice-car-model"><?= htmlspecialchars($invoice['model']) ?></span>
                                                        <span class="invoice-price-per-day">₱<?= number_format($price_per_day, 2) ?></span>
                                                    </div>
                                                    <div class="invoice-dates">
                                                        <?= $start_date->format('M d, Y') ?> to <?= $end_date->format('M d, Y') ?>
                                                    </div>
                                                    <div class="invoice-total-row">
                                                        <span class="invoice-total-label">Total Amount</span>
                                                        <span class="invoice-total-amount">₱<?= number_format($total_amount, 2) ?></span>
                                                    </div>
                                                    <a href="print_invoice.php?id=<?= $invoice['id'] ?>" class="print-invoice-link" target="_blank">
                                                        PRINT INVOICE
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                            } catch (PDOException $e) {
                                error_log("Invoice Error: " . $e->getMessage());
                                ?>
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <p>We encountered an error while loading your invoices.</p>
                                    <p class="sub-text">Please try refreshing the page or contact support if the problem persists.</p>
                                </div>
                                <?php
                            }
                            ?>
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
    <script src="../../javascript/notifications.js"></script>

    <!-- Font Awesome -->

    <div id="editProfileModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" id="closeEditModal">&times;</span>
            <h2>Edit Profile</h2>
            <form id="editProfileForm" enctype="multipart/form-data" method="POST">
                <?php if (!empty($success)): ?>
                    <div class="alert-success"> <?= htmlspecialchars($success) ?> </div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert-error"> <?= htmlspecialchars($error) ?> </div>
                <?php endif; ?>
                <h2>Personal Information</h2>
                <div class="form-group">
                    <label for="editProfilePicture">Profile Picture</label>
                    <input type="file" id="editProfilePicture" name="profile_picture" accept="image/*" onchange="previewEditProfileImage(event)">
                    <img src="<?= !empty($user['profile_picture']) ? '../../uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']) : '../../images/default-profile.jpg' ?>"
                         alt="Profile Preview" class="profile-image-preview" id="editProfileImagePreview" style="width:100px;height:100px;border-radius:50%;object-fit:cover;">
                </div>
                <div class="form-group">
                    <label for="editFirstName">First Name</label>
                    <input type="text" id="editFirstName" name="firstname" 
                           value="<?= htmlspecialchars($user['firstname'] ?? '') ?>" 
                           placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <label for="editLastName">Last Name</label>
                    <input type="text" id="editLastName" name="lastname" 
                           value="<?= htmlspecialchars($user['lastname'] ?? '') ?>"
                           placeholder="Last Name" required>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" 
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                           placeholder="Email" required>
                </div>
                <div class="form-group">
                    <label for="editPhone">Phone Number</label>
                    <input type="text" id="editPhone" name="phone"
                           value="<?= htmlspecialchars($user['customer_phone'] ?? '') ?>"
                           placeholder="Phone Number" required>
                </div>
                <h2>Account Information</h2>
                <div class="form-group">
                    <label for="editUsername">Username</label>
                    <input type="text" id="editUsername" name="username" 
                           value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                           placeholder="Username" required>
                </div>
                <button type="submit" class="btn">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Add this JavaScript before the closing body tag -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tab switching functionality
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            tabLinks.forEach(link => {
                link.addEventListener('click', function () {
                    // Remove active class from all tabs and hide all tab contents
                    tabLinks.forEach(link => link.classList.remove('active'));
                    tabContents.forEach(content => content.classList.add('hidden'));

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Show the corresponding tab content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.remove('hidden');
                });
            });

            // Edit profile modal functionality
            const editBtn = document.querySelector('.edit-icon-button');
            const modal = document.getElementById('editProfileModal');
            const closeBtn = document.getElementById('closeEditModal');

            if (editBtn) {
                editBtn.addEventListener('click', function () {
                    modal.style.display = "block";
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    modal.style.display = "none";
                });
            }

            window.addEventListener('click', function (event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            });
        });

        // Image preview for profile photo
        function previewEditProfileImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const output = document.getElementById('editProfileImagePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>


</body>
</html>