<?php
session_start();
require_once __DIR__ . '/../db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username']; 
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        header('Location: signup.php');
        exit();
    }

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Username already exists.";
        header('Location: signup.php');
        exit();
    }
  
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); 

    // Insert user into the database
    $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, username, email, password) VALUES (?, ?, ?, ?, ?)");
   
    if ($stmt->execute([$firstname, $lastname, $username, $email, $hashedPassword])) {
        $_SESSION['success'] = "Your account has been created. You can now Login.";
        header('Location: login.php');
        exit();
    } else {
        echo("There is an error");
        exit();
    }
}

?>