<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  exit;
}

$to = "info@hero-umzug.de";
$subject = "Neue Umzugsanfrage – Hero Umzug";

function field($name) {
  return isset($_POST[$name]) ? trim($_POST[$name]) : "";
}

$messageText = "
Neue Umzugsanfrage

Name: " . field("name") . "
E-Mail: " . field("email") . "
Telefon: " . field("phone") . "
Umzugsdatum: " . field("date") . "

STARTADRESSE
" . field("start_street") . " " . field("start_house") . "
" . field("start_zip") . " " . field("start_city") . "
Etage: " . field("floor_start") . "

ZIELADRESSE
" . field("target_street") . " " . field("target_house") . "
" . field("target_zip") . " " . field("target_city") . "
Etage: " . field("floor_target") . "

Wohnfläche / Menge:
" . field("size") . "

Zusatzinfos:
" . field("details") . "
";

$boundary = md5(time());
$replyTo = field("email") ?: "info@hero-umzug.de";

$headers  = "From: Hero Umzug <info@hero-umzug.de>\r\n";
$headers .= "Reply-To: $replyTo\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";

$body  = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$body .= $messageText . "\r\n";

if (
  isset($_FILES["attachments"]) &&
  is_array($_FILES["attachments"]["name"])
) {
  $count = count($_FILES["attachments"]["name"]);

  for ($i = 0; $i < $count; $i++) {

    if ($_FILES["attachments"]["error"][$i] !== UPLOAD_ERR_OK) continue;
    if ($_FILES["attachments"]["size"][$i] > 10 * 1024 * 1024) continue;

    $tmp  = $_FILES["attachments"]["tmp_name"][$i];
    if (!is_uploaded_file($tmp)) continue;

    $name = basename($_FILES["attachments"]["name"][$i]);
    $data = chunk_split(base64_encode(file_get_contents($tmp)));

    $body .= "--$boundary\r\n";
    $body .= "Content-Type: application/octet-stream; name=\"$name\"\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$name\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= $data . "\r\n";
  }
}

$body .= "--$boundary--";

if (mail($to, $subject, $body, $headers)) {
  header("Location: /anfrage-danke.html");
  exit;
}

http_response_code(500);
echo "Fehler beim Senden der E-Mail.";
exit;
