<?php
// login.php — username OR email
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require 'db.php';
require 'vendor/autoload.php'; // firebase/php-jwt
require __DIR__ . '/../../examminer/vendor/autoload.php';

use Firebase\JWT\JWT;

$secret_key = "exam-miner";

/* ---------- read inputs (form or JSON) ---------- */
$login    = trim($_POST['username'] ?? $_POST['email'] ?? $_POST['login'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($login === '' || $password === '') {
  $raw = file_get_contents('php://input');
  if ($raw) {
    $j = json_decode($raw, true);
    if (is_array($j)) {
      $login    = $login    !== '' ? $login    : trim($j['username'] ?? $j['email'] ?? $j['login'] ?? '');
      $password = $password !== '' ? $password : (string)($j['password'] ?? '');
    }
  }
}

if ($login === '' || $password === '') {
  echo json_encode(["status" => "error", "message" => "Missing username/email or password"]);
  exit;
}

/* ---------- fetch user by username OR email ---------- */
/*
  Uses LOWER(email)=LOWER(?) so mixed-case emails work regardless of DB collation.
  LIMIT 1 assumes username and email are unique.
*/
$sql = "
  SELECT
    id,
    username,
    password_hash,
    activated,
    email,
    name,
    COALESCE(profile_picture,'') AS profile_picture
  FROM users
  WHERE username = ? OR LOWER(email) = LOWER(?)
  LIMIT 1
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo json_encode(["status" => "error", "message" => "SQL Error: " . $conn->error]);
  exit;
}
$stmt->bind_param("ss", $login, $login);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
  echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
  $stmt->close();
  $conn->close();
  exit;
}

$stmt->bind_result($user_id, $db_username, $hashed_password, $activated, $email, $name, $profile_picture);
$stmt->fetch();

/* ---------- activation check ---------- */
if ((int)$activated !== 1) {
  echo json_encode([
    "status"   => "unverified",
    "message"  => "Account not activated. Please confirm OTP.",
    "redirect" => "confirm_otp.html?email=" . urlencode($email) .
                  "&user_id=" . urlencode($user_id) .
                  "&username=" . urlencode($db_username)
  ], JSON_UNESCAPED_SLASHES);
  $stmt->close();
  $conn->close();
  exit;
}

/* ---------- password check ---------- */
if (!password_verify($password, $hashed_password)) {
  echo json_encode(["status" => "error", "message" => "Incorrect password"]);
  $stmt->close();
  $conn->close();
  exit;
}

/* ---------- build token (compatible with your APIs) ---------- */
$now = time();
$accessPayload = [
  "sub"      => (string)$user_id,   // standard
  "user_id"  => (string)$user_id,   // some endpoints expect user_id
  "id"       => (string)$user_id,   // some endpoints expect id
  "username" => $db_username,       // canonical username from DB
  "email"    => $email,
  "scope"    => "api",
  "iat"      => $now,
  "exp"      => $now + 60*60*24     // 1 day
];
$accessJwt = JWT::encode($accessPayload, $secret_key, 'HS256');

/* ---------- UI helper payload ---------- */
$ui = [
  "name"            => $name,
  "username"        => $db_username,
  "email"           => $email,
  "profile_picture" => $profile_picture // data URL or relative path
];

/* ---------- respond ---------- */
echo json_encode([
  "status"        => "success",
  "access_token"  => $accessJwt,   // keep old field
  "token"         => $accessJwt,   // alias for convenience
  "ui"            => $ui
], JSON_UNESCAPED_SLASHES);

$stmt->close();
$conn->close();

?>
