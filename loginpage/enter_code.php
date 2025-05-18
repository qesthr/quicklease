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
    <link rel="stylesheet" href="../css/loginandsignup.css">

    <link rel="stylesheet" href="../css/entercode.css">

</head>

<body class="enter-code-page">
    <div class="card">
        <div class="">
            <div class="logo-wrapper">
                <img src="../images/logo.png" alt="Logo">
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
