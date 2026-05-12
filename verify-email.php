<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - MoodTrail</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌈</text></svg>">
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
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div style="text-align: center; padding: 20px 0;">
                <div style="font-size: 64px; margin-bottom: 16px;">📧</div>
                <h1 style="margin-bottom: 12px;">Check Your Email</h1>
                <p style="color: var(--text-secondary); margin-bottom: 24px; line-height: 1.6;">
                    We've sent a verification link to your email address.<br>
                    Click the link to activate your account.
                </p>
                <div style="background: var(--bg); padding: 20px; border-radius: var(--radius-sm); margin-bottom: 24px;">
                    <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 4px;">Didn't receive the email?</p>
                    <p style="font-size: 13px; color: var(--text-light);">Check your spam folder or try again.</p>
                </div>
                <div class="auth-links">
                    <p><a href="login.php">Go to Sign In</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>
</html>
