<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../lib/PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  exit;
}

function field($name) {
  return isset($_POST[$name]) ? trim($_POST[$name]) : "";
}

$mail = new PHPMailer(true);

try {
  // SMTP CONFIG
  $mail->isSMTP();
  $mail->Host       = 'smtp.udag.de';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'a118789';
  $mail->Password   = 'Lindrit20!';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = 22;

  // MAIL
  $mail->setFrom('info@hero-umzug.de', 'Hero Umzug');
  $mail->addAddress('info@hero-umzug.de');

  $replyTo = field("email");
  if ($replyTo) {
    $mail->addReplyTo($replyTo);
  }

  $mail->Subject = 'Neue Umzugsanfrage – Hero Umzug';

  $mail->Body =
"Neue Umzugsanfrage

Name: " . field("name") . "
E-Mail: " . field("email") . "
Telefon: " . field("phone") . "
Umzugsdatum: " . field("date") . "

STARTADRESSE
Straße: " . field("start_street") . " " . field("start_house") . "
PLZ / Ort: " . field("start_zip") . " " . field("start_city") . "
Etage: " . field("floor_start") . "

ZIELADRESSE
Straße: " . field("target_street") . " " . field("target_house") . "
PLZ / Ort: " . field("target_zip") . " " . field("target_city") . "
Etage: " . field("floor_target") . "

Wohnfläche / Menge :
" . field("size") . "

Zusatzinfos:
" . field("details");

  // ATTACHMENTS
  if (!empty($_FILES['attachments']['name'][0])) {
    foreach ($_FILES['attachments']['tmp_name'] as $i => $tmp) {
      if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
        if ($_FILES['attachments']['size'][$i] <= 10 * 1024 * 1024) {
          $mail->addAttachment(
            $tmp,
            basename($_FILES['attachments']['name'][$i])
          );
        }
      }
    }
  }

  $mail->send();
  header("Location: /anfrage-danke.html");
  exit;

} catch (Exception $e) {
  http_response_code(500);
  echo "Mail Fehler: " . $mail->ErrorInfo;
  exit;
}
