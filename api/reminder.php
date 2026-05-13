<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $db = getDB();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt = $db->prepare("SELECT reminder_enabled, reminder_time FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

echo json_encode([
    'success' => true,
    'enabled' => (int)($user['reminder_enabled'] ?? 0),
    'time' => $user['reminder_time'] ?? '21:00'
]);
