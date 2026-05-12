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
    <title>Log Mood - MoodTrail</title>
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
                    <a href="add-mood.php" class="nav-link active">
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
                    <a href="profile.php" class="nav-link">
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
                    <h1 class="page-title">Log Your Mood</h1>
                </div>
                <div class="header-right">
                    <button class="header-btn">
                        <i class="fas fa-bell"></i>
                    </button>
                </div>
            </header>
            
            <div class="page-content">
                <div style="max-width: 700px; margin: 0 auto;">
                    <div class="card">
                        <form id="moodForm">
                            <div class="form-group">
                                <label>How are you feeling?</label>
                                <div class="mood-selector" id="moodSelector">
                                    <div class="mood-option" data-mood="Happy">
                                        <span class="mood-emoji">😊</span>
                                        <span class="mood-label">Happy</span>
                                    </div>
                                    <div class="mood-option" data-mood="Sad">
                                        <span class="mood-emoji">😢</span>
                                        <span class="mood-label">Sad</span>
                                    </div>
                                    <div class="mood-option" data-mood="Angry">
                                        <span class="mood-emoji">😠</span>
                                        <span class="mood-label">Angry</span>
                                    </div>
                                    <div class="mood-option" data-mood="Calm">
                                        <span class="mood-emoji">😌</span>
                                        <span class="mood-label">Calm</span>
                                    </div>
                                    <div class="mood-option" data-mood="Anxious">
                                        <span class="mood-emoji">😰</span>
                                        <span class="mood-label">Anxious</span>
                                    </div>
                                    <div class="mood-option" data-mood="Excited">
                                        <span class="mood-emoji">🤩</span>
                                        <span class="mood-label">Excited</span>
                                    </div>
                                    <div class="mood-option" data-mood="Tired">
                                        <span class="mood-emoji">😴</span>
                                        <span class="mood-label">Tired</span>
                                    </div>
                                    <div class="mood-option" data-mood="Loved">
                                        <span class="mood-emoji">🥰</span>
                                        <span class="mood-label">Loved</span>
                                    </div>
                                    <div class="mood-option mood-more-btn" id="moreMoodsBtn">
                                        <span class="mood-emoji" style="font-size: 32px; font-weight: 300;">+</span>
                                        <span class="mood-label">More</span>
                                    </div>
                                </div>
                                <input type="hidden" id="selectedMood" name="mood" required>
                            </div>
                            
                            <div class="intensity-slider">
                                <div class="intensity-header">
                                    <label>Intensity Level</label>
                                    <span class="intensity-value" id="intensityValue">5</span>
                                </div>
                                <input type="range" id="intensity" name="intensity" min="1" max="10" value="5">
                                <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: var(--text-light);">
                                    <span>Low</span>
                                    <span>Medium</span>
                                    <span>High</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Notes (optional)</label>
                                <textarea id="notes" name="notes" placeholder="What's on your mind? How did you feel today? Any triggers or highlights?" maxlength="500"></textarea>
                                <small style="color: var(--text-light); font-size: 12px;"><span id="notesCount">0</span>/500 characters</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="moodDate">Date & Time</label>
                                <input type="datetime-local" id="moodDate" name="mood_date" value="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block" id="saveMoodBtn">
                                <i class="fas fa-save"></i>
                                <span>Save Mood Entry</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <div class="modal" id="extraMoodsModal">
        <div class="modal-header">
            <h3>More Feelings</h3>
            <button class="modal-close" onclick="closeExtraMoods()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="extra-moods-grid">
                <div class="mood-option" data-mood="Grateful" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">🙏</span>
                    <span class="mood-label">Grateful</span>
                </div>
                <div class="mood-option" data-mood="Hopeful" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">🌟</span>
                    <span class="mood-label">Hopeful</span>
                </div>
                <div class="mood-option" data-mood="Confident" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">💪</span>
                    <span class="mood-label">Confident</span>
                </div>
                <div class="mood-option" data-mood="Peaceful" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">🕊️</span>
                    <span class="mood-label">Peaceful</span>
                </div>
                <div class="mood-option" data-mood="Confused" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">😕</span>
                    <span class="mood-label">Confused</span>
                </div>
                <div class="mood-option" data-mood="Stressed" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">😫</span>
                    <span class="mood-label">Stressed</span>
                </div>
                <div class="mood-option" data-mood="Bored" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">😑</span>
                    <span class="mood-label">Bored</span>
                </div>
                <div class="mood-option" data-mood="Frustrated" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">😤</span>
                    <span class="mood-label">Frustrated</span>
                </div>
                <div class="mood-option" data-mood="Joyful" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">😄</span>
                    <span class="mood-label">Joyful</span>
                </div>
                <div class="mood-option" data-mood="Surprised" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">😮</span>
                    <span class="mood-label">Surprised</span>
                </div>
                <div class="mood-option" data-mood="Nostalgic" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">🥲</span>
                    <span class="mood-label">Nostalgic</span>
                </div>
                <div class="mood-option" data-mood="Energetic" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">⚡</span>
                    <span class="mood-label">Energetic</span>
                </div>
                <div class="mood-option" data-mood="Disappointed" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">😞</span>
                    <span class="mood-label">Disappointed</span>
                </div>
                <div class="mood-option" data-mood="Embarrassed" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">😳</span>
                    <span class="mood-label">Embarrassed</span>
                </div>
                <div class="mood-option" data-mood="Inspired" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">✨</span>
                    <span class="mood-label">Inspired</span>
                </div>
                <div class="mood-option" data-mood="Curious" onclick="selectExtraMood(this)">
                    <span class="mood-emoji">🤔</span>
                    <span class="mood-label">Curious</span>
                </div>
            </div>
        </div>
    </div>
    <div class="overlay" id="overlay"></div>
    <div class="toast-container" id="toastContainer"></div>
    
    <script src="assets/js/main.js"></script>
    <script>
        document.querySelectorAll('.mood-selector .mood-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.mood-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedMood').value = this.dataset.mood;
            });
        });
        
        document.getElementById('moreMoodsBtn').addEventListener('click', function() {
            document.getElementById('extraMoodsModal').classList.add('active');
            document.getElementById('overlay').classList.add('active');
        });
        
        function closeExtraMoods() {
            document.getElementById('extraMoodsModal').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
        }
        
        function selectExtraMood(el) {
            document.querySelectorAll('.mood-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('selectedMood').value = el.dataset.mood;
            closeExtraMoods();
        }
        
        document.getElementById('overlay').addEventListener('click', closeExtraMoods);
        
        const intensitySlider = document.getElementById('intensity');
        const intensityValue = document.getElementById('intensityValue');
        
        intensitySlider.addEventListener('input', function() {
            intensityValue.textContent = this.value;
        });
        
        const notesArea = document.getElementById('notes');
        const notesCount = document.getElementById('notesCount');
        
        notesArea.addEventListener('input', function() {
            notesCount.textContent = this.value.length;
        });
        
        document.getElementById('moodForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const mood = document.getElementById('selectedMood').value;
            const intensity = document.getElementById('intensity').value;
            const notes = document.getElementById('notes').value;
            const moodDate = document.getElementById('moodDate').value;
            
            if (!mood) {
                showToast('Please select a mood', 'error');
                return;
            }
            
            const saveBtn = document.getElementById('saveMoodBtn');
            const originalBtnContent = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const formData = new FormData();
            formData.append('action', 'add_mood');
            formData.append('mood', mood);
            formData.append('intensity', intensity);
            formData.append('notes', notes);
            formData.append('mood_date', moodDate);
            
            try {
                const response = await fetch('api/mood.php', {
                    method: 'POST',
                    body: formData
                });
                
                const text = await response.text();
                
                if (!response.ok || !text) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnContent;
                    showToast('Failed to save. Please try again.', 'error');
                    return;
                }
                
                const result = JSON.parse(text);
                
                if (result.success) {
                    showToast('Mood logged successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1500);
                } else {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnContent;
                    showToast(result.message || 'Failed to save mood', 'error');
                }
            } catch (error) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnContent;
                showToast('An error occurred. Please try again.', 'error');
            }
        });
    </script>
</body>
</html>