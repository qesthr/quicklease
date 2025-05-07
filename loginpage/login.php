<?php
session_start();
require_once __DIR__ . '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$siteKey = $_ENV['RECAPTCHA_SITE_KEY'];

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT']);
$client->addScope('email');
$client->addScope('profile');

$googleLoginUrl = $client->createAuthUrl();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-signin-client_id" content="<?= htmlspecialchars($_ENV['GOOGLE_CLIENT_ID']) ?>">

    <link rel="stylesheet" href="../css/loginandsignup.css">
    
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
</head>
<body>
    <div class="card">
        <img src="../images/logo.png" alt="Logo">

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form action="login_validate.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($siteKey) ?>"></div>
            <button type="submit">Login</button>
        </form>
        
        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">

        <div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark"></div>
            <a href="forgot_password.php">Forgot password?</a>
            <a href="signup.php">Create an account</a>
        </div>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function onSignIn(googleUser) {
            // Handle the Google Sign-In response here
            var profile = googleUser.getBasicProfile();
            console.log('ID: ' + profile.getId());
            console.log('Name: ' + profile.getName());
            console.log('Email: ' + profile.getEmail());
        }
    </script>
</body>
</html>
