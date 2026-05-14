<?php

require_once __DIR__ . '/vendor/autoload.php';

function getMailer() {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = getenv('MAIL_SMTP_HOST') ?: getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('MAIL_SMTP_USER') ?: getenv('SMTP_USER') ?: '';
    $mail->Password = getenv('MAIL_SMTP_PASS') ?: getenv('SMTP_PASS') ?: '';
    $enc = getenv('MAIL_SMTP_ENCRYPTION') ?: 'tls';
    $mail->SMTPSecure = $enc === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = getenv('MAIL_SMTP_PORT') ?: getenv('SMTP_PORT') ?: 587;
    $mail->setFrom(
        getenv('MAIL_FROM') ?: getenv('SMTP_FROM_EMAIL') ?: getenv('MAIL_SMTP_USER') ?: getenv('SMTP_USER') ?: 'noreply@moodtrail.com',
        getenv('MAIL_FROM_NAME') ?: getenv('SMTP_FROM_NAME') ?: 'MoodTrail'
    );
    $mail->isHTML(true);
    return $mail;
}

function sendEmail($toEmail, $toName, $subject, $htmlBody, $altBody = '') {
    $mailer = getenv('MAIL_MAILER') ?: 'smtp';

    if ($mailer === 'brevo') {
        $apiKey = getenv('BREVO_API_KEY');
        $fromEmail = getenv('MAIL_FROM') ?: getenv('MAIL_FROM_ADDRESS') ?: 'noreply@moodtrail.app';
        $fromName = getenv('MAIL_FROM_NAME') ?: 'MoodTrail';

        $payload = json_encode([
            'sender' => ['email' => $fromEmail, 'name' => $fromName],
            'to' => [['email' => $toEmail, 'name' => $toName]],
            'subject' => $subject,
            'htmlContent' => $htmlBody,
            'textContent' => $altBody ?: strip_tags($htmlBody),
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                "api-key: $apiKey",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception("Brevo API error (HTTP $httpCode): $response");
        }
        return true;
    }

    $mail = getMailer();
    $mail->addAddress($toEmail, $toName);
    $mail->Subject = $subject;
    $mail->Body = $htmlBody;
    if ($altBody) {
        $mail->AltBody = $altBody;
    }
    $mail->send();
    return true;
}
