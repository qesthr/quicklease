<?php


session_start();

?>

<!DOCTYPE html>
<html>


<head>
<title>Signup</title>
<link rel="stylesheet" href="styles/style.css">
<meta charset="UTF-8">

</head>

<style>
    

</style>

<body>

    <div class="container">
        <div class="card" style="text-align: center;">
            <img src="logo.png" alt="">

            <?php if (isset($_SESSION['error'])): ?>
                <div class = "alert alert-danger" role = "after">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
                
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>


            <?php endif; ?>
                
            <form action="signup_validate.php" class="forms" method="post">
                <input class="signup-input" type="number" name="student_id" placeholder="Student ID No." required>
                <input class="signup-input" type="text" name="firstname" placeholder="First Name" required>
                <input class="signup-input" type="text" name="lastname" placeholder="Last Name" required>
                <input class="signup-input" type="text" name="username" placeholder="Username" required>
                <input class="signup-input" type="email" name="email" placeholder="Email" required>
                <input class="signup-input" type="password" name="password" placeholder="Password" required>
                <input class="signup-input" type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" style="cursor: pointer;">Signup</button>
                <a style="color: black;" href="login.html" href="login.php" >Already have an account?</a>
            </form>

        </div>
    </div>

</body>

</html>