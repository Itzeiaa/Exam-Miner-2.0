<?php
// api/export_docx.php
declare(strict_types=1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// Important: don't echo notices/warnings to the download stream
ini_set('display_errors', '0');          // hide to client
ini_set('log_errors', '1');              // but log them
error_reporting(E_ALL);
ini_set('memory_limit', '512M');
set_time_limit(120);
libxml_use_internal_errors(true);        // suppress DOM warnings to output

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Html;

function json_error($msg, $code = 400){
  if (!headers_sent()) {
    http_response_code($code);
    header('Content-Type: application/json');
  }
  echo json_encode(["status"=>"error","message"=>$msg], JSON_UNESCAPED_SLASHES);
  exit;
}

if (!class_exists('PhpOffice\\PhpWord\\PhpWord')) {
  error_log("PhpWord library missing.");
  json_error("DOCX engine not installed.", 500);
}

$secret_key   = "exam-miner";
$APP_ROOT     = realpath(__DIR__ . '/..');
$STORAGE_ROOT = realpath($APP_ROOT . '/storage/exams') ?: ($APP_ROOT . '/storage/exams');
define('AUTH_REQUIRED', false);

// Merge JSON body into $_POST if needed
$ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($ct, 'application/json') !== false) {
  $raw = file_get_contents('php://input');
  if ($raw) { $json = json_decode($raw, true); if (is_array($json)) $_POST += $json; }
}

function sanitize_filename(string $s): string {
  $s = preg_replace('/[^\w\-. ]+/', '_', trim($s));
  return $s ?: 'export';
}

// -------- inputs --------
$exam_id = (int)($_POST['id'] ?? 0);
$titleIn = trim((string)($_POST['title'] ?? ''));
$htmlIn  = isset($_POST['html']) ? (string)$_POST['html'] : null;

$title = $titleIn ?: 'Exam Paper';
$body_html = null;

// prefer POSTed HTML; else DB
if ($htmlIn !== null && trim($htmlIn) !== '') {
  $body_html = $htmlIn;
} else {
  if (!$exam_id) json_error("Missing exam id", 400);
  if (!isset($conn) || !$conn) json_error("Database connection not available", 500);

  // title
  $stmt = $conn->prepare("SELECT title FROM exams WHERE id=? LIMIT 1");
  if (!$stmt) json_error("SQL Error (exams read): ".$conn->error, 500);
  $stmt->bind_param("i", $exam_id);
  $stmt->execute(); $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
  if (!$row) json_error("Exam not found", 404);
  if (!$titleIn) $title = $row['title'] ?: $title;

  // storage detection
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

// ---- Clean editor HTML into an XHTML-ish fragment ----

// remove full-document/unsupported tags
$body_html = preg_replace('#<\s*/?\s*(html|head|meta|link|title|body)\b[^>]*>#i', '', $body_html);
$body_html = preg_replace('#<script[^>]*>.*?</script>#is', '', $body_html);
$body_html = preg_replace('#<style[^>]*>.*?</style>#is',  '', $body_html);
$body_html = preg_replace('/\scontenteditable="[^"]*"/i', '', $body_html);
$body_html = preg_replace('/\scontenteditable\b/i', '', $body_html);

// self-close voids
$body_html = preg_replace('/<br([^>]*)>/i', '<br$1 />', $body_html);
$body_html = preg_replace('/<hr([^>]*)>/i', '<hr$1 />', $body_html);
$body_html = preg_replace('/<img([^>]*?)(?<!\/)>/i', '<img$1 />', $body_html);

// normalize DIV → P where inner content is inline-only
$body_html = preg_replace_callback(
  '#<div([^>]*)>(.*?)</div>#is',
  function($m){
    $inner = $m[2];
    if (preg_match('#<(div|p|ul|ol|li|table|thead|tbody|tr|td|th|h[1-6]|pre|code)\b#i', $inner)) {
      return $m[0]; // keep div if it contains block children
    }
    return '<p'.$m[1].'>'.$inner.'</p>';
  },
  $body_html
);

// drop empty paragraphs
$body_html = preg_replace('#<p[^>]*>(?:\s|&nbsp;|<br\s*/?>)*</p>#i', '', $body_html);

// OPTIONAL: if images cause issues, uncomment the next line to strip them while stabilizing
 $body_html = preg_replace('#<img\b[^>]*>#i', '', $body_html);

// Build the fragment to feed to PhpWord (no <!doctype>, no <head>/<body>)
$fragment = '<div style="font-family:Arial, sans-serif; font-size:12pt; line-height:1.5">'
          . '<div style="text-align:center;font-weight:bold;font-size:14pt;margin:0 0 12pt">'
          . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
          . '</div>'
          . $body_html
          . '</div>';

// ---- Build the DOCX ----
if (class_exists('ZipArchive')) {
  Settings::setZipClass(Settings::ZIPARCHIVE);
} else {
  Settings::setZipClass(Settings::PCLZIP);
}

try {
  $phpWord = new PhpWord();
  $phpWord->setDefaultFontName('Arial');
  $phpWord->setDefaultFontSize(12);
  $section = $phpWord->addSection([
    'marginLeft'=>1200,'marginRight'=>1200,'marginTop'=>1200,'marginBottom'=>1200
  ]);

  Html::addHtml($section, $fragment, false, false);
  $writer = IOFactory::createWriter($phpWord, 'Word2007');
} catch (Throwable $e) {
  // log any libxml warnings too
  foreach (libxml_get_errors() as $err) { error_log('libxml: '.$err->message); }
  libxml_clear_errors();
  error_log("DOCX build error: ".$e->getMessage());
  json_error("DOCX render failed: ".$e->getMessage(), 500);
}

// ---- Stream via temp file (avoid buffering issues) ----
$fname = sanitize_filename($title) . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="'.$fname.'"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

try {
  $tmp = tempnam(sys_get_temp_dir(), 'docx_');
  $writer->save($tmp);
  header('Content-Length: '.filesize($tmp));
  readfile($tmp);
  @unlink($tmp);
  exit;
} catch (Throwable $e) {
  error_log("DOCX save/stream error: ".$e->getMessage());
  json_error("DOCX save failed: ".$e->getMessage(), 500);
}
