<?php
session_start();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../css/loginandsignup.css">
    <meta charset="UTF-8">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- gogel fonts-->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
</head>

<body>


    <div class="container">
        <div class="card" style="text-align: center;">
            <img src="logo.png" alt="This is t  he Logo">

            <div class="card-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form action="/loginvalidate.php" method="post">
                    <input class="login-input" type="text" name="username" placeholder="Username" required>
                    <input class="login-input" type="password" name="password" placeholder="Password" required>

                    <!-- Google reCAPTCHA -->
                    <div class="g-recaptcha" data-sitekey="6LekoywrAAAAAP9aPlhhZ3_KnXgdrcdAXPdV6IoC"></div>
                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

                    <br>

                    <button type="submit">SUBMIT</button>
                </form>

                <p><a href="../loginpage/forgotpassword.php" style="color: black;">Forgot Password?</a></p>

                <!-- Divider -->
                <hr style="margin: 20px 0;">

                <!-- Google Sign-In Button -->
                <a href="google_signin.php">
                    <img src="https://developers.google.com/identity/images/btn_google_signin_dark_normal_web.png" alt="Sign in with Google" style="width: 200px; margin: 10px;">
                </a>

                <p>Do not have an account yet? <a href="/signup.php">Sign up</a></p>
            </div>
        </div>
    </div>
</body>
</html>
