<?php
session_start();
require __DIR__ . '/../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

ob_start(); // Fix redirect issue (no output before header)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $reset_code = rand(100000, 999999);

        $update = $pdo->prepare("UPDATE users SET reset_code = ? WHERE email = ?");
        $update->execute([$reset_code, $email]);

        $_SESSION['email'] = $email;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'queennina.dlr@gmail.com'; // Gmail
            $mail->Password = 'kkjk vqib twnj dxlg'; // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('queennina.dlr@gmail.com', 'Queen de los Reyes');
            $mail->addAddress($email, 'User');

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Code";
            $mail->Body = "<p>Hello, this is your password reset code: <strong>{$reset_code}</strong></p>";
            $mail->AltBody = "Hello, use this code to reset your password:\n\n{$reset_code}";

            $mail->send();

            $_SESSION['email_sent'] = true;
            $_SESSION['success'] = "A verification code has been sent to your email.";

            header('Location: ../loginpage/entercode.php');
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
            header('Location: forgot_password.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "No user found with that email.";
        header('Location: forgot_password.php');
        exit();
    }
}

ob_end_flush(); // End buffering
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/loginandsignup.css">

    <!-- gogel fonts-->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>

<style>
    .card img {
        width: 100% !important;
        display: block;
        margin-left: auto;
        margin-right: auto;
        height: 20%;
        object-fit: contain;
    }

    form {
        margin-top: 0 !important;
    }
</style>


<body>

    <div class="container">
        <div class="card forgot-password-card" >
            <img src="../images/logo.png" alt="QuickLease Logo">
            
            <div class="card-body" style="margin: 10px;">
                <h3 style="margin-top: -99px;">Forgot Password</h3>
                
                <!-- Your form and other content -->
                <?php
                    if (isset($_SESSION['success'])) {
                        echo '<div class="alert alert-success text-center">' . $_SESSION['success'] . '</div>';
                        unset($_SESSION['success']);
                    }
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
                        unset($_SESSION['error']);
                    }
                ?>

                <form action="forgotpassword.php" method="POST" style="margin: 7px;">
                    <div class="form-floating mb-3" style="margin-top: 20px;">
                        <input required type="email" name="email" class="form-control" placeholder="Email Address">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Send Code</button>
                </form>
     
                <div class="login-link" style="margin-top: 15px;">
                    <p>Remember your password?</p> 
                    <a href="login.php" style="margin-left: -5px;">Login</a>
                </div>
            </div>
           

            
        </div>
    </div>

</body>
</html>
