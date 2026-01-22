<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
var_dump($_FILES);
phpinfo();
exit;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../lib/PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit;
}

function field(string $name): string {
  return isset($_POST[$name]) ? trim($_POST[$name]) : '';
}

$mail = new PHPMailer(true);

try {
  /* SMTP */
  $mail->isSMTP();
  $mail->Host       = 'smtp.udag.de';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'info@hero-umzug.de';
  $mail->Password   = 'Lindrit2000!';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = 22;

  /* Absender / Empfänger */
  $mail->setFrom('info@hero-umzug.de', 'Hero Umzug');
  $mail->addAddress('info@hero-umzug.de');
  $mail->addReplyTo(field('email'), field('name'));

  /* Inhalt */
  $mail->CharSet = 'UTF-8';
  $mail->isHTML(false);
  $mail->Subject = 'Neue Umzugsanfrage – Hero Umzug';

  $mail->Body =
"Neue Umzugsanfrage

Name: " . field('name') . "
E-Mail: " . field('email') . "
Telefon: " . field('phone') . "
Umzugsdatum: " . field('date') . "

STARTADRESSE
Straße: " . field('start_street') . " " . field('start_house') . "
PLZ / Ort: " . field('start_zip') . " " . field('start_city') . "
Etage: " . field('floor_start') . "

ZIELADRESSE
Straße: " . field('target_street') . " " . field('target_house') . "
PLZ / Ort: " . field('target_zip') . " " . field('target_city') . "
Etage: " . field('floor_target') . "

Wohnfläche / Menge:
" . field('size') . "

Zusatzinfos:
" . field('details');

  /* Anhänge */
  if (!empty($_FILES['attachments']['tmp_name'])) {

    // EINZELNE Datei
    if (is_string($_FILES['attachments']['tmp_name'])) {
      if ($_FILES['attachments']['error'] === UPLOAD_ERR_OK) {
        $mail->addAttachment(
          $_FILES['attachments']['tmp_name'],
          $_FILES['attachments']['name']
        );
      }
    }

    // MEHRERE Dateien
    if (is_array($_FILES['attachments']['tmp_name'])) {
      foreach ($_FILES['attachments']['tmp_name'] as $i => $tmp) {
        if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
          $mail->addAttachment($tmp, $_FILES['attachments']['name'][$i]);
        }
      }
    }
  }

  $mail->send();

  header('Location: /anfrage-danke.html');
  exit;

} catch (Exception $e) {
  http_response_code(500);
  echo 'Mail Fehler: ' . $mail->ErrorInfo;
}
