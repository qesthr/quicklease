<?php

session_start();

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
        <div class="card" style="text-align: center; ">
            <img src="logo.png" alt="This is the Logo">

            <div class="card-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
                <?php endif ?>
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form action="login_validate.php" method="post">
                <input class="login-input" type="text" name="username" placeholder="Username" required>
                <input class="login-input" type="password" name="password" placeholder="Password" required>
                <button type="submit" style="cursor: pointer;">Login</button>
            </form>

            <p></p>
            <a href="forgot_password.php" style="color: black;">Forgot password?</a>                  
            
            <div class="divider" style="align-items: center;"> </div>
            <a style="margin-left: 10px;" href="signup.php">Signup</a>
        </div>
    </div>

 

</body>

</html>