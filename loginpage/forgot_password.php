<?php
session_start();
require_once '../db.php';

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
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('images/background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background-color:rgba(37, 41, 41, 0.86);
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 360px;
            animation: floatIn 0.5s ease;
        }

        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card img {
            max-width: 100px;
            margin-bottom: 20px;
        }

        .card input[type="email"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .card button {
            width: 100%;
            background-color: #2563eb;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .card button:hover {
            background-color: #1d4ed8;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-size: 14px;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #15803d;
        }

        .text-center a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="text-center mb-4">
                <img src="logo.png" alt="Logo" class="img-fluid" width="110px" height="110px">
            </div>

            <h3 class="text-center mb-4"style="color:rgb(255, 255, 255)">Forgot Password</h3>

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
                <button type="submit" class="btn btn-primary btn-block">Send Code</button>
            </form>

            <div class="text-center mt-3">
                <p class="text-white"style="color:rgb(255, 255, 255)">Remember your password? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>
