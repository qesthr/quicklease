<?php
session_start();
include_once '../../db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['idImage']) || $_FILES['idImage']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
    exit();
}

$file = $_FILES['idImage'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG and PNG allowed.']);
    exit();
}

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024; // 5MB in bytes
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
    exit();
}

try {
    // Read file content
    $image_data = file_get_contents($file['tmp_name']);
    
    // Check if user already has submitted ID
    $check_stmt = $pdo->prepare("SELECT submitted_id FROM users WHERE id = ?");
    $check_stmt->execute([$user_id]);
    $user = $check_stmt->fetch();
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'id_' . $user_id . '_' . time() . '.' . $file_extension;
    $upload_path = '../../uploads/ids/' . $new_filename;
    
    // Create directory if it doesn't exist
    if (!file_exists('../../uploads/ids/')) {
        mkdir('../../uploads/ids/', 0777, true);
    }
    
    // Save file to uploads directory
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Update user's submitted_id field and set initial verification status
        $stmt = $pdo->prepare("UPDATE users SET submitted_id = ?, verification_status = 'Pending' WHERE id = ?");
        $success = $stmt->execute([$new_filename, $user_id]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'ID uploaded successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user record.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
