<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

switch ($action) {
    case 'update_profile':
        updateProfile();
        break;
    case 'change_password':
        changePassword();
        break;
    case 'update_avatar':
        updateAvatar();
        break;
    case 'delete_avatar':
        deleteAvatar();
        break;
    case 'update_reminders':
        updateReminders();
        break;
    case 'delete_account':
        deleteAccount();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function updateProfile() {
    $db = getDB();
    
    $name = trim($_POST['name'] ?? '');
    $email = normalizeEmail($_POST['email'] ?? '');
    
    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Name and email are required']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already in use']);
        return;
    }
    
    $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    
    try {
        $stmt->execute([$name, $email, $_SESSION['user_id']]);
        
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        
        $stmt2 = $db->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt2->execute([$_SESSION['user_id']]);
        $_SESSION['user_avatar'] = $stmt2->fetchColumn();
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
}

function changePassword() {
    $db = getDB();
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'All password fields are required']);
        return;
    }
    
    if (strlen($new_password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        return;
    }
    
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        return;
    }
    
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        return;
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    
    try {
        $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        
        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to change password']);
    }
}

function updateAvatar() {
    $db = getDB();

    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        return;
    }

    $file = $_FILES['avatar'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 2 * 1024 * 1024;

    if (!in_array($file['type'], $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF & WEBP allowed']);
        return;
    }

    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File must be under 2MB']);
        return;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
    $uploadPath = __DIR__ . '/../assets/uploads/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        return;
    }

    $stmt = $db->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $old = $stmt->fetchColumn();

    $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $stmt->execute([$filename, $_SESSION['user_id']]);

    if ($old && $old !== $filename) {
        $oldPath = __DIR__ . '/../assets/uploads/' . $old;
        if (file_exists($oldPath)) unlink($oldPath);
    }

    $_SESSION['user_avatar'] = $filename;

    echo json_encode(['success' => true, 'message' => 'Avatar updated', 'avatar' => $filename]);
}

function deleteAvatar() {
    $db = getDB();

    $stmt = $db->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $old = $stmt->fetchColumn();

    if ($old) {
        $oldPath = __DIR__ . '/../assets/uploads/' . $old;
        if (file_exists($oldPath)) unlink($oldPath);
    }

    $stmt = $db->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    unset($_SESSION['user_avatar']);

    echo json_encode(['success' => true, 'message' => 'Avatar removed']);
}

function updateReminders() {
    $db = getDB();
    
    $enabled = isset($_POST['enabled']) ? (int)$_POST['enabled'] : 1;
    $time = $_POST['time'] ?? '21:00';
    
    $stmt = $db->prepare("UPDATE users SET reminder_enabled = ?, reminder_time = ? WHERE id = ?");
    
    try {
        $stmt->execute([$enabled, $time, $_SESSION['user_id']]);
        
        echo json_encode(['success' => true, 'message' => 'Reminder settings updated']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to update settings']);
    }
}

function deleteAccount() {
    $db = getDB();
    
    $stmt = $db->prepare("DELETE FROM mood_entries WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    
    try {
        $stmt->execute([$_SESSION['user_id']]);
        
        session_destroy();
        
        echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete account']);
    }
}