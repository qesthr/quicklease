<?php
session_start();
require 'includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Student_ID = $_POST['student_id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username']; 
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        header('Location: signup.php');
        exit();
    }


    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Username already exists.";
        header('Location: signup.php');
        exit();
    }
  
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); 
    $stmt = $pdo->prepare("INSERT INTO users (student_id, firstname, lastname, username, email, password) VALUES (?, ?, ?, ?, ?, ?)");
   
    if ($stmt->execute([$Student_ID, $firstname, $lastname, $username, $email, $hashedPassword])) {
    $_SESSION['success'] = "Your account has been created. You can now Login.";
    header('Location: login.php');
    exit();
} else {
    echo("There is an error");
    exit();
}

}

?>
