<?php
$mail_to   = "stolarstwostyldom@interia.pl";
$mail_from = "webmaster@schody.katowice.pl";

$name    = strip_tags(trim($_POST['name']    ?? ''));
$email   = strip_tags(trim($_POST['email']   ?? ''));
$phone   = strip_tags(trim($_POST['phone']   ?? ''));
$subject = strip_tags(trim($_POST['subject'] ?? ''));
$text    = strip_tags(trim($_POST['message'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Nieprawidłowy adres e-mail.');
}

$mail_subject = ($subject !== '') ? $subject : 'Kontakt ze strony schody.katowice.pl';

$bodyHtml  = "<h3>Wiadomość z formularza kontaktowego</h3><br>";
$bodyHtml .= "<b>Imię i nazwisko:</b> " . htmlspecialchars($name)    . "<br>";
$bodyHtml .= "<b>E-mail:</b> "          . htmlspecialchars($email)   . "<br>";
if ($phone !== '') {
    $bodyHtml .= "<b>Telefon:</b> "     . htmlspecialchars($phone)   . "<br>";
}
$bodyHtml .= "<b>Temat:</b> "           . htmlspecialchars($subject) . "<br>";
$bodyHtml .= "<b>Wiadomość:</b><br>"    . nl2br(htmlspecialchars($text)) . "<br>";

// --- Obsługa załącznika ---
$hasAttachment = false;
$attachmentData = '';
$attachmentName = '';
$attachmentMime = '';

if (!empty($_FILES['attachment']['tmp_name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $allowedExt   = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    $maxSize      = 5 * 1024 * 1024; // 5 MB

    $fileTmp  = $_FILES['attachment']['tmp_name'];
    $fileOrig = basename($_FILES['attachment']['name']);
    $fileExt  = strtolower(pathinfo($fileOrig, PATHINFO_EXTENSION));
    $fileMime = mime_content_type($fileTmp);
    $fileSize = $_FILES['attachment']['size'];

    if ($fileSize <= $maxSize && in_array($fileMime, $allowedTypes) && in_array($fileExt, $allowedExt)) {
        $hasAttachment  = true;
        $attachmentData = chunk_split(base64_encode(file_get_contents($fileTmp)));
        $attachmentName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileOrig);
        $attachmentMime = $fileMime;
    }
}

// --- Budowanie wiadomości ---
$boundary = md5(uniqid((string)time()));

if ($hasAttachment) {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
    $headers .= "From: Formularz WWW <{$mail_from}>\r\n";
    $headers .= "Reply-To: {$email}\r\n";

    $message  = "--{$boundary}\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $bodyHtml . "\r\n\r\n";
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: {$attachmentMime}; name=\"{$attachmentName}\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n";
    $message .= "Content-Disposition: attachment; filename=\"{$attachmentName}\"\r\n\r\n";
    $message .= $attachmentData . "\r\n";
    $message .= "--{$boundary}--";
} else {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Formularz WWW <{$mail_from}>\r\n";
    $headers .= "Reply-To: {$email}\r\n";

    $message = $bodyHtml;
}

$sent = mail($mail_to, $mail_subject, $message, $headers);

// Auto-odpowiedź do klienta
if ($sent && $email !== '') {
    $autoSubject = 'Dziękujemy za wiadomość – Kleszcz Stolarstwo';

    $autoBody  = "<div style='font-family:Arial,sans-serif;font-size:15px;color:#222;max-width:600px;'>";
    $autoBody .= "<p>Dzień dobry" . ($name !== '' ? ", <strong>" . htmlspecialchars($name) . "</strong>" : '') . ",</p>";
    $autoBody .= "<p>Dziękujemy za kontakt. Otrzymaliśmy Twoją wiadomość i odpiszemy najszybciej jak to możliwe – zazwyczaj w ciągu 1–2 dni roboczych.</p>";
    $autoBody .= "<p>Jeśli sprawa jest pilna, zadzwoń do nas:<br><strong><a href='tel:+48500261410' style='color:#005117;'>+48 500 261 410</a></strong><br>Pon – Pt: 8:00 – 17:00</p>";
    $autoBody .= "<hr style='border:none;border-top:1px solid #ddd;margin:24px 0;'>";
    $autoBody .= "<p style='font-size:13px;color:#666;'>Ta wiadomość została wygenerowana automatycznie – prosimy na nią nie odpowiadać.<br>";
    $autoBody .= "<strong>Kleszcz Stolarstwo</strong> | 3 Maja 42, 32-650 Kęty | <a href='https://www.schody.katowice.pl' style='color:#005117;'>schody.katowice.pl</a></p>";
    $autoBody .= "</div>";

    $autoHeaders  = "MIME-Version: 1.0\r\n";
    $autoHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
    $autoHeaders .= "From: Kleszcz Stolarstwo <{$mail_from}>\r\n";
    $autoHeaders .= "Reply-To: stolarstwostyldom@interia.pl\r\n";

    mail($email, $autoSubject, $autoBody, $autoHeaders);
}

if ($sent) {
    header('Location: kontakt.html?wyslano=1');
} else {
    header('Location: kontakt.html?blad=1');
}
exit;
?>
