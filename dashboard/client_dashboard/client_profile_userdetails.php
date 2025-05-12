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

                    <?php if ($success): ?>
                        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php elseif ($error): ?>
                        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

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
                                    <div class="id-verification-container">
                                        <div class="id-left">
                                            <?php if (!empty($user['submitted_id'])): ?>
                                                <img id="idPreview" src="../../uploads/<?= htmlspecialchars($user['submitted_id']) ?>" alt="Submitted ID" class="submitted-id">
                                            <?php else: ?>
                                                <p>No ID uploaded yet.</p>
                                                <img id="idPreview" src="#" alt="ID Preview" class="submitted-id hidden">
                                            <?php endif; ?>

                                            <label for="idImage">Upload New ID:</label>
                                            <input type="file" id="idImage" accept="image/*">
                                        </div>

                                        <?php if ($isAdmin): ?>
                                        <div class="id-right">
                                            <span id="statusText" class="<?= $user['is_verified'] ? 'verified' : 'not-verified' ?>">
                                                <?= $user['is_verified'] ? 'Verified' : 'Not Verified' ?>
                                            </span>
                                            <button id="verifyBtn">
                                                <?= $user['is_verified'] ? 'Mark as Not Verified' : 'Mark as Verified' ?>
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="notificationsTab" class="tab-content hidden">
                            
                        </div>
                        
                        <div id="invoicesTab" class="tab-content hidden">

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
            <form id="editProfileForm">
                <div class="form-group">
                    <label for="editFirstName">First Name</label>
                    <input type="text" id="editFirstName" name="firstname" 
                           placeholder="<?= htmlspecialchars($user['firstname'] ?? '') ?>" 
                           onfocus="this.value = this.getAttribute('<?= htmlspecialchars($user['firstname'] ?? '') ?>')"
                           onblur="if(!this.value) this.value = this.getAttribute('<?= htmlspecialchars($user['firstname'] ?? '') ?>')">
                </div>
                <div class="form-group">
                    <label for="editLastName">Last Name</label>
                    <input type="text" id="editLastName" name="lastname" 
                           placeholder="<?= htmlspecialchars($user['lastname'] ?? '') ?>"
                           onfocus="this.value = this.getAttribute('<?= htmlspecialchars($user['lastname'] ?? '') ?>')"
                           onblur="if(!this.value) this.value = this.getAttribute('<?= htmlspecialchars($user['lastname'] ?? '') ?>')">
                </div>
                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" 
                           placeholder="<?= htmlspecialchars($user['email'] ?? '') ?>"
                           onfocus="this.value = this.getAttribute('<?= htmlspecialchars($user['email'] ?? '') ?>')"
                           onblur="if(!this.value) this.value = this.getAttribute('<?= htmlspecialchars($user['email'] ?? '') ?>')">
                </div>
                <div class="form-group">
                    <label for="editPhone">Phone Number</label>
                    <input type="text" id="editPhone" name="phone"
                           placeholder="<?= htmlspecialchars($user['customer_phone'] ?? '') ?>"
                           onfocus="this.value = this.getAttribute('<?= htmlspecialchars($user['customer_phone'] ?? '') ?>')"
                           onblur="if(!this.value) this.value = this.getAttribute('<?= htmlspecialchars($user['customer_phone'] ?? '') ?>')">
                </div>
                <div class="form-group">
                    <label for="editUsername">Username</label>
                    <input type="text" id="editUsername" name="username" 
                           placeholder="<?= htmlspecialchars($user['username'] ?? '') ?>"
                           onfocus="this.value = this.getAttribute('<?= htmlspecialchars($user['username'] ?? '') ?>')"
                           onblur="if(!this.value) this.value = this.getAttribute('<?= htmlspecialchars($user['username'] ?? '') ?>')">
                </div>
                <button type="submit" class="btn">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>
