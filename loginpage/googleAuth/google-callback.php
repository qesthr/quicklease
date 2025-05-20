<?php
session_start();

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT']);

try {
    if (isset($_GET['code'])) { 
        // Exchange authorization code for access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (isset($token['error'])) {
            error_log('Google Token Error: ' . $token['error_description']);
            throw new Exception('Error fetching access token: ' . $token['error_description']);
        }   

        $client->setAccessToken($token['access_token']);

        // Fetch user information from Google
        $oauth2 = new Google_Service_Oauth2($client);
        $userinfo = $oauth2->userinfo->get();

        $email = $userinfo->email;
        $name = $userinfo->name;
        $picture = $userinfo->picture;

        // Check if the user exists in the database
        require_once '../../db.php';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // User exists, log them in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
        } else {
            // User does not exist, create a new account
$stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, user_type) VALUES (?, ?, ?, ?)");
$stmt->execute([$name, '', $email, 'client']); // Default role is 'client'

            // Log the user in
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $name;
            $_SESSION['user_type'] = 'client';
        }

        // Redirect based on role
        if ($_SESSION['user_type'] === 'admin') {
            header('Location: /quicklease/dashboard/reports.php'); // Admin dashboard
        } else {
            header('Location: /quicklease/dashboard/client_dashboard/client_profile_userdetails.php'); // Client dashboard
        }
        exit();
    } else {
        throw new Exception('No authorization code received.');
    }

} catch (Exception $e) {
    // Log the error (optional)
    error_log('Google login error: ' . $e->getMessage());

    // Redirect to login page with error message
    $_SESSION['error'] = 'Google login failed: ' . $e->getMessage();
    header('Location: http://localhost/quicklease/loginpage/login.php');
    exit();
}