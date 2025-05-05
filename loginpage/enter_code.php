<?php

session_start();

require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enterdCode = $_POST['code'];
    if (!isset($_SESSION['email'])) {
        $_SESSION['error'] = "No email session found. Please try again.";
        header('Location: forgot_password.php');
        exit(); 
    }
    $email = $_SESSION['email'];    

    $stmt = $pdo->prepare("SELECT reset_code FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ((string)$enterdCode == (string)$user['reset_code']) {
            $_SESSION ['reset_email'] = $email;
            $_SESSION ['reset_code_verified'] = true;

            header('Location: reset_password.php');
            exit();
        } else {
            $_SESSION['error'] = "Invalid code. Please try again";
            
        }
    } else {
        $_SESSION['error'] = "No user found with that email.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Enter Verification Code</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            padding: 40px 30px;
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
            max-width: 120px;
            margin: 0 auto 20px;
            display: block;
        }

        .card input[type="number"] {
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
                <img src="images/logo.png" alt="Logo">
            </div>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <form action="enter_code.php" method="POST">
                <input required type="number" name="code" placeholder="Enter the code" class="form-control">
                <button type="submit" class="btn btn-primary">Verify Code</button>
            </form>

        </div>
    </div>

</body>

</html>
