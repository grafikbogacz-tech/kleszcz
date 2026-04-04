<?php
$mail_to   = "stolarstwostyldom@interia.pl";
$mail_from = "webmaster@schody.katowice.pl";

// Pola tekstowe
$imie         = strip_tags(trim($_POST['imie']         ?? ''));
$telefon      = strip_tags(trim($_POST['telefon']      ?? ''));
$email        = strip_tags(trim($_POST['email']        ?? ''));
$kontakt_pref = strip_tags(trim($_POST['kontakt_pref'] ?? ''));
$rodzaj       = strip_tags(trim($_POST['rodzaj']       ?? ''));
$ksztalt      = strip_tags(trim($_POST['ksztalt']      ?? ''));
$drewno       = strip_tags(trim($_POST['drewno']       ?? ''));
$wykonczenie  = strip_tags(trim($_POST['wykonczenie']  ?? ''));
$wysokosc     = strip_tags(trim($_POST['wysokosc']     ?? ''));
$szerokosc    = strip_tags(trim($_POST['szerokosc']    ?? ''));
$stopnie      = strip_tags(trim($_POST['stopnie']      ?? ''));
$miejscowosc  = strip_tags(trim($_POST['miejscowosc']  ?? ''));
$uwagi        = strip_tags(trim($_POST['uwagi']        ?? ''));

// Balustrada (checkbox – może być wiele)
$balustrada = [];
if (!empty($_POST['balustrada'])) {
    foreach ((array)$_POST['balustrada'] as $b) {
        $balustrada[] = strip_tags(trim($b));
    }
}
$balustrada_str = implode(', ', $balustrada);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Nieprawidłowy adres e-mail.');
}

// Treść maila
$message  = "<h2 style='color:#005117;'>Nowe zapytanie o wycenę schodów</h2>";
$message .= "<table cellpadding='6' cellspacing='0' style='font-family:Arial,sans-serif;font-size:14px;'>";
$message .= "<tr><td colspan='2'><hr></td></tr>";
$message .= "<tr><td><b>Imię i nazwisko:</b></td><td>" . htmlspecialchars($imie) . "</td></tr>";
$message .= "<tr><td><b>Telefon:</b></td><td>" . htmlspecialchars($telefon) . "</td></tr>";
$message .= "<tr><td><b>E-mail:</b></td><td>" . htmlspecialchars($email) . "</td></tr>";
$message .= "<tr><td><b>Preferowany kontakt:</b></td><td>" . htmlspecialchars($kontakt_pref) . "</td></tr>";
$message .= "<tr><td colspan='2'><hr></td></tr>";
$message .= "<tr><td><b>Rodzaj schodów:</b></td><td>" . htmlspecialchars($rodzaj) . "</td></tr>";
$message .= "<tr><td><b>Kształt:</b></td><td>" . htmlspecialchars($ksztalt) . "</td></tr>";
$message .= "<tr><td><b>Balustrada:</b></td><td>" . htmlspecialchars($balustrada_str) . "</td></tr>";
$message .= "<tr><td><b>Drewno:</b></td><td>" . htmlspecialchars($drewno) . "</td></tr>";
$message .= "<tr><td><b>Wykończenie:</b></td><td>" . htmlspecialchars($wykonczenie) . "</td></tr>";
$message .= "<tr><td colspan='2'><hr></td></tr>";
$message .= "<tr><td><b>Wysokość kondygnacji:</b></td><td>" . htmlspecialchars($wysokosc) . " cm</td></tr>";
$message .= "<tr><td><b>Szerokość biegu:</b></td><td>" . htmlspecialchars($szerokosc) . " cm</td></tr>";
$message .= "<tr><td><b>Liczba stopni:</b></td><td>" . htmlspecialchars($stopnie) . "</td></tr>";
$message .= "<tr><td><b>Miejscowość:</b></td><td>" . htmlspecialchars($miejscowosc) . "</td></tr>";
if ($uwagi !== '') {
    $message .= "<tr><td><b>Uwagi:</b></td><td>" . nl2br(htmlspecialchars($uwagi)) . "</td></tr>";
}
$message .= "</table>";

// Załączniki
$attachments = [];
$allowed_ext = ['jpg', 'jpeg', 'png', 'pdf', 'dwg'];
$max_size    = 10 * 1024 * 1024; // 10 MB

if (!empty($_FILES['zalaczniki']['name'][0])) {
    $files = $_FILES['zalaczniki'];
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
        if ($files['size'][$i] > $max_size) continue;
        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) continue;
        $attachments[] = [
            'name'    => $files['name'][$i],
            'type'    => $files['type'][$i],
            'content' => file_get_contents($files['tmp_name'][$i]),
        ];
    }
}

// Budowanie maila (multipart jeśli są załączniki)
$boundary = md5(time());

if (!empty($attachments)) {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n";
    $headers .= "From: Formularz Wyceny <" . $mail_from . ">\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";

    $body  = "--" . $boundary . "\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $message . "\r\n\r\n";

    foreach ($attachments as $att) {
        $body .= "--" . $boundary . "\r\n";
        $body .= "Content-Type: " . $att['type'] . "; name=\"" . $att['name'] . "\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"" . $att['name'] . "\"\r\n\r\n";
        $body .= chunk_split(base64_encode($att['content'])) . "\r\n";
    }
    $body .= "--" . $boundary . "--";

} else {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Formularz Wyceny <" . $mail_from . ">\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $body = $message;
}

$subject = "Wycena schodów – " . ($imie ?: 'nowe zapytanie') . " – " . ($miejscowosc ?: 'brak miejscowości');
$sent = mail($mail_to, $subject, $body, $headers);

// Auto-odpowiedź do klienta
if ($sent && $email !== '') {
    $autoSubject = 'Otrzymaliśmy Twoje zapytanie o wycenę – Kleszcz Stolarstwo';

    $autoBody  = "<div style='font-family:Arial,sans-serif;font-size:15px;color:#222;max-width:600px;'>";
    $autoBody .= "<p>Dzień dobry" . ($imie !== '' ? ", <strong>" . htmlspecialchars($imie) . "</strong>" : '') . ",</p>";
    $autoBody .= "<p>Dziękujemy za przesłanie zapytania o wycenę schodów. Skontaktujemy się z Tobą najszybciej jak to możliwe – zazwyczaj w ciągu 1–2 dni roboczych – aby omówić szczegóły i umówić <strong>bezpłatny pomiar</strong>.</p>";

    $autoBody .= "<table cellpadding='6' cellspacing='0' style='font-family:Arial,sans-serif;font-size:14px;border-collapse:collapse;margin:16px 0;'>";
    $autoBody .= "<tr><td colspan='2'><strong style='color:#005117;'>Podsumowanie Twojego zapytania:</strong></td></tr>";
    $autoBody .= "<tr><td colspan='2'><hr style='border:none;border-top:1px solid #ddd;'></td></tr>";
    if ($rodzaj !== '')       $autoBody .= "<tr><td style='color:#666;padding-right:16px;'>Rodzaj schodów:</td><td>" . htmlspecialchars($rodzaj) . "</td></tr>";
    if ($ksztalt !== '')      $autoBody .= "<tr><td style='color:#666;padding-right:16px;'>Kształt:</td><td>" . htmlspecialchars($ksztalt) . "</td></tr>";
    if ($drewno !== '')       $autoBody .= "<tr><td style='color:#666;padding-right:16px;'>Drewno:</td><td>" . htmlspecialchars($drewno) . "</td></tr>";
    if ($balustrada_str !== '') $autoBody .= "<tr><td style='color:#666;padding-right:16px;'>Balustrada:</td><td>" . htmlspecialchars($balustrada_str) . "</td></tr>";
    if ($miejscowosc !== '')  $autoBody .= "<tr><td style='color:#666;padding-right:16px;'>Miejscowość:</td><td>" . htmlspecialchars($miejscowosc) . "</td></tr>";
    $autoBody .= "</table>";

    $autoBody .= "<p>Jeśli masz dodatkowe pytania, zadzwoń do nas:<br><strong><a href='tel:+48500261410' style='color:#005117;'>+48 500 261 410</a></strong><br>Pon – Pt: 8:00 – 17:00</p>";
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
    header('Location: wycena.html?wyslano=1');
} else {
    header('Location: wycena.html?blad=1');
}
exit;
?>
