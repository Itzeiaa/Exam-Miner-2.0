<?php
declare(strict_types=1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

ini_set('display_errors', '1');
error_reporting(E_ALL);
ini_set('memory_limit', '512M');
set_time_limit(120);

$APP_ROOT     = realpath(__DIR__ . '/..');
$STORAGE_ROOT = realpath($APP_ROOT . '/storage') ?: ($APP_ROOT . '/storage');
$TMP_DIR      = $STORAGE_ROOT . '/tmp';
if (!is_dir($TMP_DIR)) @mkdir($TMP_DIR, 0775, true);

function respond($arr, $code = 200){
  if (!headers_sent()) {
    http_response_code($code);
    header('Content-Type: application/json');
  }
  echo json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
  exit;
}
function sanitize_filename(string $s): string { $s = preg_replace('/[^\w\-. ]+/', '_', trim($s)); return $s ?: 'export'; }

// ---- SINGLE autoloader
$autoloads = [
  __DIR__ . '/vendor/autoload.php',
  $APP_ROOT . '/../examminer/vendor/autoload.php', // only if your composer is really there
];
$autoload_used = null;
foreach ($autoloads as $p) {
  if (is_file($p)) { require_once $p; $autoload_used = $p; break; }
}
if (!$autoload_used) respond(['ok'=>false,'where'=>'autoload','error'=>'Composer autoload not found'], 500);

// db.php (must be silent!)
require_once __DIR__ . '/db.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Html;

$info = [
  'ok' => false,
  'steps' => [],
  'autoload_used' => $autoload_used,
];

// sanity: PhpWord loaded?
if (!class_exists('PhpOffice\\PhpWord\\PhpWord')) {
  $info['steps'][] = 'PhpWord NOT loaded';
  respond($info + ['error'=>'PhpWord not loaded'], 500);
}
$info['steps'][] = 'PhpWord loaded';

// inputs
$titleIn = trim((string)($_POST['title'] ?? ''));
$htmlIn  = isset($_POST['html']) ? (string)$_POST['html'] : '';
$title   = $titleIn ?: 'Exam Paper';

$info['input_title'] = $title;
$info['input_html_len'] = strlen($htmlIn);

if ($htmlIn === '') respond($info + ['error'=>'No HTML content to export (POST["html"] empty)'], 400);

// Clean the editor HTML a bit: drop script/style, contenteditable, & normalize simple DIV→P
$clean = preg_replace('#<script[^>]*>.*?</script>#is', '', $htmlIn);
$clean = preg_replace('#<style[^>]*>.*?</style>#is',  '', $clean);
$clean = preg_replace('/\scontenteditable="[^"]*"/i', '', $clean);
$clean = preg_replace('/\scontenteditable/i', '', $clean);

// Simple DIV→P when no block children (best-effort)
$clean = preg_replace_callback(
  '#<div([^>]*)>(.*?)</div>#is',
  function($m){
    $inner = $m[2];
    // if it contains block tags, keep div
    if (preg_match('#<(div|p|ul|ol|li|table|thead|tbody|tr|td|th|h[1-6]|pre|code)\b#i', $inner)) {
      return $m[0];
    }
    return '<p'.$m[1].'>'.$inner.'</p>';
  },
  $clean
);

// Ensure minimal HTML skeleton
$docHtml = <<<HTML
<!doctype html>
<html><head><meta charset="utf-8">
<style>
  body{font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; color:#000}
  p{margin:0 0 10pt} ul,ol{margin:0 0 10pt 22pt}
  table{border-collapse:collapse;margin:8pt 0}
  td,th{border:1px solid #999;padding:4pt;vertical-align:top}
  h1,h2,h3{font-weight:bold;margin:12pt 0 6pt}
</style></head><body>
  <div style="text-align:center;font-weight:bold;font-size:14pt;margin:0 0 12pt">{$title}</div>
  {$clean}
</body></html>
HTML;

$info['doc_html_len'] = strlen($docHtml);

// zip backend
if (class_exists('ZipArchive')) {
  Settings::setZipClass(Settings::ZIPARCHIVE);
  $info['zip_class'] = 'ZipArchive';
} else {
  Settings::setZipClass(Settings::PCLZIP);
  $info['zip_class'] = 'PclZip';
}

// build docx to disk
try {
  $phpWord = new PhpWord();
  $phpWord->setDefaultFontName('Arial');
  $phpWord->setDefaultFontSize(12);
  $section = $phpWord->addSection(['marginLeft'=>1200,'marginRight'=>1200,'marginTop'=>1200,'marginBottom'=>1200]);

  Html::addHtml($section, $docHtml, false, false);
  $info['steps'][] = 'Html::addHtml OK';

  $writer = IOFactory::createWriter($phpWord, 'Word2007');
  $info['steps'][] = 'IOFactory writer OK';

  $fname = sanitize_filename($title) . '-' . date('Ymd_His') . '.docx';
  $out   = $TMP_DIR . '/' . $fname;

  $writer->save($out);
  $info['steps'][] = 'writer->save OK';

  $size = @filesize($out);
  $info['file_path'] = $out;
  $info['file_size'] = $size !== false ? $size : null;

  if ($size === false || $size < 2000) {
    respond($info + ['ok'=>false,'error'=>'Output file too small or not created'], 500);
  }

  respond($info + ['ok'=>true], 200);
} catch (Throwable $e) {
  respond($info + [
    'ok' => false,
    'error' => 'Exception: ' . $e->getMessage(),
    'trace' => $e->getTraceAsString(),
  ], 500);
}
