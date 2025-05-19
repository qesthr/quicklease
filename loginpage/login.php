<?php

session_start();

error_log("Session ID: " . session_id());
error_log("Session contents: " . print_r($_SESSION, true));

// 1. Corrected path to autoload.php
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Load .env from absolute path with error handling
try {
    $dotenv = Dotenv\Dotenv::createImmutable('C:/xampp/htdocs/quicklease');
    $dotenv->load();
} catch (Exception $e) {
    // Set default values if .env file is not found
    $_ENV['RECAPTCHA_SITE_KEY'] = '';
    $_ENV['GOOGLE_CLIENT_ID'] = '';
    $_ENV['GOOGLE_CLIENT_SECRET'] = '';
    $_ENV['GOOGLE_REDIRECT'] = '';
    error_log("Warning: .env file not found. Using default values.");
}

// 3. Safely get environment variables with defaults
$siteKey = $_ENV['RECAPTCHA_SITE_KEY'] ?? '';
$googleClientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
$googleClientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
$googleRedirect = $_ENV['GOOGLE_REDIRECT'] ?? '';

// Verify you're getting the values (temporary debug)
error_log("reCAPTCHA Site Key: " . ($siteKey ? 'Set' : 'Missing'));
error_log("Google Client ID: " . ($googleClientId ? 'Set' : 'Missing'));

// 4. Initialize Google Client only if credentials exist
$googleLoginUrl = '';
if (!empty($googleClientId) && !empty($googleClientSecret)) {
    $client = new Google_Client();
    $client->setClientId($googleClientId);
    $client->setClientSecret($googleClientSecret);
    $client->setRedirectUri($googleRedirect);
    $client->addScope('email');
    $client->addScope('profile');
    $googleLoginUrl = $client->createAuthUrl();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QuickLease | Login Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- 5. CONDITIONAL GOOGLE META TAG -->
    <?php if (!empty($googleClientId)): ?>
    <meta name="google-signin-client_id" content="<?= htmlspecialchars($googleClientId) ?>">
    <?php endif; ?>
    
    <!-- 6. CONDITIONAL SCRIPT LOADING -->
    <?php if (!empty($siteKey)): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    
    <?php if (!empty($googleClientId)): ?>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <?php endif; ?>

    <link rel="stylesheet" href="../css/loginandsignup.css">

</head>
<body>
    <div class="card"> 
        <div class="logo-wrapper">
            <img src="../images/logo.png" alt="Logo">
        </div>

        <!-- 7. SESSION MESSAGE DISPLAY -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form class="login-forms" action="login_validate.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>

            <!-- 8. CONDITIONAL RECAPTCHA -->
            <?php if (!empty($siteKey)): ?>
                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($siteKey) ?>"></div>
            <?php endif; ?>
            
            <button type="submit">Login</button>
        </form>

        <!-- 9. CONDITIONAL GOOGLE SIGN-IN -->
            <?php if (!empty($googleClientId)): ?>
                <div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark"></div>
            <?php endif; ?>

        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">

        <a href="forgot_password.php">Forgot password?</a>
        <a href="signup.php">Create an account</a>

        <!-- 10. MOVED SCRIPT TO BOTTOM & CONDITIONAL -->
        <?php if (!empty($googleClientId)): ?>
            <script>
                function onSignIn(googleUser) {
                    var id_token = googleUser.getAuthResponse().id_token;

                    fetch('google_auth.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'idtoken=' + id_token
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'dashboard.php';
                        } else {
                            alert('Authentication failed: ' + data.message);
                        }
                    });
                }

                // Ensure gapi loads
                window.onload = function() {
                    gapi.load('auth2', function() {
                    gapi.auth2.init({
                        client_id: "<?= htmlspecialchars($googleClientId) ?>"
                    });
                    });
                };
            </script>
        <?php endif; ?>
    </div>

    <script src="https://www.google.com/recaptcha/api.js?render=your_site_key"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=6LekoywrAAAAAP9aPlhhZ3_KnXgdrcdAXPdV6IoC"></script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('6LekoywrAAAAAP9aPlhhZ3_KnXgdrcdAXPdV6IoC', {action: 'login'}).then(function(token) {
                document.querySelector('form.login-forms').insertAdjacentHTML('beforeend',
                    '<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
            });
        });
    </script>


</body>
</html>