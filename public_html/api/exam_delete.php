<?php
// api/exam_delete.php (cascade + file cleanup)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require 'db.php';
require 'vendor/autoload.php';
require __DIR__ . '/../../examminer/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "exam-miner";

// Where file-backed bodies are stored (keep in sync with save/update)
$EXAM_STORAGE_ROOT = realpath(__DIR__ . '/../storage/exams') ?: (__DIR__ . '/../storage/exams');

function json_error($m,$c=400){ http_response_code($c); echo json_encode(['status'=>'error','message'=>$m], JSON_UNESCAPED_SLASHES); exit; }
function json_ok($arr=[]){ echo json_encode(array_merge(['status'=>'success'],$arr), JSON_UNESCAPED_SLASHES); exit; }

function bearerToken(){
  // Try common server vars
  foreach (['HTTP_AUTHORIZATION','Authorization','REDIRECT_HTTP_AUTHORIZATION'] as $h) {
    if (!empty($_SERVER[$h]) && preg_match('/Bearer\s+(\S+)/i', $_SERVER[$h], $m)) return $m[1];
  }
  // Fallback to getallheaders
  if (function_exists('getallheaders')) {
    foreach (getallheaders() as $k=>$v) {
      if (strcasecmp($k,'Authorization')===0 && preg_match('/Bearer\s+(\S+)/', $v, $m)) return $m[1];
    }
  }
  // Fallback to form/query
  if (!empty($_POST['token'])) return trim($_POST['token']);
  if (!empty($_GET['token']))  return trim($_GET['token']);
  return null;
}

function rrmdir($dir){
  if (!is_dir($dir)) return;
  $items = scandir($dir);
  foreach ($items as $it) {
    if ($it === '.' || $it === '..') continue;
    $path = $dir . DIRECTORY_SEPARATOR . $it;
    if (is_dir($path)) rrmdir($path);
    else @unlink($path);
  }
  @rmdir($dir);
}

$jwt = bearerToken();
if (!$jwt) json_error('Missing token', 401);

try { $payload = JWT::decode($jwt, new Key($secret_key, 'HS256')); }
catch(Throwable $e){ json_error('Invalid or expired token', 401); }

$user_id = $payload->user_id ?? $payload->id ?? $payload->sub ?? null;
if (!$user_id) json_error('Token missing user_id', 401);
$user_id = (int)$user_id;

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) json_error('Missing id');

// (1) Collect file paths to delete (if any) BEFORE the FK cascade removes rows
$files = [];
$hasFileCols = false;
if ($stmt = $conn->prepare("SELECT file_path FROM exam_bodies WHERE exam_id=? LIMIT 1")) {
  $hasFileCols = true; // table exists (and likely has file_path after your migration)
  $stmt->bind_param("i", $id);
  if ($stmt->execute()) {
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
      if (!empty($row['file_path'])) $files[] = $row['file_path'];
    }
  }
  $stmt->close();
}

// (2) Delete the exam (FK ON DELETE CASCADE will remove exam_bodies)
$stmt = $conn->prepare("DELETE FROM exams WHERE id=? AND user_id=?");
if (!$stmt) json_error("SQL Error (delete exam): ".$conn->error, 500);
$stmt->bind_param("ii", $id, $user_id); // both integers
$stmt->execute();
$ok = $stmt->affected_rows > 0;
$stmt->close();

if (!$ok) {
  json_error('Not found or not yours', 404);
}

// (3) Best-effort: remove file-backed storage folder for this exam
// If you versioned multiple HTML files, they live under storage/exams/{user}/{exam}/
$examDir = rtrim($EXAM_STORAGE_ROOT, '/\\') . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . $id;
if (is_dir($examDir)) rrmdir($examDir);

// Also try to unlink any directly stored file paths recorded (covers custom locations)
foreach ($files as $p) { if (is_string($p) && $p !== '' && file_exists($p)) @unlink($p); }

// Optional: try to remove now-empty user dir (ignore failure)
$userDir = rtrim($EXAM_STORAGE_ROOT, '/\\') . DIRECTORY_SEPARATOR . $user_id;
@rmdir($userDir);

json_ok();
$conn->close();
