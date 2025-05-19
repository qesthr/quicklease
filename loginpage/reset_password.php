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
    <link rel="stylesheet" href="../css/loginandsignup.css">
    <link rel="stylesheet" href="../css/resetpassword.css">
    <meta charset="UTF-8">
    
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="logo-wrapper">
                <img src="../images/logo.png" alt="Logo">

            </div>

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
