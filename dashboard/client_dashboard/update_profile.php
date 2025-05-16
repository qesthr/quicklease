<?php
session_start();
include_once '../../db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit();
}

// Get and sanitize input
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// Basic validation
if (!$firstname || !$lastname || !$email || !$phone) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit();
}

// Validate phone number (assuming Philippine format)
if (!preg_match('/^09\d{9}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number. It must start with 09 and be 11 digits.']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, customer_phone = ? WHERE id = ?");
    $success = $stmt->execute([$firstname, $lastname, $email, $phone, $user_id]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
