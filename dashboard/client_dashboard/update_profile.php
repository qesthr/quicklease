<?php
session_start();
include_once '../../db.php';

$client_id = $_SESSION['users_id'] ?? null;

if (!$client_id) {
    echo "You must be logged in.";
    exit();
}

// Get values from POST
$first_name = trim($_POST['firstname'] ?? '');
$last_name = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// Combine names
$full_name = $first_name . ' ' . $last_name;

// Basic validation
if (!$full_name || !$email || !$phone) {
    echo "All fields are required.";
    exit();
}

// Update query
$stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, customer_phone = ? WHERE id = ?");
$success = $stmt->execute([$first_name, $last_name, $email, $phone, $client_id]);

if ($success) {
    // Optionally redirect or confirm
    header("Location: client_profile_userdetails.php?updated=1");
    exit();
} else {
    echo "Failed to update profile.";
}
