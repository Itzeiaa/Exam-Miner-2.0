<?php
// api/exam_update.php (storage-aware: DB or file)
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

// —— Configs (keep in sync with exam_save) ——
$EXAM_STORAGE_ROOT = realpath(__DIR__ . '/../storage/exams') ?: (__DIR__ . '/../storage/exams');
$DB_SOFT_LIMIT = 6 * 1024 * 1024;     // Prefer file if > 6MB
$ALWAYS_FILE_FOR_UPDATES = false;      // set true if you want all updates to go to file

// —— helpers ——
function json_error($msg, $code = 400){
  http_response_code($code);
  echo json_encode(["status"=>"error","message"=>$msg], JSON_UNESCAPED_SLASHES);
  exit;
}
function json_ok($arr = []){
  echo json_encode(array_merge(["status"=>"success"], $arr), JSON_UNESCAPED_SLASHES);
  exit;
}
function bearerToken(){
  foreach (['HTTP_AUTHORIZATION','Authorization','REDIRECT_HTTP_AUTHORIZATION'] as $h) {
    if (!empty($_SERVER[$h]) && preg_match('/Bearer\s+(\S+)/i', $_SERVER[$h], $m)) return $m[1];
  }
  if (function_exists('getallheaders')) {
    foreach (getallheaders() as $k=>$v) {
      if (strcasecmp($k,'Authorization')===0 && preg_match('/Bearer\s+(\S+)/', $v, $m)) return $m[1];
    }
  }
  if (!empty($_POST['token'])) return trim($_POST['token']);
  if (!empty($_GET['token']))  return trim($_GET['token']);
  return null;
}
function merge_json_body_into_post(){
  $ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
  if (stripos($ct, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    if ($raw) {
      $json = json_decode($raw, true);
      if (is_array($json)) {
        foreach ($json as $k => $v) {
          if (!isset($_POST[$k])) $_POST[$k] = $v;
        }
      }
    }
  }
}
function ensure_dir($path){
  if (is_dir($path)) return true;
  return @mkdir($path, 0775, true);
}

// —— auth ——
merge_json_body_into_post();

$jwt = bearerToken();
if (!$jwt) json_error("Missing token", 401);

try {
  $payload = JWT::decode($jwt, new Key($secret_key, 'HS256'));
} catch(Throwable $e){
  json_error("Invalid or expired token", 401);
}

$user_id = $payload->user_id ?? $payload->id ?? $payload->sub ?? null;
if (!$user_id) json_error("Token missing user_id", 401);
$user_id = (string)$user_id;

// —— inputs ——
$exam_id = (int)($_POST['id'] ?? 0);
if ($exam_id <= 0) json_error("Missing exam id");

$title = array_key_exists('title', $_POST) ? trim((string)$_POST['title']) : null;
$desc  = array_key_exists('description', $_POST) ? trim((string)$_POST['description']) : null;
$body  = array_key_exists('body_html', $_POST) ? (string)$_POST['body_html'] : null;

// —— ownership check ——
$stmt = $conn->prepare("SELECT id FROM exams WHERE id=? AND user_id=? LIMIT 1");
if (!$stmt) json_error("SQL Error (ownership prep): ".$conn->error, 500);
$stmt->bind_param("is", $exam_id, $user_id);
$stmt->execute();
$owned = $stmt->get_result()->num_rows > 0;
$stmt->close();
if (!$owned) json_error("Exam not found or not owned by user", 404);

$now = date('Y-m-d H:i:s');

// —— update title/desc if provided ——
if ($title !== null || $desc !== null) {
  $set = []; $params = []; $types = '';
  if ($title !== null) { $set[]="title=?"; $params[]=$title; $types.='s'; }
  if ($desc  !== null) { $set[]="description=?"; $params[]=$desc; $types.='s'; }
  $set[] = "updated_at=?"; $params[] = $now; $types.='s';
  $params[] = $exam_id; $types.='i';
  $params[] = $user_id; $types.='i';

  $sql = "UPDATE exams SET ".implode(',', $set)." WHERE id=? AND user_id=? LIMIT 1";
  $stmt = $conn->prepare($sql);
  if (!$stmt) json_error("SQL Error (update exams): ".$conn->error, 500);
  $stmt->bind_param($types, ...$params);
  if (!$stmt->execute()) { $stmt->close(); json_error("Failed to update exam", 500); }
  $stmt->close();
}

// —— upsert body_html/file if provided ——
if ($body !== null) {
  // Probe columns to know if file mode exists
  $hasStorageCols = false;
  if ($res = $conn->query("SHOW COLUMNS FROM exam_bodies LIKE 'storage_mode'")) {
    $hasStorageCols = $res && $res->num_rows > 0;
    if ($res) $res->close();
  }

  // Read current body row
  $cur = ['id'=>null,'storage_mode'=>null,'file_path'=>null];
  if ($stmt = $conn->prepare("SELECT id"
      . ($hasStorageCols ? ", storage_mode, file_path" : "")
      . " FROM exam_bodies WHERE exam_id=? LIMIT 1")) {
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r) {
      $cur['id'] = (int)$r['id'];
      if ($hasStorageCols) {
        $cur['storage_mode'] = $r['storage_mode'] ?? null;
        $cur['file_path']    = $r['file_path'] ?? null;
      }
    }
    $stmt->close();
  }

  // Decide target mode
  $approxBytes = strlen($body);
  $targetMode = 'db';
  if ($hasStorageCols) {
    if ($cur['storage_mode'] === 'file') {
      $targetMode = 'file';                          // keep file mode
    } else if ($ALWAYS_FILE_FOR_UPDATES || $approxBytes > $DB_SOFT_LIMIT) {
      $targetMode = 'file';                          // promote to file
    } else {
      $targetMode = 'db';
    }
  }

  // Paths if file mode
  $writtenPath = null;
  if ($targetMode === 'file') {
    $base = rtrim($EXAM_STORAGE_ROOT, '/\\') . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . $exam_id;
    if (!ensure_dir($base)) json_error("Failed to prepare storage directory", 500);
    $writtenPath = $base . DIRECTORY_SEPARATOR . 'body.html'; // overwrite latest
    if (@file_put_contents($writtenPath, $body) === false) {
      json_error("Failed to write exam body file", 500);
    }
    // small safety: ensure readable perms
    @chmod($writtenPath, 0664);
  }

  // Upsert row accordingly
  if ($cur['id']) {
    if ($hasStorageCols) {
      if ($targetMode === 'file') {
        // update file path, null body_html
        $stmt = $conn->prepare("UPDATE exam_bodies SET storage_mode='file', file_path=?, body_html=NULL, updated_at=? WHERE exam_id=?");
        if (!$stmt) json_error("SQL Error (exam_bodies update file): ".$conn->error, 500);
        $stmt->bind_param("ssi", $writtenPath, $now, $exam_id);
        if (!$stmt->execute()) { $stmt->close(); json_error("Failed to update file-backed body", 500); }
        $stmt->close();

        // if we previously had a DB copy, it's null now; if we had an old file at another path, best-effort cleanup:
        if (!empty($cur['file_path']) && $cur['file_path'] !== $writtenPath && file_exists($cur['file_path'])) @unlink($cur['file_path']);

      } else {
        // DB mode: store body_html, clear file_path
        $stmt = $conn->prepare("UPDATE exam_bodies SET storage_mode='db', body_html=?, file_path=NULL, updated_at=? WHERE exam_id=?");
        if (!$stmt) json_error("SQL Error (exam_bodies update db): ".$conn->error, 500);
        $stmt->bind_param("ssi", $body, $now, $exam_id);
        if (!$stmt->execute()) { $stmt->close(); json_error("Failed to update db-backed body", 500); }
        $stmt->close();

        // cleanup old file if any
        if (!empty($cur['file_path']) && file_exists($cur['file_path'])) @unlink($cur['file_path']);
      }
    } else {
      // Legacy table: only body_html is available
      $stmt = $conn->prepare("UPDATE exam_bodies SET body_html=?, updated_at=? WHERE exam_id=?");
      if (!$stmt) json_error("SQL Error (exam_bodies legacy update): ".$conn->error, 500);
      $stmt->bind_param("ssi", $body, $now, $exam_id);
      if (!$stmt->execute()) { $stmt->close(); json_error("Failed to update body_html", 500); }
      $stmt->close();
    }
  } else {
    // Insert new row
    if ($hasStorageCols) {
      if ($targetMode === 'file') {
        $stmt = $conn->prepare("INSERT INTO exam_bodies (exam_id, storage_mode, file_path, body_html, created_at, updated_at) VALUES (?, 'file', ?, NULL, ?, ?)");
        if (!$stmt) json_error("SQL Error (exam_bodies insert file): ".$conn->error, 500);
        $stmt->bind_param("isss", $exam_id, $writtenPath, $now, $now);
        if (!$stmt->execute()) { $stmt->close(); json_error("Failed to insert file-backed body", 500); }
        $stmt->close();
      } else {
        $stmt = $conn->prepare("INSERT INTO exam_bodies (exam_id, storage_mode, file_path, body_html, created_at, updated_at) VALUES (?, 'db', NULL, ?, ?, ?)");
        if (!$stmt) json_error("SQL Error (exam_bodies insert db): ".$conn->error, 500);
        $stmt->bind_param("isss", $exam_id, $body, $now, $now);
        if (!$stmt->execute()) { $stmt->close(); json_error("Failed to insert db-backed body", 500); }
        $stmt->close();
      }
    } else {
      // Legacy table
      $stmt = $conn->prepare("INSERT INTO exam_bodies (exam_id, body_html, created_at, updated_at) VALUES (?, ?, ?, ?)");
      if (!$stmt) json_error("SQL Error (exam_bodies legacy insert): ".$conn->error, 500);
      $stmt->bind_param("isss", $exam_id, $body, $now, $now);
      if (!$stmt->execute()) { $stmt->close(); json_error("Failed to insert body_html", 500); }
      $stmt->close();
    }
  }

  // bump parent updated_at when body changes
  if ($stmt = $conn->prepare("UPDATE exams SET updated_at=? WHERE id=? AND user_id=? LIMIT 1")) {
    $stmt->bind_param("sis", $now, $exam_id, $user_id);
    $stmt->execute();
    $stmt->close();
  }
}

json_ok(["exam_id"=>$exam_id, "updated_at"=>$now]);
$conn->close();
