<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (file_exists(__DIR__ . '/.env')) {
        Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
        foreach ($_ENV as $key => $value) {
            putenv("$key=$value");
        }
    }
}

$mysqlUrl = getenv('MYSQL_URL');
if ($mysqlUrl) {
    $parts = parse_url($mysqlUrl);
    define('DB_HOST', $parts['host'] ?? 'localhost');
    define('DB_PORT', $parts['port'] ?? '3306');
    define('DB_USER', $parts['user'] ?? 'root');
    define('DB_PASS', $parts['pass'] ?? '');
    define('DB_NAME', trim($parts['path'] ?? 'moodtracker', '/') ?: 'moodtracker');
} else {
    define('DB_HOST', getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: 'localhost'));
    define('DB_NAME', getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'moodtracker'));
    define('DB_USER', getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root'));
    define('DB_PASS', getenv('DB_PASS') ?: (getenv('MYSQLPASSWORD') ?: ''));
    define('DB_PORT', getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: '3306'));
}

function _dbError() {
    http_response_code(503);
    if (php_sapi_name() === 'cli') {
        echo "Database connection failed\n";
        exit(1);
    }
    $isJson = !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    if ($isJson) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Service unavailable']);
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head><meta charset="UTF-8"><title>Service Unavailable - MoodTrail</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg);">
    <div class="card" style="text-align:center;padding:48px;max-width:480px;">
        <div style="font-size:64px;margin-bottom:16px;">🔌</div>
        <h1 style="margin-bottom:8px;">Service Unavailable</h1>
        <p style="color:var(--text-secondary);margin-bottom:24px;">We're having trouble connecting to the database. Please try again in a moment.</p>
        <a href="landing.php" class="btn btn-primary">Back to Home</a>
    </div>
    </body>
    </html>
    <?php
    exit;
}

function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            _dbError();
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
    try { $db->exec("ALTER TABLE users ADD COLUMN otp_code VARCHAR(6) DEFAULT NULL"); } catch (PDOException $e) {}
    try { $db->exec("ALTER TABLE users ADD COLUMN otp_expires DATETIME DEFAULT NULL"); } catch (PDOException $e) {}
    try { $db->exec("ALTER TABLE users ADD COLUMN otp_attempts TINYINT(1) DEFAULT 0"); } catch (PDOException $e) {}
}

function baseUrl() {
    $appUrl = getenv('APP_URL');
    if ($appUrl) {
        if (!preg_match('#^https?://#', $appUrl)) $appUrl = 'https://' . $appUrl;
        return rtrim($appUrl, '/');
    }
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

