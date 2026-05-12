<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

$token = $_POST['g-recaptcha-response'] ?? '';
if (empty($token) || !verifyRecaptcha($token)) {
    $_SESSION['error'] = 'Please complete the reCAPTCHA verification';
    switch ($action) {
        case 'login':
            header('Location: ../login.php');
            break;
        case 'register':
            header('Location: ../register.php');
            break;
        case 'forgot_password':
            header('Location: ../forgot-password.php');
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    exit;
}

switch ($action) {
    case 'login':
        login();
        break;
    case 'register':
        register();
        break;
    case 'forgot_password':
        forgotPassword();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function login() {
    $db = getDB();
    
    $email = normalizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields';
        header('Location: ../login.php');
        return;
    }
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['error'] = 'Invalid email or password';
        header('Location: ../login.php');
        return;
    }
    
    if (!$user['email_verified']) {
        $_SESSION['error'] = 'Please verify your email address before logging in. Check your inbox for the verification link.';
        header('Location: ../login.php');
        return;
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_avatar'] = $user['avatar'] ?? null;
    
    header('Location: ../dashboard.php');
    exit;
}

function register() {
    $db = getDB();
    
    $name = trim($_POST['name'] ?? '');
    $email = normalizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields';
        header('Location: ../register.php');
        return;
    }
    
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters';
        header('Location: ../register.php');
        return;
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: ../register.php');
        return;
    }
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Email already registered';
        header('Location: ../register.php');
        return;
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, verification_token) VALUES (?, ?, ?, ?)");
    
    try {
        $stmt->execute([$name, $email, $hashed_password, $token]);
        
        require_once '../mailer.php';
        $mail = getMailer();
        $mail->addAddress($email, $name);
        $mail->Subject = 'Verify your MoodTrail account';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto; padding: 32px; background: #f8f9fa; border-radius: 12px;'>
                <div style='text-align: center; font-size: 48px; margin-bottom: 16px;'>🌈</div>
                <h1 style='text-align: center; color: #1a1a2e; margin-bottom: 8px;'>Welcome to MoodTrail!</h1>
                <p style='text-align: center; color: #64748b; margin-bottom: 24px;'>Click the button below to verify your email address.</p>
                <div style='text-align: center;'>
                    <a href='" . baseUrl() . "/verify.php?token={$token}'
                       style='display: inline-block; padding: 14px 40px; background: #6C63FF; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;'>
                        Verify Email
                    </a>
                </div>
                <p style='text-align: center; color: #94a3b8; font-size: 12px; margin-top: 24px;'>
                    Or copy this link:<br>
                    " . baseUrl() . "/verify.php?token={$token}
                </p>
            </div>
        ";
        $mail->AltBody = "Welcome to MoodTrail!\n\nClick this link to verify your email:\n" . baseUrl() . "/verify.php?token={$token}";
        $mail->send();
        
        $_SESSION['success'] = 'Account created! Please check your email to verify your account.';
        header('Location: ../verify-email.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        header('Location: ../register.php');
        return;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Account created but failed to send verification email. Contact support.';
        header('Location: ../register.php');
        return;
    }
}

function forgotPassword() {
    $db = getDB();
    $email = normalizeEmail($_POST['email'] ?? '');
    
    if (empty($email)) {
        $_SESSION['error'] = 'Please enter your email address';
        header('Location: ../forgot-password.php');
        return;
    }
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);
        
        require_once '../mailer.php';
        try {
            $mail = getMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Reset your MoodTrail password';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto; padding: 32px; background: #f8f9fa; border-radius: 12px;'>
                    <div style='text-align: center; font-size: 48px; margin-bottom: 16px;'>🔐</div>
                    <h1 style='text-align: center; color: #1a1a2e; margin-bottom: 8px;'>Reset Your Password</h1>
                    <p style='text-align: center; color: #64748b; margin-bottom: 24px;'>Click the button below to reset your password. This link expires in 24 hours.</p>
                    <div style='text-align: center;'>
                        <a href='" . baseUrl() . "/reset-password.php?token={$token}'
                           style='display: inline-block; padding: 14px 40px; background: #6C63FF; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;'>
                            Reset Password
                        </a>
                    </div>
                    <p style='text-align: center; color: #94a3b8; font-size: 12px; margin-top: 24px;'>
                        Or copy this link:<br>
                        " . baseUrl() . "/reset-password.php?token={$token}
                    </p>
                </div>
            ";
            $mail->AltBody = "Reset your MoodTrail password:\n\n" . baseUrl() . "/reset-password.php?token={$token}\n\nThis link expires in 24 hours.";
            $mail->send();
        } catch (Exception $e) {}
    }
    
    $_SESSION['success'] = 'If an account exists with this email, a reset link has been sent.';
    header('Location: ../forgot-password.php');
    exit;
}