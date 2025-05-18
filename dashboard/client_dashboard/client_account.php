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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile</title>
    <link rel="stylesheet" href="../../css/client.css">
    <link rel="stylesheet" href="../../css/client-account.css">

</head>
<body class="client-account">

    <?php include __DIR__ . '/../client_dashboard/includes/sidebar.php'; ?>

    <div class="profile-container">

        <header>
            <?php include __DIR__ . '/../client_dashboard/includes/topbar.php'; ?>
        </header>

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
</body>
</html>