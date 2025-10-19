<?php
// mail.php restricted OTP for signup only
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

require 'vendor/autoload.php';
require __DIR__ . '/../../examminer/vendor/autoload.php';
require 'phpmailer_vendor/autoload.php';
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$secret_key = "exam-miner";

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = ['https://exam-miner.com', 'https://www.exam-miner.com'];
if (in_array($origin, $allowed, true)) {
  header("Access-Control-Allow-Origin: $origin");
  header("Vary: Origin");
} else {
  header("Access-Control-Allow-Origin: https://exam-miner.com");
}
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");



if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; // preflight
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function userExists($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $found = $stmt->num_rows > 0;
    $stmt->close();
    return $found;
}


/* | **************************** |
 * |                              |
 * |  Recovery account Function   |
 * |                              |
 * | **************************** |
*/

function sendRecoveryOTP($email) {
    global $conn;

    $email = sanitize($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
        return;
    }

    // Must exist to recover
    if (!userExists($email)) {
        echo json_encode(['status' => 'error', 'message' => 'If the email is eligible, an OTP was sent.' /*'This email is not registered.'*/ ]);
        return;
    }

    $otp = strval(random_int(100000, 999999));

    // reuse same table; one row per email
    $stmt = $conn->prepare("REPLACE INTO otp_table (email, otp, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $stmt->close();

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'YOU_USERNAME'; // Your google username
        $mail->Password = 'YOUR_APP_PASSWORD'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@exam-miner.com', 'Exam Miner 2.0 Recovery');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Account Recovery OTP';

        $mail->Body = '
<!DOCTYPE html>
<html>
  <body style="margin:0; padding:0; background:#0b1220;">
    <div style="display:none; font-size:1px; color:#0b1220; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
      Your Exam Miner recovery code is ' . htmlspecialchars($otp) . ' and expires in 5 minutes.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0b1220;">
      <tr>
        <td align="center" style="padding:32px 16px;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px; background:#ffffff; border:1px solid #e5e7eb; border-radius:14px;">
            <tr>
              <td align="center" style="padding:28px 28px 10px;">
                <div style="font-size:18px; font-weight:700; color:#2563eb; letter-spacing:.3px;">Exam Miner 2.0</div>
                <div style="font-size:22px; font-weight:700; color:#0f172a; margin-top:6px;">Account Recovery</div>
              </td>
            </tr>

            <tr>
              <td style="padding:8px 28px 18px; color:#334155; font-size:15px; line-height:1.6;">
                Hello,<br>
                Use the one-time code below to recover your account. This code expires in 5 minutes.
              </td>
            </tr>

            <tr>
              <td align="center" style="padding:4px 28px 22px;">
                <div style="display:inline-block; font-size:28px; font-weight:700; letter-spacing:6px; color:#111827; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px; font-family:SFMono-Regular,Consolas,Menlo,monospace;">
                  ' . htmlspecialchars($otp) . '
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding:0 28px 16px; color:#64748b; font-size:13px; line-height:1.6;">
                If you did not request this, you can ignore this email.
              </td>
            </tr>

            <tr>
              <td align="center" style="padding:0 28px 28px; color:#94a3b8; font-size:12px;">
                &copy; ' . date('Y') . ' Exam Miner • Automated message — replies are not monitored.
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>';

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Recovery OTP sent']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Email failed to send']);
    }
}


function validateRecoveryOTP($email, $otpInput) {
    global $conn, $secret_key;

    $email = sanitize($email);
    $otpInput = sanitize($otpInput);

    $stmt = $conn->prepare("SELECT otp, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS age_min FROM otp_table WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($storedOtp, $ageMin);

    if ($stmt->fetch() && hash_equals($storedOtp, $otpInput) && $ageMin !== null && $ageMin <= 5) {
        $stmt->close();
        // one-time use
        $del = $conn->prepare("DELETE FROM otp_table WHERE email = ?");
        $del->bind_param("s", $email);
        $del->execute();
        $del->close();

        // short-lived token (10 minutes) to authorize password reset
        $payload = [ 'email'=>$email, 'kind'=>'recovery', 'exp'=> time() + 600 ];
        $token = \Firebase\JWT\JWT::encode($payload, $secret_key, 'HS256');

        echo json_encode(['status'=>'success','message'=>'OTP verified','recovery_token'=>$token]);
    } else {
        $stmt->close();
        echo json_encode(['status'=>'error','message'=>'Invalid or expired OTP']);
    }
}




function resetPassword($email, $newPassword, $recoveryToken) {
    global $conn, $secret_key;

    $email = sanitize($email);
    $newPassword = trim($newPassword);
    $recoveryToken = trim($recoveryToken);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status'=>'error','message'=>'Invalid email']); return;
    }
    if (strlen($newPassword) < 6
        || preg_match('/\s/', $newPassword)
        || !preg_match('/[A-Z]/', $newPassword)
        || !preg_match('/[a-z]/', $newPassword)
        || !preg_match('/\d/', $newPassword)
        || !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
        echo json_encode(['status'=>'error',
            'message'=>'Password must be ≥6 chars and include uppercase, lowercase, number, and symbol (no spaces).'
        ]);
        return;
    }

    if (!$recoveryToken) {
        echo json_encode(['status'=>'error','message'=>'Missing recovery token']); return;
    }

    try {
        $decoded = \Firebase\JWT\JWT::decode($recoveryToken, new \Firebase\JWT\Key($secret_key, 'HS256'));
    } catch (\Throwable $e) {
        echo json_encode(['status'=>'error','message'=>'Invalid or expired token']); return;
    }

    if (($decoded->email ?? null) !== $email || ($decoded->kind ?? '') !== 'recovery') {
        echo json_encode(['status'=>'error','message'=>'Token mismatch']); return;
    }

    $hash = password_hash($newPassword, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET password_hash = ?, activated = 1 WHERE email = ? LIMIT 1");
    $stmt->bind_param("ss", $hash, $email);
    if ($stmt->execute()) {
        // best effort: clear any leftover OTP rows
        $del = $conn->prepare("DELETE FROM otp_table WHERE email = ?");
        $del->bind_param("s", $email);
        $del->execute();
        $del->close();

        echo json_encode(['status'=>'success','message'=>'Password updated']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Update failed']);
    }
    $stmt->close();
}


// ====================

function sendOTP($email) {
    global $conn;

    $email = sanitize($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
        return;
    }

	$stmt = $conn->prepare("SELECT activated FROM users WHERE email = ? LIMIT 1");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$stmt->bind_result($activated);
	$found = $stmt->fetch();
    $stmt->close();

    // If not found OR already activated → return generic success but DO NOT send OTP
    if (!$found || intval($activated) === 1) {
        echo json_encode(['status' => 'success', 'message' => 'If the email is eligible, an OTP was sent.']);
        return;
    }

    $otp = strval(random_int(100000, 999999));

    $stmt = $conn->prepare("REPLACE INTO otp_table (email, otp, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $stmt->close();

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'YOUR_USERNAME'; // Your google username
        $mail->Password = 'YOUR_APP_PASSWORD'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@exam-miner.com', 'Exam-Miner 2.0 OTP Verification');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Signup OTP';
$mail->Body = '
<!DOCTYPE html>
<html>
  <body style="margin:0; padding:0; background:#0b1220;">
    <!-- Preheader (hidden) -->
    <div style="display:none; font-size:1px; color:#0b1220; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
      Your Exam Miner verification code is ' . htmlspecialchars($otp) . ' and expires in 5 minutes.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0b1220;">
      <tr>
        <td align="center" style="padding:32px 16px;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px; background:#ffffff; border:1px solid #e5e7eb; border-radius:14px;">
            <tr>
              <td align="center" style="padding:28px 28px 10px;">
                <div style="font-size:18px; font-weight:700; color:#2563eb; letter-spacing:.3px;">Exam Miner 2.0</div>
                <div style="font-size:22px; font-weight:700; color:#0f172a; margin-top:6px;">Email Verification</div>
              </td>
            </tr>

            <tr>
              <td style="padding:8px 28px 18px; color:#334155; font-size:15px; line-height:1.6;">
                Hello,<br>
                Use the one-time code below to verify your email. This code expires in 5 minutes.
              </td>
            </tr>

            <tr>
              <td align="center" style="padding:4px 28px 22px;">
                <div style="display:inline-block; font-size:28px; font-weight:700; letter-spacing:6px; color:#111827; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px; font-family:SFMono-Regular,Consolas,Menlo,monospace;">
                  ' . htmlspecialchars($otp) . '
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding:0 28px 16px; color:#64748b; font-size:13px; line-height:1.6;">
                If you did not request this, you can safely ignore this email. Please do not share this code with anyone.
              </td>
            </tr>

            <tr>
              <td align="center" style="padding:0 28px 28px; color:#94a3b8; font-size:12px;">
                &copy; ' . date('Y') . ' Exam Miner • Automated message — replies are not monitored.
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>';




        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'If the email is eligible, an OTP was sent.']);  // 'OTP sent'
    } catch (Exception $e) {
        //http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Email failed to send']);
    }
}

function validateOTP($email, $otpInput) {
    global $conn, $secret_key;

    $email = sanitize($email);
    $otpInput = sanitize($otpInput);

    // 1) Get OTP (and age)
    $stmt = $conn->prepare(
        "SELECT otp, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS age_min
         FROM otp_table WHERE email = ? LIMIT 1"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($storedOtp, $ageMin);
    $ok = $stmt->fetch() && hash_equals($storedOtp ?? '', $otpInput) && $ageMin !== null && $ageMin <= 5;
    $stmt->close();

    if (!$ok) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired OTP']);
        return;
    }

    // 2) Delete used OTP
    $del = $conn->prepare("DELETE FROM otp_table WHERE email = ?");
    $del->bind_param("s", $email);
    $del->execute();
    $del->close();

    // 3) Activate user and fetch authoritative identity
    $up = $conn->prepare("UPDATE users SET activated = 1 WHERE email = ? LIMIT 1");
    $up->bind_param("s", $email);
    $up->execute();
    $up->close();

    $u = null;
    $q = $conn->prepare("SELECT id, username FROM users WHERE email = ? LIMIT 1");
    $q->bind_param("s", $email);
    $q->execute();
    $res = $q->get_result();
    $u = $res->fetch_assoc();
    $q->close();

    if (!$u) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']); // very rare
        return;
    }

    // 4) Issue JWT with server-sourced claims only
    $payload = [
        "sub"      => (string)$u['id'],
        "user_id"  => (string)$u['id'],
        "id"       => (string)$u['id'],
        "username" => $u['username'],
        "email"    => $email,
        "scope"    => "api",
        "iat"      => time(),
        "exp"      => time() + 60*60*24
    ];
    $jwt = \Firebase\JWT\JWT::encode($payload, $secret_key, 'HS256');

    echo json_encode(['status' => 'success', 'message' => 'OTP verified', 'token' => $jwt]);
}


function cleanupOTP($email) {
    global $conn;
    $email = sanitize($email);
    $stmt = $conn->prepare("DELETE FROM otp_table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'OTP cleared']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $email    = $_POST['email'] ?? '';
    $otp      = $_POST['otp'] ?? '';
    $password = $_POST['password'] ?? '';
    $token    = $_POST['recovery_token'] ?? '';

    if ($action === 'send') {
        sendOTP($email);
    } elseif ($action === 'verify') {
        validateOTP($email, $otp);
    } elseif ($action === 'cleanup') {
        cleanupOTP($email);
    } elseif ($action === 'send_recovery') {
        sendRecoveryOTP($email);
    } elseif ($action === 'verify_recovery') {
        validateRecoveryOTP($email, $otp);
    } elseif ($action === 'reset_password') {
        resetPassword($email, $password, $token);
    } else {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'Invalid action']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Method not allowed']);
}
