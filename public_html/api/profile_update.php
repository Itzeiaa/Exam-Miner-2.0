<?php
// profile_update.php  — UPDATED
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require 'db.php';
require 'vendor/autoload.php'; // firebase/php-jwt
require __DIR__ . '/../../examminer/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "exam-miner"; // must match login.php

/* ---------- helpers ---------- */
function json_error($msg, $code = 400) {
  http_response_code($code);
  echo json_encode(["status" => "error", "message" => $msg], JSON_UNESCAPED_SLASHES);
  exit;
}
function bearerTokenFromHeaders(): ?string {
  // Common server vars
  if (!empty($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\s+(\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) return $m[1];
  if (!empty($_SERVER['Authorization']) && preg_match('/Bearer\s+(\S+)/i', $_SERVER['Authorization'], $m)) return $m[1];
  // Fallback for environments with getallheaders()
  if (function_exists('getallheaders')) {
    $h = getallheaders();
    foreach (['Authorization','authorization','HTTP_AUTHORIZATION'] as $k) {
      if (!empty($h[$k]) && preg_match('/Bearer\s+(\S+)/i', $h[$k], $m)) return $m[1];
    }
  }
  return null;
}

/**
 * Compress an uploaded image to max 256px (keeping aspect), return data URL.
 * Supports JPEG/PNG/WEBP input. Output uses JPEG or PNG/WEBP when alpha.
 */
function compress_uploaded_image_to_data_url(array $file): ?string {
  if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;

  // Basic filesize guard (10MB)
  if (!empty($file['size']) && $file['size'] > 10 * 1024 * 1024) return null;

  $info = @getimagesize($file['tmp_name']);
  if (!$info) return null;
  $mime = $info['mime'];

  switch ($mime) {
    case 'image/jpeg': $src = @imagecreatefromjpeg($file['tmp_name']); $outMime = 'image/jpeg'; break;
    case 'image/png':  $src = @imagecreatefrompng($file['tmp_name']);  $outMime = 'image/png';  break;
    case 'image/webp':
      if (!function_exists('imagecreatefromwebp')) return null;
      $src = @imagecreatefromwebp($file['tmp_name']); $outMime = 'image/webp'; break;
    default: return null;
  }
  if (!$src) return null;

  $w = imagesx($src); $h = imagesy($src);
  $max = 256;
  $scale = min($max / $w, $max / $h, 1); // don't upscale
  $nw = max(1, (int)floor($w * $scale));
  $nh = max(1, (int)floor($h * $scale));

  $dst = imagecreatetruecolor($nw, $nh);

  // handle transparency for PNG/WEBP
  if ($outMime !== 'image/jpeg') {
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $nw, $nh, $trans);
  }

  imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

  ob_start();
  if ($outMime === 'image/jpeg') {
    imagejpeg($dst, null, 82);
  } elseif ($outMime === 'image/png') {
    imagepng($dst, null, 6);
  } else { // webp
    if (function_exists('imagewebp')) imagewebp($dst, null, 80);
    else { imagejpeg($dst, null, 82); $outMime = 'image/jpeg'; }
  }
  $bytes = ob_get_clean();

  imagedestroy($src);
  imagedestroy($dst);

  if (!$bytes) return null;
  return 'data:' . $outMime . ';base64,' . base64_encode($bytes);
}

/* ---------- read & verify JWT ---------- */
$jwt = bearerTokenFromHeaders();
if (!$jwt && isset($_POST['token'])) $jwt = trim($_POST['token']);
// optional fallback if you ever pass ?token= in URL
if (!$jwt && isset($_GET['token']))  $jwt = trim($_GET['token']);
if (!$jwt) json_error("Missing token", 401);

try {
  $payload = JWT::decode($jwt, new Key($secret_key, 'HS256'));
} catch (Throwable $e) {
  json_error("Invalid or expired token", 401);
}

// Accept multiple possible claim names for user id
$user_id = $payload->user_id ?? $payload->id ?? $payload->sub ?? null;
if (!$user_id) json_error("Token missing user_id", 401);

// If your DB uses INT primary keys:
$user_id_str = (string)$user_id;


/* ---------- inputs (email is ignored here) ---------- */
$name     = isset($_POST['name'])     ? trim($_POST['name']) : null;
$password = isset($_POST['password']) ? (string)$_POST['password'] : null;

// uploaded file (optional)
$avatarDataUrl = null;
if (!empty($_FILES['profile_photo']) && is_uploaded_file($_FILES['profile_photo']['tmp_name'])) {
  $avatarDataUrl = compress_uploaded_image_to_data_url($_FILES['profile_photo']);
  if ($avatarDataUrl === null) {
    json_error("Unsupported or invalid image. Use JPG/PNG/WEBP under a few MB.");
  }
}

if (($name === null || $name === '') && ($password === null || $password === '') && $avatarDataUrl === null) {
  json_error("Nothing to update");
}
if ($password !== null && $password !== '' && strlen($password) < 6) {
  json_error("Password must be at least 6 characters");
}

/* ---------- ensure user exists ---------- */
$stmt = $conn->prepare("SELECT id, username, email, name, activated, COALESCE(profile_picture,'') AS profile_picture FROM users WHERE id = ?");
if (!$stmt) json_error("SQL Error: ".$conn->error);
$stmt->bind_param("s", $user_id_str);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) json_error("User not found", 404);
$current = $res->fetch_assoc();
$stmt->close();

/* ---------- dynamic update (no email change here) ---------- */
$fields = [];
$params = [];
$types  = '';

if ($name !== null && $name !== '') {
  $fields[] = "name = ?";
  $params[] = $name; $types .= 's';
}
if ($password !== null && $password !== '') {
  $hash = password_hash($password, PASSWORD_BCRYPT);
  $fields[] = "password_hash = ?";
  $params[] = $hash; $types .= 's';
}
if ($avatarDataUrl !== null) {
  $fields[] = "profile_picture = ?";
  $params[] = $avatarDataUrl; $types .= 's';
}

if (!empty($fields)) {
  $sql = "UPDATE users SET ".implode(', ', $fields)." WHERE id = ?";
  $stmt = $conn->prepare($sql);
  if (!$stmt) json_error("SQL Error: ".$conn->error);
  $types .= 's';
  $params[] = $user_id_str;
  $stmt->bind_param($types, ...$params);
  if (!$stmt->execute()) { $stmt->close(); json_error("Failed to update profile"); }
  $stmt->close();
}

/* ---------- fetch updated & issue fresh JWT (now includes multiple id claims) ---------- */
$stmt = $conn->prepare("SELECT id, username, email, name, activated, COALESCE(profile_picture,'') AS profile_picture FROM users WHERE id = ?");
$stmt->bind_param("s", $user_id_str);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

$new_payload = [
  // include all three so any consumer works
  "sub"             => (string)$u['id'],
  "user_id"         => (string)$u['id'],
  "id"              => (string)$u['id'],
  "username"        => $u['username'],
  "email"           => $u['email'],   // unchanged
  "name"            => $u['name'],
  "profile_picture" => $u['profile_picture'] ?: null,
  "scope"           => "api",
  "iat"             => time(),
  "exp"             => time() + 60*60*24
];
$new_token = JWT::encode($new_payload, $secret_key, 'HS256');

/* ---------- respond ---------- */
echo json_encode([
  "status"  => "success",
  "message" => "Profile updated",
  "user"    => [
    "id"              => $u['id'],
    "username"        => $u['username'],
    "email"           => $u['email'],
    "name"            => $u['name'],
    "activated"       => (int)$u['activated'],
    "profile_picture" => $u['profile_picture'], // base64 data URL or ""
    "updated_at"      => date('Y-m-d H:i:s')
  ],
  // keep key name consistent with login.php
  "token"         => $new_token,
  "access_token"  => $new_token
], JSON_UNESCAPED_SLASHES);

$conn->close();
