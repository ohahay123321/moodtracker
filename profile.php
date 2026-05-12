<?php
session_start();
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - MoodTrail</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌈</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">
                    🌈 Mood<span>Trail</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="add-mood.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>Log Mood</span>
                    </a>
                    <a href="history.php" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>History</span>
                    </a>
                    <a href="reports.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Reports</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="profile.php" class="nav-link active">
                        <i class="fas fa-user-cog"></i>
                        <span>Profile</span>
                    </a>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <a href="api/logout.php" class="user-card">
                    <div class="user-avatar">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="assets/uploads/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                            <?php echo strtoupper(substr(($user['name'] ?? 'U'), 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                    </div>
                    <i class="fas fa-sign-out-alt" style="color: var(--text-light);"></i>
                </a>
            </div>
        </aside>
        
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Profile Settings</h1>
                </div>
                <div class="header-right">
                    <button class="header-btn">
                        <i class="fas fa-bell"></i>
                    </button>
                </div>
            </header>
            
            <div class="page-content">
                <div style="max-width: 700px; margin: 0 auto;">
                    <div class="card" style="text-align: center; margin-bottom: 24px;">
                        <div class="avatar-upload-wrapper">
                            <div class="profile-avatar-large" id="profileAvatar">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="assets/uploads/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                                <?php else: ?>
                                    <?php echo strtoupper(substr(($user['name'] ?? 'U'), 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <label class="avatar-upload-btn" for="avatarInput" title="Change photo">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                            <button class="avatar-remove-btn" id="removeAvatarBtn" title="Remove photo" style="<?php echo empty($user['avatar']) ? 'display:none;' : ''; ?>">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <h2><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h2>
                        <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="settings-section">
                            <h3>Account Information</h3>
                            
                            <form id="profileForm">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="settings-section">
                            <h3>Change Password</h3>
                            
                            <form id="passwordForm">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" required minlength="8">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-lock"></i>
                                    Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="settings-section">
                            <h3>Notification Settings</h3>
                            
                            <div class="setting-item">
                                <div class="setting-info">
                                    <h4>Daily Reminders</h4>
                                    <p>Get reminded to log your mood daily</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="reminderToggle" <?php echo ($user['reminder_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="setting-item">
                                <div class="setting-info">
                                    <h4>Reminder Time</h4>
                                    <p>When should we remind you?</p>
                                </div>
                                <input type="time" id="reminderTime" value="<?php echo htmlspecialchars($user['reminder_time'] ?? '21:00'); ?>" style="width: auto; padding: 8px 12px;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="danger-zone">
                        <h3>Danger Zone</h3>
                        <p>Once you delete your account, there is no going back. Please be certain.</p>
                        <button class="btn btn-danger" onclick="confirmDelete()">
                            <i class="fas fa-trash"></i>
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <div class="modal" id="deleteModal">
        <div class="modal-header">
            <h3>Delete Account</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 20px;">Are you sure you want to delete your account? This action cannot be undone and all your mood data will be permanently lost.</p>
            <div class="btn-group">
                <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn btn-danger" onclick="deleteAccount()">Delete Account</button>
            </div>
        </div>
    </div>
    <div class="overlay" id="overlay" onclick="closeModal()"></div>
    
    <div class="toast-container" id="toastContainer"></div>
    
    <script src="assets/js/main.js"></script>
    <script>
        (function() {
            const config = { enabled: <?php echo (int)($user['reminder_enabled'] ?? 0); ?>, time: '<?php echo htmlspecialchars($user['reminder_time'] ?? '21:00'); ?>', lastShown: null };
            if (!storage.get('moodtrail_reminder')?.time) {
                saveReminderConfig(config);
            }
        })();
        
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update_profile');
            
            try {
                const response = await fetch('api/user.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Profile updated successfully!', 'success');
                } else {
                    showToast(result.message || 'Failed to update profile', 'error');
                }
            } catch (error) {
                showToast('An error occurred. Please try again.', 'error');
            }
        });
        
        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                showToast('Passwords do not match', 'error');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('action', 'change_password');
            
            try {
                const response = await fetch('api/user.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Password changed successfully!', 'success');
                    this.reset();
                } else {
                    showToast(result.message || 'Failed to change password', 'error');
                }
            } catch (error) {
                showToast('An error occurred. Please try again.', 'error');
            }
        });
        
        document.getElementById('reminderToggle').addEventListener('change', async function() {
            const formData = new FormData();
            formData.append('action', 'update_reminders');
            formData.append('enabled', this.checked ? 1 : 0);
            formData.append('time', document.getElementById('reminderTime').value);
            
            try {
                const response = await fetch('api/user.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const config = { enabled: document.getElementById('reminderToggle').checked ? 1 : 0, time: document.getElementById('reminderTime').value, lastShown: null };
                    saveReminderConfig(config);
                    showToast('Reminder settings updated!', 'success');
                } else {
                    showToast('Failed to update settings', 'error');
                }
            } catch (error) {
                showToast('An error occurred.', 'error');
            }
        });
        
        document.getElementById('reminderTime').addEventListener('change', async function() {
            const enabled = document.getElementById('reminderToggle').checked ? 1 : 0;
            const formData = new FormData();
            formData.append('action', 'update_reminders');
            formData.append('enabled', enabled);
            formData.append('time', this.value);
            
            try {
                const response = await fetch('api/user.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    const config = { enabled, time: this.value, lastShown: null };
                    saveReminderConfig(config);
                    showToast('Reminder time saved!', 'success');
                }
            } catch (error) {
                console.error('Error saving reminder time');
            }
        });
        
        function confirmDelete() {
            document.getElementById('overlay').classList.add('active');
            document.getElementById('deleteModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('overlay').classList.remove('active');
            document.getElementById('deleteModal').classList.remove('active');
        }
        
        // Avatar upload
        document.getElementById('avatarInput').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('action', 'update_avatar');
            formData.append('avatar', file);

            try {
                const response = await fetch('api/user.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const img = document.createElement('img');
                    img.src = 'assets/uploads/' + result.avatar + '?t=' + Date.now();
                    img.alt = 'Avatar';
                    const avatarDiv = document.getElementById('profileAvatar');
                    const existingImg = avatarDiv.querySelector('img');
                    if (existingImg) {
                        existingImg.src = img.src;
                    } else {
                        avatarDiv.innerHTML = '';
                        avatarDiv.appendChild(img);
                    }
                    document.getElementById('removeAvatarBtn').style.display = 'flex';
                    showToast('Avatar updated!', 'success');
                } else {
                    showToast(result.message || 'Failed to update avatar', 'error');
                }
            } catch (error) {
                showToast('An error occurred', 'error');
            }
        });

        document.getElementById('removeAvatarBtn').addEventListener('click', async function() {
            const formData = new FormData();
            formData.append('action', 'delete_avatar');

            try {
                const response = await fetch('api/user.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const avatarDiv = document.getElementById('profileAvatar');
                    avatarDiv.innerHTML = '<?php echo strtoupper(substr(($user['name'] ?? 'U'), 0, 1)); ?>';
                    document.getElementById('removeAvatarBtn').style.display = 'none';
                    showToast('Avatar removed', 'success');
                } else {
                    showToast('Failed to remove avatar', 'error');
                }
            } catch (error) {
                showToast('An error occurred', 'error');
            }
        });

        async function deleteAccount() {
            const formData = new FormData();
            formData.append('action', 'delete_account');
            
            try {
                const response = await fetch('api/user.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Account deleted. Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    showToast(result.message || 'Failed to delete account', 'error');
                }
            } catch (error) {
                showToast('An error occurred. Please try again.', 'error');
            }
            
            closeModal();
        }
    </script>
</body>
</html>