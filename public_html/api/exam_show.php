<?php
// /api/exam_show.php
header("Content-Type: application/json");

require __DIR__ . '/_auth.php';

$user_id = auth_user_id();

$exam_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
if ($exam_id <= 0) json_error("Missing exam id", 400);

// single query that gets storage_mode + file_path + body_html
$sql = "SELECT e.id, e.user_id, e.title, e.description, e.exam_type, e.number_of_questions,
               e.sets_of_exam, e.learning_material, e.status, e.created_at, e.updated_at,
               b.storage_mode, b.file_path, b.body_html
        FROM exams e
        LEFT JOIN exam_bodies b ON b.exam_id = e.id
        WHERE e.id = ? AND e.user_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql) ?: json_error("SQL Error: ".$conn->error, 500);
$stmt->bind_param("is", $exam_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$e = $res->fetch_assoc();
$stmt->close();

if (!$e) json_error("Exam not found", 404);

// Cast numerics
$e['id'] = (int)$e['id'];
$e['number_of_questions'] = (int)$e['number_of_questions'];
$e['sets_of_exam'] = (int)$e['sets_of_exam'];

// honor storage_mode: read from file if storage_mode='file', else use body_html
$body = '';
if (!empty($e['storage_mode']) && $e['storage_mode'] === 'file') {
  $p = $e['file_path'] ?? '';
  $body = ($p && is_file($p) && is_readable($p)) ? (string)@file_get_contents($p) : '';
} else {
  $body = (string)($e['body_html'] ?? '');
}
$e['body_html'] = $body;

// Optional: quick derived count (same heuristic as before)
$detected = 0;
if ($e['body_html'] !== '') {
  $txt = strip_tags($e['body_html'], '<li><p><ol><ul><br>');
  if (preg_match_all('/<li\b[^>]*>.*?<\/li>/is', $txt, $m)) {
    $detected = max($detected, count($m[0]));
  }
  if (preg_match_all('/(?:^|\n)\s*\d+\s*[\)\.:-]\s+/m', html_entity_decode($txt), $m2)) {
    $detected = max($detected, count($m2[0]));
  }
}
$e['computed_questions'] = $detected;

// don’t leak file_path to the client
unset($e['file_path']);

json_ok(["status"=>"success","exam"=>$e]);
