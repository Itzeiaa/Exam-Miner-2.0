<?php
// api/oauth/google_start.php
// require 'https://exam-miner.com/api/vendor/autoload.php';
require __DIR__ . '/../../examminer/vendor/autoload.php'; // << correct path
require __DIR__ . '/google_config.php'; // << correct path
// require 'https://exam-miner.com/api/oauth/google_config.php';

use Google\Client;

header("Access-Control-Allow-Origin: https://exam-miner.com");
header("Vary: Origin");

$client = new Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->setAccessType('online'); // we only need ID token, not refresh token
$client->setPrompt('consent'); // Force consent screen to show
$client->setIncludeGrantedScopes(true);
$client->setScopes([
  'openid',
  'email',
  'profile'
]);

$authUrl = $client->createAuthUrl();
header("Location: ".$authUrl);
exit;
