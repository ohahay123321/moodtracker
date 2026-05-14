<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['error'] = 'Invalid verification link.';
    header('Location: login.php');
    exit;
}

$db = getDB();

$stmt = $db->prepare("SELECT id, email_verified FROM users WHERE verification_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = 'Invalid or expired verification link.';
    header('Location: login.php');
    exit;
}

if ($user['email_verified']) {
    $_SESSION['success'] = 'Email already verified. Please sign in.';
    header('Location: login.php');
    exit;
}

$stmt = $db->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?");
$stmt->execute([$user['id']]);

$_SESSION['success'] = 'Email verified successfully! You can now sign in.';
header('Location: login.php');
exit;
