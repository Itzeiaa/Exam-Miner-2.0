<?php
// api/exam_save.php
header("Content-Type: application/json");

require __DIR__ . '/_auth.php';  // $conn, json_error(), json_ok(), auth_user_id()

// Accept JSON bodies too (merge into $_POST without clobbering form fields)
$ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($ct, 'application/json') !== false) {
  $raw = file_get_contents('php://input');
  if ($raw) {
    $json = json_decode($raw, true);
    if (is_array($json)) {
      foreach ($json as $k => $v) {
        if (!array_key_exists($k, $_POST)) $_POST[$k] = $v;
      }
    }
  }
}

$user_id = auth_user_id(); // _auth.php returns 401 if bad
if ($user_id === '' || $user_id === null) json_error("Token missing user_id", 401);

// --- Inputs ---
$title  = trim((string)($_POST['title'] ?? ''));
$desc   = trim((string)($_POST['description'] ?? ''));
$type   = trim((string)($_POST['exam_type'] ?? 'mixed'));
$qty    = (int)($_POST['number_of_questions'] ?? 0);
$sets   = (int)($_POST['sets_of_exam'] ?? 1);
$lm     = trim((string)($_POST['learning_material'] ?? ''));
$body   = (string)($_POST['body_html'] ?? '');

if ($title === '' || $qty <= 0 || $body === '') {
  json_error("Missing required fields (title, number_of_questions, body_html).");
}

// Optional size warning
$approxBytes = strlen($body);
$SOFT_LIMIT = 6 * 1024 * 1024; // 6 MB friendly warning
if ($approxBytes > $SOFT_LIMIT) {
  $size_warning = "Large body_html (~" . number_format($approxBytes / (1024*1024), 2) . " MB). Ensure PHP/Nginx upload limits are raised.";
}

// --- Ensure user exists ---
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
if (!$stmt) json_error("SQL Error (users prepare): ".$conn->error, 500);
// users.id type can be VARCHAR in your schema, bind as string for compatibility
$stmt->bind_param("s", $user_id);
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;
$stmt->close();
if (!$exists) json_error("User not found", 404);

$now = date('Y-m-d H:i:s');

// Wrap in a transaction
$conn->begin_transaction();

// Save exam meta
$sql = "INSERT INTO exams
  (user_id, title, description, exam_type, number_of_questions, sets_of_exam, learning_material, status, created_at, updated_at)
  VALUES (?, ?, ?, ?, ?, ?, ?, 'generated', ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
  $conn->rollback();
  json_error("SQL Error (exams prepare): ".$conn->error, 500);
}
if (!$stmt->bind_param("ssssissss", $user_id, $title, $desc, $type, $qty, $sets, $lm, $now, $now)) {
  $msg = $stmt->error ?: $conn->error;
  $stmt->close();
  $conn->rollback();
  json_error("SQL Error (exams bind): ".$msg, 500);
}
if (!$stmt->execute()) {
  $msg = $stmt->error ?: $conn->error;
  $stmt->close();
  $conn->rollback();
  json_error("Failed to save exam: ".$msg, 500);
}
$exam_id = $stmt->insert_id;
$stmt->close();

/* ===================== Storage mode decision ===================== */
// ------- decide storage mode -------
$useFile = $approxBytes > $SOFT_LIMIT;

// where to store files
$EXAM_STORAGE_ROOT = realpath(__DIR__ . '/../storage/exams') ?: (__DIR__ . '/../storage/exams');
if ($useFile) {
  $dir = rtrim($EXAM_STORAGE_ROOT, '/\\') . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . $exam_id;
  if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
    $conn->rollback();
    json_error("Failed to create storage dir", 500);
  }
  $path = $dir . DIRECTORY_SEPARATOR . 'body.html';
  if (@file_put_contents($path, $body) === false) {
    $conn->rollback();
    json_error("Failed to write body file", 500);
  }
}

// ------- insert exam_bodies (mutually exclusive) -------
$sqlB = "INSERT INTO exam_bodies (exam_id, storage_mode, file_path, body_html, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?)";
$stmtB = $conn->prepare($sqlB);
if (!$stmtB) { $conn->rollback(); json_error("SQL Error (exam_bodies prepare): ".$conn->error, 500); }

$mode = $useFile ? 'file' : 'db';
$filePath = $useFile ? $path : null;
$bodyHtml = $useFile ? null : $body;

if (!$stmtB->bind_param("isssss", $exam_id, $mode, $filePath, $bodyHtml, $now, $now)) {
  $msg = $stmtB->error ?: $conn->error; $stmtB->close(); $conn->rollback();
  json_error("SQL Error (exam_bodies bind): ".$msg, 500);
}
if (!$stmtB->execute()) {
  $msg = $stmtB->error ?: $conn->error; $stmtB->close(); $conn->rollback();
  json_error("Failed to save exam body: ".$msg, 500);
}
$stmtB->close();

$conn->commit();

json_ok([
  "status"=>"success",
  "exam_id"=>$exam_id,
  "message"=>"Saved exam (mode: $mode).",
  "warning"=>$size_warning ?? null
]);
