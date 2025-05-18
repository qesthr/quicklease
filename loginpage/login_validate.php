<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/session_handler.php';
require_once __DIR__ . '/../db.php';

// // Load .env variables
// require_once __DIR__ . '/vendor/autoload.php';
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

// // Check if form was submitted
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // // ✅ Step 1: reCAPTCHA verification
    // if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
    //     $_SESSION['error'] = 'Please complete the reCAPTCHA.';
    //     header('Location: login.php');
    //     exit();
    // }

    // $recaptchaSecret = $_ENV['RECAPTCHA_SECRET_KEY'];
    // $recaptchaResponse = $_POST['g-recaptcha-response'];
    
    // $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
    // $captchaSuccess = json_decode($verifyResponse, true);
    
    // if (!$captchaSuccess['success']) {
    //     $_SESSION['error'] = 'reCAPTCHA verification failed. Please try again.';
    //     header('Location: login.php');
    //     exit();
    // }

    // ✅ Step 2: Proceed with login validation
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Debug output
    echo "Attempting login with username: " . htmlspecialchars($username) . "<br>";

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo "User found in database<br>";
            echo "User type: " . htmlspecialchars($user['user_type']) . "<br>";
            
            if (password_verify($password, $user['password'])) {
                echo "Password verified successfully<br>";
                
                // Start the appropriate session based on user type
                if ($user['user_type'] === 'admin') {
                    startAdminSession();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];
                    header('Location: /quicklease/dashboard/reports.php');
                    exit();
                } else if ($user['user_type'] === 'client') {
                    startClientSession();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];
                    header('Location: /quicklease/dashboard/client_dashboard/client_profile_userdetails.php');
                    exit();
                } else {
                    session_start();
                    $_SESSION['error'] = "Invalid user type. Please contact support.";
                    header('Location: login.php');
                    exit();
                }
            } else {
                echo "Password verification failed<br>";
                session_start();
                $_SESSION['error'] = "Invalid username or password.";
                header('Location: login.php');
                exit();
            }
        } else {
            echo "User not found in database<br>";
            session_start();
            $_SESSION['error'] = "Invalid username or password.";
            header('Location: login.php');
            exit();
        }
    } catch (Exception $e) {
        echo "Error during login: " . $e->getMessage() . "<br>";
        session_start();
        $_SESSION['error'] = "An error occurred during login. Please try again.";
        header('Location: login.php');
        exit();
    }

?>