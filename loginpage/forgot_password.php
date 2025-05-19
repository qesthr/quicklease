<?php
session_start();
require_once '../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

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
            $mail->Username = 'joenilpanal@gmail.com'; // Gmail
            $mail->Password = 'zljg qdch thot ajiq'; // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('joenilpanal@gmail.com', 'Quicklease');
            $mail->addAddress($email, 'User');

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Code";
            $mail->Body = "<p>Hello, this is your password reset code: <strong>{$reset_code}</strong></p>";
            $mail->AltBody = "Hello, use this code to reset your password:\n\n{$reset_code}";

            $mail->send();

            $_SESSION['email_sent'] = true;
            $_SESSION['success'] = "A verification code has been sent to your email.";

            header('Location: enter_code.php');
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
    <link rel="stylesheet" href="../css/forgotpass.css">
</head>
<body class="forgot-password-page">
   <div>
        <div class="card">
            <div class="logo-wrapper">
                <img src="../images/logo.png" alt="Logo">
            </div>

            <h3 class="text-center mb-4"style="color:rgb(0, 0, 0)">Forgot Password</h3>

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

            <form action="forgot_password.php" method="POST">
                <div class="form-floating mb-3">
                    <input required type="email" name="email" class="form-control" placeholder="Email Address">
                </div>
                <button type="submit" class="btn">Send Code</button>
            </form>

            <div class="text-center mt-3">
                <p class="text-white"style="color:rgb(0, 0, 0)">Remember your password? <a href="login.php">Login</a></p>
            </div>
        </div>
   </div>
    

</body>
</html>
