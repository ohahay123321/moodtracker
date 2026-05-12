<?php

require_once __DIR__ . '/vendor/autoload.php';

function getMailer() {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USER') ?: '';
    $mail->Password = getenv('SMTP_PASS') ?: '';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = getenv('SMTP_PORT') ?: 587;
    $mail->setFrom(getenv('SMTP_FROM_EMAIL') ?: getenv('SMTP_USER') ?: 'noreply@moodtrail.com', getenv('SMTP_FROM_NAME') ?: 'MoodTrail');
    $mail->isHTML(true);
    return $mail;
}
