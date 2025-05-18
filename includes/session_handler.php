<?php
// Ensure this file is included at the start of every page
if (session_status() === PHP_SESSION_NONE) {
    // Set session configuration
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
}

// Function to start admin session
function startAdminSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    session_name('admin_session');
    session_start();
}

// Function to start client session
function startClientSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    session_name('client_session');
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// Function to check if user is client
function isClient() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'client';
}

// Function to enforce admin access
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = "Access denied. Admin privileges required.";
        header("Location: /quicklease/loginpage/login.php");
        exit();
    }
}

// Function to enforce client access
function requireClient() {
    if (!isClient()) {
        $_SESSION['error'] = "Access denied. Please log in as a client.";
        header("Location: /quicklease/loginpage/login.php");
        exit();
    }
}

// Function to enforce general login
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please log in to access this page.";
        header("Location: /quicklease/loginpage/login.php");
        exit();
    }
}
?> 