<?php
session_start();
require_once '../db.php';

// Make sure the reset request is valid
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_code_verified'])) {
    $_SESSION['error'] = "Invalid reset session.";
    header('Location: enter_code.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE email = ?");
        $stmt->execute([$hashedPassword, $_SESSION['reset_email']]);

        // Clear session data
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_code_verified']);

        $_SESSION['success'] = 'Your password has been reset successfully.';
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['error'] = 'Passwords do not match. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles/style.css">
    <meta charset="UTF-8">
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
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 350px;
            text-align: center;
        }

        .card img { 
            width: 90px;
            max-width: 80px;
            margin-bottom: 10px;
        }

        .card input[type="password"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .card button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            width: 90%;
            margin-top: 10px;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            width: 90%;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: rgb(38, 173, 83);
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: rgb(19, 215, 230);
            border: 1px solid #c3e6cb;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <img src="images/logo.png" alt="">

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            ?>

            <form action="reset_password.php" method="POST">
                <input required type="password" name="password" placeholder="New Password">
                <input required type="password" name="confirm_password" placeholder="Confirm Password">
                <button type="submit" style="cursor: pointer;">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>
