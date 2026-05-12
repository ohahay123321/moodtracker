<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'moodtracker');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $db;
}

function initDB() {
    $db = getDB();
    
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email_verified TINYINT(1) DEFAULT 0,
        verification_token VARCHAR(64) DEFAULT NULL,
        reset_token VARCHAR(64) DEFAULT NULL,
        reset_token_expires DATETIME DEFAULT NULL,
        reminder_enabled TINYINT(1) DEFAULT 1,
        reminder_time TIME DEFAULT '21:00:00',
        last_reminder_sent DATE DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS mood_entries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        mood VARCHAR(50) NOT NULL,
        intensity INT NOT NULL,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    try { $db->exec("ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0"); } catch (PDOException $e) {}
    try { $db->exec("ALTER TABLE users ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL"); } catch (PDOException $e) {}
    try { $db->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL"); } catch (PDOException $e) {}
    try { $db->exec("ALTER TABLE users ADD COLUMN reset_token_expires DATETIME DEFAULT NULL"); } catch (PDOException $e) {}
    try { $db->exec("ALTER TABLE users ADD COLUMN last_reminder_sent DATE DEFAULT NULL"); } catch (PDOException $e) {}
    try { $db->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL"); } catch (PDOException $e) {}
}

function baseUrl() {
    $appUrl = getenv('APP_URL');
    if ($appUrl) return rtrim($appUrl, '/');
    if (php_sapi_name() === 'cli' || empty($_SERVER['HTTP_HOST'])) {
        return 'https://' . (getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'moodtrail.app');
    }
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? '/moodtracker' : '');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, avatar, reminder_enabled, reminder_time FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    return $result ?: null;
}

define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY') ?: '');
define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: '');

function verifyRecaptcha($token) {
    $data = ['secret' => RECAPTCHA_SECRET_KEY, 'response' => $token];
    if (ini_get('allow_url_fopen')) {
        $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, stream_context_create([
            'http' => ['method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded', 'content' => http_build_query($data)]
        ]));
        $result = $response ? json_decode($response, true) : [];
    } elseif (function_exists('curl_version')) {
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($data), CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = $response ? json_decode($response, true) : [];
    } else {
        return false;
    }
    return !empty($result['success']);
}

function normalizeEmail($email) {
    $email = strtolower(trim($email));
    $parts = explode('@', $email, 2);
    if (count($parts) !== 2) return $email;
    $local = $parts[0];
    $domain = $parts[1];

    if (str_replace('www.', '', $domain) === 'gmail.com') {
        $local = str_replace('.', '', $local);
        $local = explode('+', $local)[0];
    }

    return $local . '@' . $domain;
}

initDB();

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'moodtracker');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

