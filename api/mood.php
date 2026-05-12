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
    case 'add_mood':
        addMood();
        break;
    case 'get_moods':
        getMoods();
        break;
    case 'delete_mood':
        deleteMood();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function addMood() {
    $db = getDB();
    
    $mood = $_POST['mood'] ?? '';
    $intensity = (int)($_POST['intensity'] ?? 5);
    $notes = trim($_POST['notes'] ?? '');
    $mood_date_raw = $_POST['mood_date'] ?? '';
    
    if ($mood_date_raw) {
        $ts = strtotime($mood_date_raw);
        $mood_date = $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
    } else {
        $mood_date = date('Y-m-d H:i:s');
    }
    
    $valid_moods = ['Happy', 'Sad', 'Angry', 'Calm', 'Anxious', 'Excited', 'Tired', 'Loved', 'Grateful', 'Hopeful', 'Confident', 'Peaceful', 'Confused', 'Stressed', 'Bored', 'Frustrated', 'Joyful', 'Surprised', 'Nostalgic', 'Energetic', 'Disappointed', 'Embarrassed', 'Inspired', 'Curious'];
    
    if (empty($mood) || !in_array($mood, $valid_moods)) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid mood']);
        return;
    }
    
    if ($intensity < 1 || $intensity > 10) {
        $intensity = 5;
    }
    
    $stmt = $db->prepare("INSERT INTO mood_entries (user_id, mood, intensity, notes, created_at) VALUES (?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([
            $_SESSION['user_id'],
            $mood,
            $intensity,
            $notes,
            $mood_date
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Mood logged successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to save mood entry', 'debug' => $e->getMessage()]);
    }
}

function getMoods() {
    $db = getDB();
    
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $userId = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT * FROM mood_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute([$userId]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $entries]);
}

function deleteMood() {
    $db = getDB();
    
    $id = (int)($_POST['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid entry ID']);
        return;
    }
    
    $stmt = $db->prepare("DELETE FROM mood_entries WHERE id = ? AND user_id = ?");
    
    try {
        $stmt->execute([$id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Entry deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Entry not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete entry']);
    }
}