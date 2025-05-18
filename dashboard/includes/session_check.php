<?php
// Set session timeout to 24 hours (must be before session_start)
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);

session_start();

// Check if user is logged in
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /quicklease/loginpage/login.php");
        exit();
    }
}

// Check if user is admin
function checkAdmin() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        header("Location: /quicklease/loginpage/login.php");
        exit();
    }
}

// Check if user is client
function checkClient() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'client') {
        header("Location: /quicklease/loginpage/login.php");
        exit();
    }
}
?> 