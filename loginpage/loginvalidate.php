<?php

    session_start();
    require __DIR__ . '/../db.php';

    // Only handle POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: login.php');
        exit;
    }

    // 1. reCAPTCHA validation
    if (empty($_POST['g-recaptcha-response'])) {
        $_SESSION['error'] = 'Please complete the reCAPTCHA.';
        header('Location: login.php');
        exit;
    }

    $recaptchaSecret = '6LekoywrAAAAAAZEMugrxdIejBkBJhx7gvXdaMk4';
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    // Build the verification URL
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $recaptchaSecret,
        'response' => $recaptchaResponse
    ]));

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $verify = curl_exec($ch);
    curl_close($ch);
    $responseData = json_decode($verify, true);


    // Verify with Google
    $verify = file_get_contents($verifyUrl);
    $responseData = json_decode($verify, true);

    if (empty($responseData['success'])) {
        $_SESSION['error'] = 'reCAPTCHA verification failed. Please try again.';
        header('Location: login.php');
        exit;
    }

    // 2. Credentials validation
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Login successful — store user info in session
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: ../loginpage/reports.php');
        exit;
    }

    // 3. If we get here, login failed
    $_SESSION['error'] = 'Invalid username or password.';
    header('Location: login.php');
    exit;


?>
