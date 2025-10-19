<?php
// api/exam_download.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require 'db.php';
require 'vendor/autoload.php';
require __DIR__ . '/../../examminer/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "exam-miner";

function bearerToken(){
  if (!empty($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\s+(\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) return $m[1];
  if (isset($_GET['token'])) return trim($_GET['token']);
  return null;
}
function http_error($msg,$code=400){ http_response_code($code); header("Content-Type: application/json"); echo json_encode(['status'=>'error','message'=>$msg]); exit; }

$jwt = bearerToken();
if (!$jwt) http_error('Missing token', 401);
try { $payload = JWT::decode($jwt, new Key($secret_key, 'HS256')); }
catch(Throwable $e){ http_error('Invalid or expired token', 401); }
$user_id = $payload->user_id ?? $payload->id ?? null;
if (!$user_id) http_error('Token missing user_id', 401);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) http_error('Missing id');

$sql = "SELECT e.title, b.body_html
        FROM exams e
        LEFT JOIN exam_bodies b ON b.exam_id = e.id
        WHERE e.id=? AND e.user_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) http_error('Exam not found', 404);

$title = $row['title'] ?: "Exam-$id";
$body  = $row['body_html'] ?: '';

$doc = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>'.htmlspecialchars($title).'</title>
<style>
body{ font-family: Arial, Helvetica, sans-serif; font-size: 12pt; line-height: 1.5; }
h1,h2,h3{ margin: 0 0 8pt; }
.page-break{ page-break-before: always; }
</style>
</head><body>'.
'<h2 style="text-align:center">'.htmlspecialchars(strtoupper($title)).'</h2><br>'.
$body.
'</body></html>';

$filename = preg_replace('/[^A-Za-z0-9_\-]+/','_', strtolower($title)) . '.doc';
header('Content-Type: application/msword; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: no-store');
echo $doc;
exit;
