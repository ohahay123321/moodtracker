<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['sent' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$db = getDB();

$stmt = $db->prepare("SELECT id, name, email, reminder_enabled, reminder_time, last_reminder_sent FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || !$user['reminder_enabled']) {
    echo json_encode(['sent' => false, 'message' => 'Reminders not enabled']);
    exit;
}

$today = date('Y-m-d');

if ($user['last_reminder_sent'] === $today) {
    echo json_encode(['sent' => false, 'message' => 'Already sent today']);
    exit;
}

require_once '../mailer.php';

try {
    $mail = getMailer();
    $mail->addAddress($user['email'], $user['name']);
    $mail->Subject = 'Time to log your mood! - MoodTrail';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto; padding: 32px; background: #f8f9fa; border-radius: 12px;'>
            <div style='text-align: center; font-size: 48px; margin-bottom: 16px;'>🌈</div>
            <h1 style='text-align: center; color: #1a1a2e; margin-bottom: 8px;'>Hey {$user['name']}! How are you feeling?</h1>
            <p style='text-align: center; color: #64748b; margin-bottom: 24px;'>It's time to check in with yourself. Logging your mood only takes a few seconds.</p>
            <div style='text-align: center;'>
                <a href='" . baseUrl() . "/add-mood.php'
                   style='display: inline-block; padding: 14px 40px; background: #6C63FF; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;'>
                    Log Your Mood
                </a>
            </div>
            <p style='text-align: center; color: #94a3b8; font-size: 12px; margin-top: 24px;'>
                Track your emotions daily and discover patterns.<br>
                <a href='" . baseUrl() . "/dashboard.php' style='color: #6C63FF;'>Visit your dashboard</a>
            </p>
        </div>
    ";
    $mail->AltBody = "Hey {$user['name']}! How are you feeling?\n\nIt's time to check in with yourself. Log your mood here:\n" . baseUrl() . "/add-mood.php";

    $mail->send();

    $update = $db->prepare("UPDATE users SET last_reminder_sent = ? WHERE id = ?");
    $update->execute([$today, $user_id]);

    echo json_encode(['sent' => true, 'message' => 'Reminder sent to ' . $user['email']]);
} catch (Exception $e) {
    echo json_encode(['sent' => false, 'message' => 'Failed to send: ' . $e->getMessage()]);
}
