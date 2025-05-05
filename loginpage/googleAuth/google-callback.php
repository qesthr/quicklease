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
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (isset($token['error'])) {
            throw new Exception('Error fetching access token: ' . $token['error_description']);
        }   

        $client->setAccessToken($token['access_token']);

        $oauth2 = new Google_Service_Oauth2($client);
        $userinfo = $oauth2->userinfo->get();

        $_SESSION['user_type'] = 'google'; 
        $_SESSION['user_email'] = $userinfo->email;
        $_SESSION['user_name'] = $userinfo->name;
        $_SESSION['user_picture'] = $userinfo->picture;

        $_SESSION['success'] = 'Login with Google!';
        header('Location: http://localhost/ipt/dashboard/index.html');
        exit();
    } else {
        throw new Exception('No authorization code received.');
    }

} catch (Exception $e) {
    $_SESSION['error'] = 'Google login failed: ' . $e->getMessage();
    header('Location: http://localhost/ipt/login.php');
    exit();
}
