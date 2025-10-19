<?php
// api/_auth.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require __DIR__ . '/db.php';
require __DIR__ . '/vendor/autoload.php'; // << correct path
require __DIR__ . '/../../examminer/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$SECRET_KEY = "exam-miner"; // keep in sync with login.php

/* ---------- helpers ---------- */
function json_error($msg, $code = 400) {
  http_response_code($code);
  // DO NOT set Content-Type here globally; let endpoints set it if they return JSON
  echo json_encode(["status"=>"error","message"=>$msg], JSON_UNESCAPED_SLASHES);
  exit;
}
function json_ok($arr) {
  if (!is_array($arr)) $arr = ["data" => $arr];
  if (!isset($arr["status"])) $arr["status"] = "success";
  header('Content-Type: application/json'); // safe here because callers are JSON endpoints
  echo json_encode($arr, JSON_UNESCAPED_SLASHES);
  exit;
}

function bearer_token(): ?string {
  // Common server vars
  foreach (['HTTP_AUTHORIZATION','Authorization','REDIRECT_HTTP_AUTHORIZATION'] as $h) {
    if (!empty($_SERVER[$h]) && preg_match('/Bearer\s+(\S+)/i', $_SERVER[$h], $m)) return $m[1];
  }
  // getallheaders fallback
  if (function_exists('getallheaders')) {
    foreach (getallheaders() as $k => $v) {
      if (strcasecmp($k,'Authorization')===0 && preg_match('/Bearer\s+(\S+)/', $v, $m)) return $m[1];
    }
  }
  // permissive fallbacks used elsewhere in your codebase
  if (!empty($_POST['token'])) return trim((string)$_POST['token']);
  if (!empty($_GET['token']))  return trim((string)$_GET['token']);
  return null;
}

function auth_payload() {
  global $SECRET_KEY;
  $jwt = bearer_token();
  if (!$jwt) json_error("Missing token", 401);
  try {
    return JWT::decode($jwt, new Key($SECRET_KEY, 'HS256'));
  } catch (Throwable $e) {
    json_error("Invalid or expired token", 401);
  }
}

/**
 * Returns the authenticated user id as a STRING (UUID-safe).
 * Accepts user_id | id | sub, or falls back to username/email in the token.
 */
function auth_user_id(): string {
  global $conn;
  $p = auth_payload();

  $uid = $p->user_id ?? $p->id ?? $p->sub ?? null;
  if ($uid) return (string)$uid; // do NOT cast to int

  $email    = $p->email    ?? null;
  $username = $p->username ?? null;
  if (!$email && !$username) json_error("Token missing user identifier", 401);

  if ($email && $username) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1") ?: json_error("SQL Error: ".$conn->error, 500);
    $stmt->bind_param("ss", $email, $username);
  } elseif ($email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1") ?: json_error("SQL Error: ".$conn->error, 500);
    $stmt->bind_param("s", $email);
  } else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1") ?: json_error("SQL Error: ".$conn->error, 500);
    $stmt->bind_param("s", $username);
  }
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) json_error("User not found for token", 401);
  return (string)$row['id'];
}
