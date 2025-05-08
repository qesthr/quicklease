// File: client_account.php
// Description: This file handles the client account page, including image upload functionality.
<?php
// filepath: c:\xamppss\htdocs\quicklease\dashboard\client_dashboard\client_account.php
include '../../db.php';
session_start();

// Assuming the client is logged in and their ID is stored in the session
$client_id = $_SESSION['client_id'] ?? null;

if (!$client_id) {
    echo "You must be logged in to perform this action.";
    exit();
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['submitted_id'])) {
    $target_dir = "../../uploads/";
    $file_name = time() . "_" . basename($_FILES['submitted_id']['name']); // Add timestamp to avoid duplicate names
    $target_file = $target_dir . $file_name;
    $upload_ok = true;

    // Validate file type
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if (!in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "Only JPG, JPEG, PNG, and GIF files are allowed.";
        $upload_ok = false;
    }

    // Validate file size (max 2MB)
    if ($_FILES['submitted_id']['size'] > 2 * 1024 * 1024) {
        echo "File size must not exceed 2MB.";
        $upload_ok = false;
    }

    if ($upload_ok) {
        if (move_uploaded_file($_FILES['submitted_id']['tmp_name'], $target_file)) {
            // Update the database with the uploaded file name
            $stmt = $pdo->prepare("UPDATE customer SET submitted_id = ? WHERE id = ?");
            $stmt->execute([$file_name, $client_id]);
            echo "success";
        } else {
            echo "There was an error uploading your file.";
        }
    }
    exit();
} else {
    echo "Invalid request.";
    exit();
}
?>