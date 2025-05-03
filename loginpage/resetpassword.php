<?php
session_start();
require __DIR__ . '/../db.php';;

// Make sure the reset request is valid
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_code_verified'])) {
    $_SESSION['error'] = "Invalid reset session.";
    header('Location: ../loginpage/entercode.php');
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset | QuickLease</title>

    <link rel="stylesheet" href="../css/loginandsignup.css">

    <!-- gogel fonts-->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4a6bff;
            --primary-hover: #3a56d4;
            --text: #2d3748;
            --light-gray: #f7fafc;
            --gray: #e2e8f0;
            --dark-gray: #718096;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
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
        
        .logo {
            color: var(--primary);
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            letter-spacing: -0.5px;
        }
        
        h1 {
            color: var(--text);
            font-size: 22px;
            margin-bottom: 24px;
            font-weight: 600;
        }
        
        .input-group {
            margin-bottom: 20px;
            text-align: left;
            position: relative;

        }
        
        label {
            display: block;
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        input {
            width: 100%;
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
            margin-top: 10px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .submit-btn:hover {
            background-color: var(--primary-hover);
        }

        .reset-card img {
            width: 358px;
            height: auto;
            display: block;
            margin: -84px auto 30px auto;
            object-fit: contain;
            max-width: 100%;
        }

        /* For mobile responsiveness */
        @media (max-width: 480px) {
            .reset-card img {
                width: 140px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <img src="../images/logo.png" alt="Logo" style="">
        
        <form action="../loginpage/resetpassword.php" method="POST">
            <div class="input-group">
                <label for="email">New Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your new password" 
                    required
                >
            </div>
            
            <div class="input-group">
                <label for="code">Re-enter new password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    placeholder="Re-enter new password" 
                    required
                >
            </div>
            
            <button type="submit" class="submit-btn">Continue</button>
        </form>
    </div>
</body>
</html>
