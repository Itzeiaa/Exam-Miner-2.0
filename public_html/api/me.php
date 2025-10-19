<?php
// /api/me.php
header('Content-Type: application/json');

require __DIR__ . '/_auth.php'; // must define: $conn, json_ok(), json_error(), auth_user_id()

$user_id = auth_user_id();
if ($user_id === null || $user_id === '') {
  json_error('Unauthorized: missing user id', 401);
}

// If your users.id is INT, detect & use integer bind type
$is_numeric_id = ctype_digit((string)$user_id);

// Base select (only columns that exist)
$select = "
  SELECT
    id,
    username,
    email,
    name,
    activated,
    COALESCE(profile_picture, '') AS profile_picture
  FROM users
";

// 1) Try by primary key (works for classic login / numeric IDs)
$sql = $select . " WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
  json_error("SQL Error (prepare): " . $conn->error, 500);
}

if ($is_numeric_id) {
  $uid_int = (int)$user_id;
  $stmt->bind_param("i", $uid_int);
} else {
  // Bind as string if your schema actually stores string IDs (UUIDs).
  // If your schema is INT and you’re on Google SSO (string sub), this won’t match;
  // we fallback to email a few lines below.
  $stmt->bind_param("s", $user_id);
}

if (!$stmt->execute()) {
  $err = $stmt->error;
  $stmt->close();
  json_error("SQL Error (execute): " . $err, 500);
}

// ---- mysqlnd-free fetch path ----
$stmt->store_result(); // needed to use num_rows without get_result()
if ($stmt->num_rows === 0) {
  $stmt->close();

  // OPTIONAL: Google SSO fallback by email, if _auth.php exposes the email:
   $email = function_exists('auth_email') ? auth_email() : null;
   if ($email) {
     $sql2 = $select . " WHERE email = ? LIMIT 1";
     $stmt2 = $conn->prepare($sql2);
     if (!$stmt2) json_error("SQL Error (prepare2): " . $conn->error, 500);
     $stmt2->bind_param("s", $email);
     if (!$stmt2->execute()) {
       $e2 = $stmt2->error; $stmt2->close();
       json_error("SQL Error (execute2): " . $e2, 500);
     }
     $stmt2->store_result();
     if ($stmt2->num_rows === 0) {
       $stmt2->close();
       json_error("User not found", 404);
     }
     $stmt2->bind_result($id,$username,$email,$name,$activated,$profile_picture);
     $stmt2->fetch();
     $stmt2->close();
     json_ok([
       "status"=>"success",
       "user"=>[
         "id"=>$id,
         "username"=>$username,
         "email"=>$email,
         "name"=>$name,
         "activated"=>(int)$activated,
         "profile_picture"=>$profile_picture,
         "updated_at"=>date('Y-m-d H:i:s'),
       ]
     ]);
   }

  // No fallback (or not found even by email)
  json_error("User not found", 404);
}

$stmt->bind_result($id, $username, $email, $name, $activated, $profile_picture);
$stmt->fetch();
$stmt->close();

json_ok([
  "status" => "success",
  "user" => [
    "id"              => $id,
    "username"        => $username,
    "email"           => $email,
    "name"            => $name,
    "activated"       => (int)$activated,
    "profile_picture" => $profile_picture,
    "updated_at"      => date('Y-m-d H:i:s'),
  ]
]);
