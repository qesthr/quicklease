<?php

session_start();

require 'includes/db.php';

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
        if ($enterdCode === $user['reset_code']) {
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
<html>


<head>
<title>Login</title>
<link rel="stylesheet" href="styles/style.css">
<meta charset="UTF-8">
<style>
    .alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
    width: 90%;
    text-align: center;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
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
                echo '<div class= "alert alert-danger" role="alert">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class= "alert alert-success" role="alert">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>
           <form action="enter_code.php" method="POST">
    <input required type="number" placeholder="Enter the code" name="code">
    <button type="submit" style="cursor: pointer;">Verify Code</button>
</form>

        </div>
    </div>

</body>

</html>