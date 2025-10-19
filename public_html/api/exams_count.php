<?php
// /api/exams_count.php
header("Content-Type: application/json");

require __DIR__ . '/_auth.php';

$user_id = auth_user_id();

$sql = "SELECT COUNT(*) AS c FROM exams WHERE user_id = ?";
$stmt = $conn->prepare($sql) ?: json_error("SQL Error: ".$conn->error, 500);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

$count = (int)($row['c'] ?? 0);
json_ok(["status"=>"success","count"=>$count]);
