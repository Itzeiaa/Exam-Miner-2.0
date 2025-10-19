<?php
// /api/exams_recent.php
header("Content-Type: application/json");

require __DIR__ . '/_auth.php';

$user_id = auth_user_id();

$sql = "SELECT id, title, description, exam_type, number_of_questions, sets_of_exam,
               status, created_at, updated_at
        FROM exams
        WHERE user_id = ?
        ORDER BY COALESCE(created_at, updated_at) DESC
        LIMIT 3";
$stmt = $conn->prepare($sql) ?: json_error("SQL Error: ".$conn->error, 500);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$exams = [];
while ($r = $res->fetch_assoc()) {
  $r['id'] = (int)$r['id'];
  $r['number_of_questions'] = (int)$r['number_of_questions'];
  $r['sets_of_exam'] = (int)$r['sets_of_exam'];
  $exams[] = $r;
}
$stmt->close();

json_ok(["status"=>"success","exams"=>$exams]);
