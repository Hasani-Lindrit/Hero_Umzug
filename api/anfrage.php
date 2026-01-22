<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
var_dump($_FILES);
phpinfo();
exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit;
}

$to = 'info@hero-umzug.de';
$subject = 'Neue Umzugsanfrage – Hero Umzug';

function field($key){
  return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

$message = "
Neue Umzugsanfrage

Name: " . field('name') . "
E-Mail: " . field('email') . "
Telefon: " . field('phone') . "
Umzugsdatum: " . field('date') . "

STARTADRESSE
" . field('start_street') . " " . field('start_house') . "
" . field('start_zip') . " " . field('start_city') . "

ZIELADRESSE
" . field('target_street') . " " . field('target_house') . "
" . field('target_zip') . " " . field('target_city') . "

Etage Start: " . field('floor_start') . "
Etage Ziel: " . field('floor_target') . "

Wohnfläche / Menge:
" . field('size') . "

Zusatzinfos:
" . field('details') . "
";

$boundary = md5(time());

$headers  = "From: Hero Umzug <info@hero-umzug.de>\r\n";
$headers .= "Reply-To: " . field('email') . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";

$body  = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$body .= $message . "\r\n";

if (!empty($_FILES['attachments']['tmp_name'])) {
  foreach ($_FILES['attachments']['tmp_name'] as $i => $tmp) {

    if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) continue;
    if ($_FILES['attachments']['size'][$i] > 8 * 1024 * 1024) continue;

    $name = basename($_FILES['attachments']['name'][$i]);
    $type = mime_content_type($tmp) ?: 'application/octet-stream';
    $data = chunk_split(base64_encode(file_get_contents($tmp)));

    $body .= "--$boundary\r\n";
    $body .= "Content-Type: $type; name=\"$name\"\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$name\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= $data . "\r\n";
  }
}

$body .= "--$boundary--";

if (mail($to, $subject, $body, $headers)) {
  header('Location: /anfrage-danke.html');
  exit;
}

http_response_code(500);
echo 'E-Mail konnte nicht gesendet werden.';
