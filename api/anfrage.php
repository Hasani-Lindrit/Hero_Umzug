<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  exit;
}

$to = "info@hero-umzug.de"; // ← HIER DEINE ZIELMAIL
$subject = "Neue Umzugsanfrage – Hero Umzug";

// Textfelder sicher einsammeln
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
Straße: " . field("start_street") . " " . field("start_house") . "
PLZ / Ort: " . field("start_zip") . " " . field("start_city") . "
Etage: " . field("floor_start") . "

ZIELADRESSE
Straße: " . field("target_street") . " " . field("target_house") . "
PLZ / Ort: " . field("target_zip") . " " . field("target_city") . "
Etage: " . field("floor_target") . "

Wohnfläche / Menge:
" . field("size") . "

Zusatzinfos:
" . field("details") . "
";

// Mail-Header vorbereiten
$boundary = md5(time());
$headers  = "From: Hero Umzug <info@hero-umzug.de>\r\n";
$headers .= "Reply-To: " . field("email") . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";

// Body starten
$body  = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
$body .= $messageText . "\r\n";

// Anhänge verarbeiten
if (!empty($_FILES["attachments"]["name"][0])) {
  for ($i = 0; $i < count($_FILES["attachments"]["name"]); $i++) {
    if ($_FILES["attachments"]["error"][$i] === UPLOAD_ERR_OK) {

      $fileTmp  = $_FILES["attachments"]["tmp_name"][$i];
      $fileName = basename($_FILES["attachments"]["name"][$i]);
      $fileType = mime_content_type($fileTmp);
      $fileData = chunk_split(base64_encode(file_get_contents($fileTmp)));

      $body .= "--$boundary\r\n";
      $body .= "Content-Type: $fileType; name=\"$fileName\"\r\n";
      $body .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n";
      $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
      $body .= $fileData . "\r\n";
    }
  }
}

$body .= "--$boundary--";

// Mail senden
$success = mail($to, $subject, $body, $headers);

if ($success) {
  http_response_code(200);
} else {
  http_response_code(500);
}
header("Location: /anfrage-danke.html");
exit;