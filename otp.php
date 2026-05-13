<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } require_once 'config.php'; ?>
<?php
$userId = $_SESSION['otp_user_id'] ?? null;
$email = $_SESSION['otp_email'] ?? '';

if (!$userId) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login - MoodTrail</title>
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
            <div class="auth-logo">
                <h1>🔑 <span>Verify</span></h1>
                <p>Enter the code sent to your email</p>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <p style="text-align: center; color: var(--text-secondary); margin-bottom: 24px; font-size: 14px;">
                We sent a 6-digit code to <strong><?php echo htmlspecialchars($email); ?></strong><br>
                It expires in 5 minutes.
            </p>
            
            <form id="otpForm" method="POST" action="api/auth.php">
                <input type="hidden" name="action" value="verify_otp">
                
                <div class="form-group">
                    <label for="otp">6-Digit Code</label>
                    <input type="text" id="otp" name="otp" placeholder="000000" required
                           maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
                           style="text-align: center; font-size: 24px; letter-spacing: 8px; font-family: monospace;">
                </div>
                
                <button type="submit" class="btn btn-primary" id="otpBtn">
                    <span>Verify & Sign In</span>
                </button>
            </form>
            
            <div class="auth-links">
                <p style="margin-bottom: 8px;">Didn't receive the code? <a href="login.php">Try again</a></p>
                <p><a href="login.php">Back to sign in</a></p>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('otpForm').addEventListener('submit', function(e) {
            const otp = document.getElementById('otp').value.trim();
            if (!/^\d{6}$/.test(otp)) {
                e.preventDefault();
                alert('Please enter a valid 6-digit code');
                return;
            }
            const btn = document.getElementById('otpBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner" style="width: 20px; height: 20px; border-width: 2px;"></span> Verifying...';
        });

        document.getElementById('otp').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    </script>
</body>
</html>
