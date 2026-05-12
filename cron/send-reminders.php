<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

$now = date('H:i:00');
$today = date('Y-m-d');

$stmt = $db->prepare("SELECT id, name, email, reminder_time FROM users WHERE reminder_enabled = 1 AND reminder_time = ? AND (last_reminder_sent IS NULL OR last_reminder_sent != ?)");
$stmt->execute([$now, $today]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "No reminders to send at {$now}\n";
    exit;
}

require_once __DIR__ . '/../mailer.php';

$sent = 0;
$errors = 0;

foreach ($users as $user) {
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
        $update->execute([$today, $user['id']]);

        $sent++;
        echo "Reminder sent to {$user['email']} ({$user['name']})\n";
    } catch (Exception $e) {
        $errors++;
        echo "Failed to send reminder to {$user['email']}: {$e->getMessage()}\n";
    }
}

echo "Done. Sent: {$sent}, Errors: {$errors}\n";
