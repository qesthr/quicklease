<?php

session_start();

require __DIR__ . '/../db.php';

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

    <!-- gogel fonts-->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>

<style>
    :root {
        --primary: #4a6bff;
        --primary-hover: #3a56d4;
        --text: #2d3748;
        --light-gray: #f7fafc;
        --gray: #e2e8f0;
        --dark-gray: #718096;
    }
    
    body {
        background-color: var(--light-gray);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }
    
    .reset-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 420px;
        padding: 40px;
        text-align: center;
    }
    
    
    .input-group {
        margin-bottom: 20px;
        text-align: left;
        margin-top: -126px;
        position: relative;
    }
    
    label {
        display: flex;
        color: var(--text);
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
        justify-content: center;
   
    }
    
    input {
        width: 92%;
        padding: 14px 16px;
        border: 1px solid var(--gray);
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.2s;
    }
    
    input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(74, 107, 255, 0.1);
    }
    
    input::placeholder {
        color: var(--dark-gray);
        opacity: 0.6;
    }
    
    .submit-btn {
        background-color: var(--primary);
        color: white;
        border: none;
        padding: 14px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 500;
        width: 100%;
        margin-top: -8px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .submit-btn:hover {
        background-color: var(--primary-hover);
    }

    .reset-card img {
        width: 100% !important;
        display: block;
        margin-left: auto;
        margin-right: auto;
        height: 20%;
        object-fit: contain;

        margin-top: -108px;
        margin-bottom: 53px;
    }

</style>

<body>
    <div class="reset-card">
        <img src="../images/logo.png" alt="Logo" style="">

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
        
        <form action="../loginpage/resetpassword.php" method="POST">
    
            <div class="input-group">
                <label for="code">Verification Code</label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    placeholder="Enter 6-digit code" 
                    required
                    maxlength="6"
                    pattern="\d{6}"
                    title="Please enter a 6-digit code"
                >
            </div>
            
            <button type="submit" class="submit-btn">Continue</button>
        </form>
    </div>
</body>
</html>