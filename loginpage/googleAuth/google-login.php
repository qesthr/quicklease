<?php
require_once '../vendor/autoload.php';

$client = new Google_Client();
$client->setClientId($_ENV['YOUR_CLIENT_ID']);
$client->setClientSecret($_ENV['YOUR_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT']);
$client->addScope('email');
$client->addScope('profile');

$login_url = $client->createAuthUrl();
?>

<a href="<?= htmlspecialchars($login_url) ?>">Login with Google</a>
