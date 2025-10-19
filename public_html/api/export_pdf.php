<?php
// api/export_pdf.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('memory_limit', '512M');
set_time_limit(120);

require __DIR__ . '/../../examminer/vendor/autoload.php';
require __DIR__ . '/db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dompdf\Dompdf;
use Dompdf\Options;

$secret_key   = "exam-miner";
$APP_ROOT     = realpath(__DIR__ . '/..');                     // project root
$STORAGE_ROOT = realpath($APP_ROOT . '/storage/exams') ?: ($APP_ROOT . '/storage/exams');
$TMP_DIR      = $APP_ROOT . '/storage/tmp';
$DOMPDF_CACHE = $APP_ROOT . '/storage/dompdf';

// Toggle auth quickly while testing
define('AUTH_REQUIRED', false);

/* ---------- helpers ---------- */
function json_error($msg, $code = 400){
  if (!headers_sent()) {
    http_response_code($code);
    header('Content-Type: application/json');
  }
  echo json_encode(["status"=>"error","message"=>$msg], JSON_UNESCAPED_SLASHES);
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
      if (is_array($json)) foreach ($json as $k=>$v) { if (!isset($_POST[$k])) $_POST[$k]=$v; }
    }
  }
}
function ensure_dir($p){ return is_dir($p) || @mkdir($p, 0775, true); }
function sanitize_filename(string $s): string {
  $s = trim($s);
  $s = preg_replace('/[^\w\-. ]+/', '_', $s);
  return $s ?: 'export';
}

/* ---------- prep writable dirs (prevents "resource was not cached") ---------- */
if (!ensure_dir($TMP_DIR))     { json_error("Temp dir not writable: $TMP_DIR", 500); }
if (!ensure_dir($DOMPDF_CACHE)){ json_error("Cache dir not writable: $DOMPDF_CACHE", 500); }

/* ---------- auth ---------- */
merge_json_body_into_post();

$user_id = null;
if (AUTH_REQUIRED) {
  $jwt = bearerToken();
  if (!$jwt) json_error("Missing token", 401);
  try { $payload = JWT::decode($jwt, new Key($secret_key, 'HS256')); }
  catch(Throwable $e){ json_error("Invalid or expired token", 401); }
  $user_id = (int)($payload->user_id ?? $payload->id ?? $payload->sub ?? 0);
  if ($user_id <= 0) json_error("Token missing user_id", 401);
}

/* ---------- inputs ---------- */
$exam_id = (int)($_POST['id'] ?? 0);
$titleIn = trim((string)($_POST['title'] ?? ''));
$htmlIn  = isset($_POST['html']) ? (string)$_POST['html'] : null;

/* ---------- fetch title/body if needed ---------- */
$title = $titleIn ?: 'Exam Paper';
$body_html = null;

if ($htmlIn !== null && trim($htmlIn) !== '') {
  $body_html = $htmlIn;
} else {
  if (!$exam_id) json_error("Missing exam id", 400);

  // title
  if (AUTH_REQUIRED && $user_id !== null) {
    $stmt = $conn->prepare("SELECT title FROM exams WHERE id=? AND user_id=? LIMIT 1");
    if (!$stmt) json_error("SQL Error (exams ownership): ".$conn->error, 500);
    $stmt->bind_param("is", $exam_id, $user_id);
  } else {
    $stmt = $conn->prepare("SELECT title FROM exams WHERE id=? LIMIT 1");
    if (!$stmt) json_error("SQL Error (exams read): ".$conn->error, 500);
    $stmt->bind_param("i", $exam_id);
  }
  $stmt->execute(); $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
  if (!$row) json_error("Exam not found", 404);
  if (!$titleIn) $title = $row['title'] ?: $title;

  // detect storage mode
  $hasStorageCols = false;
  if ($r = $conn->query("SHOW COLUMNS FROM exam_bodies LIKE 'storage_mode'")) {
    $hasStorageCols = $r && $r->num_rows > 0; if ($r) $r->close();
  }

  if ($hasStorageCols) {
    $stmt = $conn->prepare("SELECT storage_mode, file_path, body_html FROM exam_bodies WHERE exam_id=? LIMIT 1");
    if (!$stmt) json_error("SQL Error (exam_bodies read): ".$conn->error, 500);
    $stmt->bind_param("i", $exam_id);
    $stmt->execute(); $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if ($r) {
      if (($r['storage_mode'] ?? '') === 'file' && !empty($r['file_path'])) {
        $real = realpath($r['file_path']);
        $root = realpath($STORAGE_ROOT);
        if ($real && $root && strpos($real, $root) === 0 && is_readable($real)) {
          $body_html = @file_get_contents($real);
        }
      } else {
        $body_html = $r['body_html'] ?? '';
      }
    }
  } else {
    $stmt = $conn->prepare("SELECT body_html FROM exam_bodies WHERE exam_id=? LIMIT 1");
    if (!$stmt) json_error("SQL Error (exam_bodies legacy): ".$conn->error, 500);
    $stmt->bind_param("i", $exam_id);
    $stmt->execute(); $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
    $body_html = $r['body_html'] ?? '';
  }
}

$body_html = (string)$body_html;
if ($body_html === '') json_error("No HTML content to export", 400);

// sanitize for dompdf
$body_html = preg_replace('#<script[^>]*>.*?</script>#is', '', $body_html);
$body_html = preg_replace('#<style[^>]*>.*?</style>#is',  '', $body_html);
$body_html = preg_replace('/font-family\s*:\s*[^;"]+;?/i', '', $body_html);

// full html
$fullHtml = <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { margin: 40px 40px 60px 40px; size: A4 portrait; }
  body{ font-family: DejaVu Sans, Arial, sans-serif; font-size: 12pt; line-height: 1.4; color:#000; }
  h1,h2,h3{ font-weight:700; margin:14pt 0 8pt; }
  p{ margin:0 0 8pt; }
  ul,ol{ margin:0 0 10pt 18pt; }
  table{ border-collapse:collapse; width:100%; margin:8pt 0; }
  td,th{ border:1px solid #999; padding:6pt; vertical-align: top; }
  .page-break{ page-break-before: always; }
</style>
</head>
<body>
  <div style="text-align:center;font-weight:bold;font-size:14pt;margin:0 0 12pt">{$title}</div>
  {$body_html}
</body>
</html>
HTML;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->setTempDir($TMP_DIR);
$options->setFontCache($DOMPDF_CACHE);
// optional: write a debug log file if dompdf fails to fetch things
$options->setLogOutputFile($DOMPDF_CACHE . '/dompdf.log');

// Limit local file access to your project root (still allows HTTP URLs)
$options->setChroot($APP_ROOT);

// If your origin has self-signed TLS and you embed https images, relax SSL (only if needed):
$context = stream_context_create([
  'ssl' => [ 'verify_peer'=>false, 'verify_peer_name'=>false ],
]);
$options->setHttpContext($context);

$dompdf = new Dompdf($options);

// base path for relative URLs (e.g., <img src="/images/foo.png">)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$base   = $scheme . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/';
$dompdf->setBasePath($base);

try {
  $dompdf->loadHtml($fullHtml);
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
} catch (Throwable $e) {
  json_error("PDF render failed: " . $e->getMessage(), 500);
}

$fname = sanitize_filename($title) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$fname.'"');
echo $dompdf->output();
