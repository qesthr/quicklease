<?php
session_start();
require 'includes/db.php';

// Load .env variables
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Step 1: reCAPTCHA verification
    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        $_SESSION['error'] = 'Please complete the reCAPTCHA.';
        header('Location: login.php');
        exit();
    }

    $recaptchaSecret = $_ENV['RECAPTCHA_SECRET_KEY'];
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    
    $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
    $captchaSuccess = json_decode($verifyResponse, true);
    
    if (!$captchaSuccess['success']) {
        $_SESSION['error'] = 'reCAPTCHA verification failed. Please try again.';
        header('Location: login.php');
        exit();
    }

    // ✅ Step 2: Proceed with login validation
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Login successful — store user info in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Store the user's role in the session

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header('Location: dashboard/reports.php'); // Admin dashboard
        } else if ($user['role'] === 'client') {
            header('Location: dashboard/client_dashboard/client_booking.php'); // Client dashboard
        }
        exit();
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header('Location: login.php');
        exit();
    }
}
?>