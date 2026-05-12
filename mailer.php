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
