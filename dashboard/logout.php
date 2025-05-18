<?php
// Try to end admin session
session_name('admin_session');
session_start();
session_unset();
session_destroy();

// Try to end client session
session_name('client_session');
session_start();
session_unset();
session_destroy();

// Clear all cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-3600, '/');
    }
}

header("Location: ../loginpage/login.php");
exit();
?>