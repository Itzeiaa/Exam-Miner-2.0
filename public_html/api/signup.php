<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require 'db.php';
require 'phpmailer_vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ---------- helpers ---------- */
function sanitize($s){ return htmlspecialchars(strip_tags(trim($s ?? ''))); }
function uuidv4(): string {
  $d = random_bytes(16);
  $d[6] = chr((ord($d[6]) & 0x0f) | 0x40); // v4
  $d[8] = chr((ord($d[8]) & 0x3f) | 0x80); // variant
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}
function json_fail($msg, $code=400){ http_response_code($code); echo json_encode(["status"=>"error","message"=>$msg]); exit; }

/* ---------- inputs ---------- */
$name     = sanitize($_POST['name'] ?? '');
$email    = sanitize($_POST['email'] ?? '');
$username = sanitize($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (!$name || !$email || !$username || !$password) json_fail("Missing required fields");
if (!filter_var($email, FILTER_VALIDATE_EMAIL))         json_fail("Invalid email format");

// --- password policy ---
if (strlen($password) < 6) {
  json_fail("Password must be at least 6 characters long.");
}
if (preg_match('/\s/', $password)) {
  json_fail("Password cannot contain spaces.");
}
if (!preg_match('/[A-Z]/', $password)) {
  json_fail("Password must include at least one uppercase letter.");
}
if (!preg_match('/[a-z]/', $password)) {
  json_fail("Password must include at least one lowercase letter.");
}
if (!preg_match('/\d/', $password)) {
  json_fail("Password must include at least one number.");
}
if (!preg_match('/[^A-Za-z0-9]/', $password)) { // any symbol
  json_fail("Password must include at least one symbol.");
}


$hashed_password = password_hash($password, PASSWORD_DEFAULT);

/* ---------- unique check ---------- */
$check = $conn->prepare("SELECT 1 FROM users WHERE username=? OR email=? LIMIT 1");
if(!$check) json_fail("Server error (prepare check failed)", 500);
$check->bind_param("ss", $username, $email);
$check->execute(); $check->store_result();
if ($check->num_rows > 0) { $check->close(); json_fail("Username or email already exists"); }
$check->close();

/* ---------- OTP mailer ---------- */
function sendOTP($email){
  global $conn;
  $email = sanitize($email);
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

  // Don't send if already activated
  $s = $conn->prepare("SELECT activated FROM users WHERE email=? LIMIT 1");
  $s->bind_param("s",$email); $s->execute(); $s->bind_result($activated);
  if ($s->fetch() && intval($activated) === 1) { $s->close(); return false; }
  $s->close();

  $otp = strval(random_int(100000, 999999));
  $s = $conn->prepare("REPLACE INTO otp_table (email, otp, created_at) VALUES (?, ?, NOW())");
  $s->bind_param("ss", $email, $otp); $s->execute(); $s->close();

  $mail = new PHPMailer(true);
  try{
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'otp.sender.online@gmail.com';
    $mail->Password = 'xyem aapx oezd npsj';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('no-reply@exam-miner.com', 'Exam-Miner 2.0 OTP Verification');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Signup OTP';
$mail->Body = '
<!DOCTYPE html>
<html>
  <body style="margin:0; padding:0; background:#eef2ff;">
    <!-- Preheader (hidden) -->
    <div style="display:none; font-size:1px; color:#eef2ff; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
      Your Exam Miner verification code is ' . htmlspecialchars($otp) . ' and expires in 5 minutes.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
           style="width:100%; background:linear-gradient(-45deg,#1e3a8a,#3b82f6,#60a5fa,#93c5fd,#1e40af,#1d4ed8); background-size:400% 400%;">
      <tr>
        <td align="center" style="padding:32px 16px;">
          <table role="presentation" cellspacing="0" cellpadding="0" width="100%"
                 style="max-width:520px; background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 10px 30px rgba(2,8,23,.08);">
            <tr>
              <td style="padding:24px 28px 8px; font-family: Inter, -apple-system, Segoe UI, Roboto, Arial, sans-serif;">
                <div style="font-weight:700; color:#2563eb; letter-spacing:.3px; font-size:18px; margin-bottom:6px;">
                  Exam Miner 2.0
                </div>
                <div style="font-size:22px; font-weight:700; color:#0f172a;">
                  Email Verification
                </div>
                <div style="height:10px;"></div>
                <div style="color:#64748b; font-size:14px; line-height:1.6;">
                  Hello,<br>
                  We sent a one-time code to your email. Enter it below to continue. This code expires in 5 minutes.
                </div>
              </td>
            </tr>

            <tr>
              <td align="center" style="padding:6px 28px 22px;">
                <div style="display:inline-block; font-family:SFMono-Regular,Consolas,Menlo,monospace;
                            font-size:28px; font-weight:700; letter-spacing:6px; color:#111827;
                            background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px;">
                  ' . htmlspecialchars($otp) . '
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding:0 28px 18px; font-family: Inter, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
                         color:#64748b; font-size:13px; line-height:1.6;">
                If you did not request this, you can safely ignore this email. Please do not share this code with anyone.
              </td>
            </tr>

            <tr>
              <td align="center" style="padding:0 28px 26px; font-family: Inter, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
                         color:#94a3b8; font-size:12px;">
                &copy; ' . date('Y') . ' Exam Miner &middot; Automated message — replies are not monitored.
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>';
$mail->AltBody = 'Your Exam Miner verification code is ' . $otp . ' (expires in 5 minutes). If you did not request this, ignore this message.';

    $mail->send();
    return true;
  }catch(Exception $e){ return false; }
}

/* ---------- insert with UUID id ---------- */
$user_id = uuidv4();
$stmt = $conn->prepare(
  "INSERT INTO users (id, name, email, username, password_hash, activated)
   VALUES (?, ?, ?, ?, ?, 0)"
);
if(!$stmt) json_fail("Server error (prepare insert failed)", 500);
$stmt->bind_param("sssss", $user_id, $name, $email, $username, $hashed_password);

if ($stmt->execute()){
  $sent = sendOTP($email);
  if ($sent){
    echo json_encode([
      "status"   => "success",
      "message"  => "Signup success, OTP sent",
      "user_id"  => $user_id,     // <- UUID returned to frontend
      "username" => $username
    ]);
  } else {
    echo json_encode(["status"=>"error","message"=>"OTP sending failed"]);
  }
} else {
  json_fail("Signup failed", 500);
}
$stmt->close(); $conn->close();
