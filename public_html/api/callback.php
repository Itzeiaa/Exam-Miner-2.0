<?php

// api/oauth/google/callback
header("Content-Type: text/html; charset=utf-8");

// Optional: allow same-origin for your domain (not required for a redirect page)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, ['https://exam-miner.com','https://www.exam-miner.com'], true)) {
  header("Access-Control-Allow-Origin: $origin");
  header("Vary: Origin");
}

require __DIR__ . '/../../examminer/vendor/autoload.php';
require __DIR__ . '/google_config.php'; // << correct path
require __DIR__ . '/db.php'; // << correct path
// require 'https://exam-miner.com/api/oauth/google_config.php';
// require 'https://exam-miner.com/api/db.php'; // provides $conn (mysqli)

use Google\Client;
use Firebase\JWT\JWT;

// Fallback if not defined in google_config.php
if (!defined('APP_JWT_SECRET')) {
  define('APP_JWT_SECRET', 'exam-miner'); // keep in sync with your other API files
}


function uuidv4(): string {
  $d = random_bytes(16);
  $d[6] = chr((ord($d[6]) & 0x0f) | 0x40); // version 4
  $d[8] = chr((ord($d[8]) & 0x3f) | 0x80); // variant
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}
/*
function fail($msg){
  http_response_code(401);
  echo "<!doctype html><meta charset='utf-8'><title>Sign-in failed</title><pre>$msg</pre>";
  exit;
}
*/

function fail($msg){
  http_response_code(401);
  echo "<!doctype html>
  <html lang='en'>
  <meta charset='utf-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <title>Sign-in Failed</title>
  <style>
    :root{
      --bg1:#1e3a8a; --bg2:#3b82f6; --bg3:#60a5fa; --bg4:#93c5fd; --bg5:#1e40af; --bg6:#1d4ed8;
      --text:#0f172a; --muted:#64748b; --border:#e5e7eb; --card:#ffffff;
      --accent:#3b82f6; --accent2:#2563eb; --ok:#16a34a; --err:#dc2626;
      --radius:14px;
    }
    @keyframes gradientShift{
      0%{background-position:0% 50%}
      50%{background-position:100% 50%}
      100%{background-position:0% 50%}
    }
    *{box-sizing:border-box; margin:0}
    html,body{height:100%}
    body{
      font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      color:var(--text);
      background:linear-gradient(-45deg,var(--bg1),var(--bg2),var(--bg3),var(--bg4),var(--bg5),var(--bg6));
      background-size:400% 400%;
      animation:gradientShift 15s ease infinite;
      display:flex; align-items:center; justify-content:center;
      padding:24px;
    }
    .card{
      width:100%; max-width:480px; background:var(--card);
      border:1px solid var(--border); border-radius:var(--radius);
      box-shadow:0 10px 30px rgba(2,8,23,.08);
      padding:28px 28px 24px;
      text-align:center;
    }
    .title{
      font-size:26px; font-weight:700; color:var(--err);
      margin-bottom:10px;
    }
    .subtitle{
      color:var(--muted); font-size:15px; margin-bottom:20px;
    }
    pre{
      background:#f8fafc; border:1px solid var(--border);
      border-radius:10px; padding:12px; color:var(--text);
      font-size:14px; text-align:left; overflow-x:auto;
      margin-bottom:24px;
    }
    .btn{
      appearance:none; border:0; border-radius:12px; padding:13px 20px;
      font-weight:700; cursor:pointer;
      background:linear-gradient(135deg,var(--accent),var(--accent2));
      color:#fff; transition:transform .15s,box-shadow .2s;
      box-shadow:0 8px 20px rgba(59,130,246,.25);
      text-decoration:none; display:inline-block;
    }
    .btn:hover{transform:translateY(-1px)}
  </style>
  <body>
    <div class='card'>
      <div class='title'>Sign-in Failed</div>
      <div class='subtitle'>Something went wrong while signing in.</div>
      <pre>$msg</pre>
      <a href='/login' class='btn'>Go Back</a>
    </div>
  </body>
  </html>";
  exit;
}



try {
  // Debug logging
  error_log("OAuth Callback - GET params: " . json_encode($_GET));
  error_log("OAuth Callback - Request URI: " . $_SERVER['REQUEST_URI']);
  error_log("OAuth Callback - User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));
  error_log("OAuth Callback - Referer: " . ($_SERVER['HTTP_REFERER'] ?? 'Unknown'));
  
  // Check for OAuth errors first
  if (isset($_GET['error'])) {
    $error = $_GET['error'];
    $errorDescription = $_GET['error_description'] ?? 'Unknown error';
    
    switch ($error) {
      case 'access_denied':
        fail('Access was denied. Please try again and make sure to grant all requested permissions.');
        break;
      case 'invalid_request':
        fail('Invalid request. Please try signing in again.');
        break;
      case 'unauthorized_client':
        fail('OAuth configuration error. Please contact support.');
        break;
      case 'unsupported_response_type':
        fail('OAuth configuration error. Please contact support.');
        break;
      case 'invalid_scope':
        fail('OAuth configuration error. Please contact support.');
        break;
      case 'server_error':
        fail('Google server error. Please try again later.');
        break;
      case 'temporarily_unavailable':
        fail('Google service temporarily unavailable. Please try again later.');
        break;
      default:
        fail("OAuth error: $error - $errorDescription");
    }
  }
  
  if (empty($_GET['code'])) fail('Missing authorization code.');

  $client = new Client();
  $client->setClientId(GOOGLE_CLIENT_ID);
  $client->setClientSecret(GOOGLE_CLIENT_SECRET);
  $client->setRedirectUri(GOOGLE_REDIRECT_URI);

  // Exchange code → tokens (contains id_token)
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  if (isset($token['error'])) fail('Token exchange error: '.$token['error']);

  $idToken = $token['id_token'] ?? null;
  if (!$idToken) fail('No id_token in response.');

  // Verify ID token (signature + claims)
  $payload = $client->verifyIdToken($idToken);
  if (!$payload) fail('Failed verifying ID token.');
  if (($payload['aud'] ?? '') !== GOOGLE_CLIENT_ID) fail('Invalid audience.');
  $iss = $payload['iss'] ?? '';
  if ($iss !== 'https://accounts.google.com' && $iss !== 'accounts.google.com') fail('Invalid issuer.');

  // Extract user info from verified token
  $googleSub = $payload['sub'];               // Google user ID (stable)
  $email     = $payload['email'] ?? null;
  $verified  = (bool)($payload['email_verified'] ?? false);
  $name      = $payload['name'] ?? '';
  $picture   = $payload['picture'] ?? '';

  if (!$email || !$verified) fail('Google email not verified.');

  // 1) Try existing by google_sub
  $stmt = $conn->prepare("SELECT id, username, email, name, activated, COALESCE(profile_picture,'') AS profile_picture
                          FROM users WHERE google_sub = ? LIMIT 1");
  $stmt->bind_param("s", $googleSub);
  $stmt->execute();
  $res  = $stmt->get_result();
  $user = $res->fetch_assoc();
  $stmt->close();

  // 2) If not found, try link by verified email
  if (!$user) {
    $stmt = $conn->prepare("SELECT id, username, email, name, activated, COALESCE(profile_picture,'') AS profile_picture
                            FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if ($user) {
      // Link Google account to this email-based user
      $stmt = $conn->prepare("UPDATE users SET google_sub = ?, google_picture_url = ?, activated = 1 WHERE id = ? LIMIT 1");
      // id is UUID string → "s"
      $stmt->bind_param("sss", $googleSub, $picture, $user['id']);
      $stmt->execute();
      $stmt->close();
    }
  }

  // 3) If still not found, create a new user (UUID id)
  if (!$user) {
    // Generate a unique username from email local-part
    $base = preg_replace('/[^a-z0-9_]/i','', explode('@',$email)[0] ?: 'user');
    $username = $base;
    $i = 0;
    while (true) {
      $check = $conn->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
      $check->bind_param("s", $username);
      $check->execute(); $check->store_result();
      $exists = $check->num_rows > 0;
      $check->close();
      if (!$exists) break;
      $i++; $username = $base.$i;
    }

    $newId = uuidv4(); // UUID for users.id

    $stmt = $conn->prepare(
      "INSERT INTO users (id, username, email, name, activated, google_sub, google_picture_url, profile_picture)
       VALUES (?,  ?,        ?,     ?,    1,        ?,          ?,                 ?)"
    );
    // profile_picture: mirror Google photo initially so UI shows it
    $profilePic = $picture;
    $stmt->bind_param("sssssss", $newId, $username, $email, $name, $googleSub, $picture, $profilePic);
    if (!$stmt->execute()) fail('Failed creating user.');
    $stmt->close();

    $user = [
      'id'              => $newId,
      'username'        => $username,
      'email'           => $email,
      'name'            => $name,
      'activated'       => 1,
      'profile_picture' => $profilePic
    ];
  } else {
    // Optional: refresh name/picture each login
    $pp = $user['profile_picture'] ?: $picture; // keep custom pic if set; else Google pic
    $stmt = $conn->prepare("UPDATE users SET name = COALESCE(?, name), google_picture_url = ?, profile_picture = COALESCE(?, profile_picture) WHERE id = ? LIMIT 1");
    $stmt->bind_param("ssss", $name, $picture, $pp, $user['id']); // id is UUID string
    $stmt->execute();
    $stmt->close();
    $user['profile_picture'] = $pp;
  }

  // 4) Issue your app JWT (UUID in sub/user_id/id)
  $jwtPayload = [
    "sub"             => (string)$user['id'],
    "user_id"         => (string)$user['id'],
    "id"              => (string)$user['id'],
    "username"        => $user['username'],
    "email"           => $user['email'],
    "name"            => $user['name'],
    "profile_picture" => $user['profile_picture'] ?? '',
    "scope"           => "api",
    "iat"             => time(),
    "exp"             => time() + 60*60*24
  ];
  $appJwt = JWT::encode($jwtPayload, APP_JWT_SECRET, 'HS256');
  $appJwtJson = json_encode($appJwt);

  // 5) Hand the JWT back to the SPA
  echo <<<HTML
<!doctype html>
<meta charset="utf-8">
<title>Signing you in…</title>
<script>
  (function(){
    try { localStorage.setItem('jwt_token', $appJwtJson); } catch(e) {}
    if (window.opener) {
      try { window.opener.postMessage({type:'oauth_done', token: $appJwtJson}, '*'); } catch(e){}
      window.close();
    } else {
      location.href = '/dashboard';
    }
  })();
</script>
<p style="font-family:system-ui,Arial">Signing you in…</p>
HTML;

  exit;

} catch (Throwable $e) {
  fail('Unhandled error: '.$e->getMessage());
}
