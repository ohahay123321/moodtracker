<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - MoodTrail</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌈</text></svg>">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="auth-container">
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
        
        <div class="auth-card">
            <div class="auth-logo">
                <h1>🔐 <span>Reset</span></h1>
                <p>Recover your account</p>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <p style="text-align: center; color: var(--text-secondary); margin-bottom: 24px; font-size: 14px;">
                Enter your email address and we'll send you a link to reset your password.
            </p>
            
            <form id="forgotForm" method="POST" action="api/auth.php">
                <input type="hidden" name="action" value="forgot_password">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>
                
                <div class="recaptcha-wrapper">
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="forgotBtn">
                    <span>Send Reset Link</span>
                </button>
            </form>
            
            <div class="auth-links">
                <p>Remember your password? <a href="login.php">Sign in</a></p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/auth.js"></script>
</body>
</html>