document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strengthBar');

    if (passwordInput && strengthBar) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            strengthBar.className = 'password-strength-bar';

            if (password.length === 0) {
                strengthBar.style.width = '0';
            } else if (strength <= 2) {
                strengthBar.classList.add('weak');
            } else if (strength <= 4) {
                strengthBar.classList.add('medium');
            } else {
                strengthBar.classList.add('strong');
            }
        });
    }

    function validateRecaptcha(form) {
        const response = grecaptcha && grecaptcha.getResponse();
        if (!response || response.length === 0) {
            alert('Please complete the reCAPTCHA verification');
            return false;
        }
        return true;
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms');

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters!');
                return false;
            }

            if (!terms.checked) {
                e.preventDefault();
                alert('Please accept the terms and conditions!');
                return false;
            }

            if (!validateRecaptcha(this)) {
                e.preventDefault();
                return false;
            }

            const btn = document.getElementById('registerBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner" style="width: 20px; height: 20px; border-width: 2px;"></span> Creating account...';
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateRecaptcha(this)) {
                e.preventDefault();
                return false;
            }

            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner" style="width: 20px; height: 20px; border-width: 2px;"></span> Signing in...';
        });
    }

    const forgotForm = document.getElementById('forgotForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            if (!validateRecaptcha(this)) {
                e.preventDefault();
                return false;
            }

            const btn = document.getElementById('forgotBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner" style="width: 20px; height: 20px; border-width: 2px;"></span> Sending...';
        });
    }
});
