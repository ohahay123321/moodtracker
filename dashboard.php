<?php
session_start();
require_once 'config.php';
requireLogin();

$user = getCurrentUser();

$db = getDB();

$stmt = $db->prepare("SELECT * FROM mood_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recent_moods = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT COUNT(*) as total FROM mood_entries WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_entries = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT mood, COUNT(*) as count FROM mood_entries WHERE user_id = ? GROUP BY mood ORDER BY count DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$most_frequent = $stmt->fetch();

$stmt = $db->prepare("SELECT AVG(intensity) as avg_intensity FROM mood_entries WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$avg_intensity = round($stmt->fetch()['avg_intensity'] ?? 0, 1);

$today = date('Y-m-d');
$stmt = $db->prepare("SELECT * FROM mood_entries WHERE user_id = ? AND DATE(created_at) = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id'], $today]);
$today_mood = $stmt->fetch();

$mood_icons = [
    'Happy' => '😊',
    'Sad' => '😢',
    'Angry' => '😠',
    'Calm' => '😌',
    'Anxious' => '😰',
    'Excited' => '🤩',
    'Tired' => '😴',
    'Loved' => '🥰',
    'Grateful' => '🙏',
    'Hopeful' => '🌟',
    'Confident' => '💪',
    'Peaceful' => '🕊️',
    'Confused' => '😕',
    'Stressed' => '😫',
    'Bored' => '😑',
    'Frustrated' => '😤',
    'Joyful' => '😄',
    'Surprised' => '😮',
    'Nostalgic' => '🥲',
    'Energetic' => '⚡',
    'Disappointed' => '😞',
    'Embarrassed' => '😳',
    'Inspired' => '✨',
    'Curious' => '🤔'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MoodTrail</title>
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
                    <a href="dashboard.php" class="nav-link active">
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
                    <h1 class="page-title">Dashboard</h1>
                </div>
                <div class="header-right">
                    <button class="header-btn" id="bellBtn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-dot"></span>
                    </button>
                </div>
            </header>
            
            <div class="page-content">
                <div class="welcome-banner">
                    <h2>Welcome back, <?php echo htmlspecialchars(explode(' ', ($user['name'] ?? 'User'))[0]); ?>!</h2>
                    <p>How are you feeling today?</p>
                    <div class="quick-actions">
                        <a href="add-mood.php" class="quick-action-btn">
                            <i class="fas fa-plus"></i>
                            <span>Log Mood</span>
                        </a>
                    </div>
                </div>
                
                <?php if (!$today_mood): ?>
                <div class="card" style="margin-bottom: 32px; border-left: 4px solid var(--primary);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h3 style="margin-bottom: 4px;">📝 You haven't logged your mood today</h3>
                            <p style="color: var(--text-secondary); font-size: 14px;">Take a moment to track how you're feeling</p>
                        </div>
                        <a href="add-mood.php" class="btn btn-primary">Log Now</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="card" style="margin-bottom: 32px; border-left: 4px solid var(--success);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h3 style="margin-bottom: 4px;">Today's Mood: <?php echo ($mood_icons[$today_mood['mood']] ?? '') . ' ' . $today_mood['mood']; ?></h3>
                            <p style="color: var(--text-secondary); font-size: 14px;">Intensity: <?php echo $today_mood['intensity']; ?>/10</p>
                        </div>
                        <a href="add-mood.php" class="btn btn-secondary">Update</a>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_entries; ?></h3>
                            <p>Total Entries</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon secondary">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $most_frequent ? ($mood_icons[$most_frequent['mood']] ?? '—') : '—'; ?></h3>
                            <p>Most Frequent Mood</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon accent">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $avg_intensity; ?>/10</h3>
                            <p>Avg. Intensity</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon info">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $today_mood ? 'Yes' : 'No'; ?></h3>
                            <p>Logged Today</p>
                        </div>
                    </div>
                </div>
                
                <div class="grid-2">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history"></i>
                                Recent Entries
                            </h3>
                            <a href="history.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;">View All</a>
                        </div>
                        
                        <?php if (empty($recent_moods)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📝</div>
                            <h3>No entries yet</h3>
                            <p>Start tracking your mood to see your history here</p>
                            <a href="add-mood.php" class="btn btn-primary">Log Your First Mood</a>
                        </div>
                        <?php else: ?>
                        <div class="mood-history-list">
                            <?php foreach ($recent_moods as $entry): ?>
                            <div class="mood-history-item">
                                <div class="mood-history-emoji"><?php echo $mood_icons[$entry['mood']] ?? ''; ?></div>
                                <div class="mood-history-details">
                                    <div class="mood-history-mood"><?php echo htmlspecialchars($entry['mood']); ?></div>
                                    <?php if ($entry['notes']): ?>
                                    <div class="mood-history-note"><?php echo htmlspecialchars($entry['notes']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mood-history-meta">
                                    <div class="mood-history-time"><?php echo date('h:i A', strtotime($entry['created_at'])); ?></div>
                                    <div class="mood-history-intensity">Intensity: <?php echo $entry['intensity']; ?>/10</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card insights-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb"></i>
                                Personalized Insights
                            </h3>
                        </div>
                        
                        <?php
                        $insights = getInsights($_SESSION['user_id'], $db);
                        if (empty($insights)):
                        ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🧠</div>
                            <h3>Not enough data</h3>
                            <p>Log more moods to receive personalized insights</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($insights as $insight): ?>
                        <div class="insight-item">
                            <div class="insight-icon" style="background: <?php echo $insight['color']; ?>20; color: <?php echo $insight['color']; ?>;">
                                <?php echo $insight['icon']; ?>
                            </div>
                            <div class="insight-content">
                                <h4><?php echo htmlspecialchars($insight['title']); ?></h4>
                                <p><?php echo htmlspecialchars($insight['message']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <div class="overlay" id="overlay"></div>
    <div class="toast-container" id="toastContainer"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
function getInsights($user_id, $db) {
    $insights = [];
    
    $stmt = $db->prepare("SELECT mood, COUNT(*) as count FROM mood_entries WHERE user_id = ? GROUP BY mood ORDER BY count DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $most_common = $stmt->fetch();
    
    if ($most_common) {
        $tips = [
            'Happy' => ['icon' => '😊', 'color' => '#FFD93D', 'title' => 'You\'re doing great!', 'message' => 'Your positive mood is shining. Keep doing what makes you happy!'],
            'Sad' => ['icon' => '😢', 'color' => '#74B9FF', 'title' => 'Take care of yourself', 'message' => 'Consider reaching out to a friend or practicing some self-care activities.'],
            'Angry' => ['icon' => '😠', 'color' => '#FF7675', 'title' => 'Time to breathe', 'message' => 'Try some deep breathing exercises or journal about what\'s triggering these feelings.'],
            'Calm' => ['icon' => '😌', 'color' => '#81ECEC', 'title' => 'Embrace the peace', 'message' => 'Your calm state is wonderful. Use this time for mindful activities.'],
            'Anxious' => ['icon' => '😰', 'color' => '#DDA0DD', 'title' => 'Stay grounded', 'message' => 'Try the 5-4-3-2-1 grounding technique: name 5 things you see, 4 you feel, 3 you hear, 2 you smell, 1 you taste.'],
            'Excited' => ['icon' => '🤩', 'color' => '#FF9FF3', 'title' => 'Channel that energy!', 'message' => 'Great vibes! Channel this energy into something productive you\'ve been putting off.'],
            'Tired' => ['icon' => '😴', 'color' => '#B2BEC3', 'title' => 'Rest is important', 'message' => 'Make sure you\'re getting enough sleep. Consider taking a short nap or just relaxing.'],
            'Loved' => ['icon' => '🥰', 'color' => '#FD79A8', 'title' => 'Share the love', 'message' => 'You\'re feeling loved! Consider reaching out to someone special in your life.'],
            'Grateful' => ['icon' => '🙏', 'color' => '#D4A843', 'title' => 'Count your blessings', 'message' => 'Gratitude is powerful. Try writing down three things you\'re grateful for today.'],
            'Hopeful' => ['icon' => '🌟', 'color' => '#FFB347', 'title' => 'Look ahead', 'message' => 'Hope is a beautiful thing. Set a small goal for tomorrow and take a step toward it.'],
            'Confident' => ['icon' => '💪', 'color' => '#45B7D1', 'title' => 'You got this!', 'message' => 'Channel your confidence into tackling something you\'ve been putting off.'],
            'Peaceful' => ['icon' => '🕊️', 'color' => '#2ECC71', 'title' => 'Enjoy the calm', 'message' => 'This peaceful state is great for reflection. Maybe try some meditation or time in nature.'],
            'Confused' => ['icon' => '😕', 'color' => '#95A5A6', 'title' => 'It\'s okay not to know', 'message' => 'Take a step back and give yourself time to process. Clarity will come.'],
            'Stressed' => ['icon' => '😫', 'color' => '#E74C3C', 'title' => 'Take a break', 'message' => 'Step away for a moment. Deep breaths and a short walk can work wonders.'],
            'Bored' => ['icon' => '😑', 'color' => '#BDC3C7', 'title' => 'Find something new', 'message' => 'Try picking up a new hobby, reading something different, or changing your routine.'],
            'Frustrated' => ['icon' => '😤', 'color' => '#E67E22', 'title' => 'Pause and refocus', 'message' => 'Take a few deep breaths and try to identify the root cause of the frustration.'],
            'Joyful' => ['icon' => '😄', 'color' => '#FFEAA7', 'title' => 'Spread the joy!', 'message' => 'Your joyful energy is contagious. Share it with someone today!'],
            'Surprised' => ['icon' => '😮', 'color' => '#FF9FF3', 'title' => 'Embrace the unexpected', 'message' => 'Surprises keep life interesting. Take a moment to appreciate the novelty.'],
            'Nostalgic' => ['icon' => '🥲', 'color' => '#A29BFE', 'title' => 'Cherish the memories', 'message' => 'Nostalgia can be comforting. Reach out to someone from those fond memories.'],
            'Energetic' => ['icon' => '⚡', 'color' => '#FF6348', 'title' => 'Use that energy!', 'message' => 'You\'re full of energy! Great time to exercise, create, or tackle a big project.'],
            'Disappointed' => ['icon' => '😞', 'color' => '#636E72', 'title' => 'It\'s okay to feel this way', 'message' => 'Disappointment is valid. Give yourself space to process, then look forward.'],
            'Embarrassed' => ['icon' => '😳', 'color' => '#FF6B81', 'title' => 'We all have moments', 'message' => 'Everyone has awkward moments. Laugh it off and remember it\'s part of being human.'],
            'Inspired' => ['icon' => '✨', 'color' => '#7BED9F', 'title' => 'Follow the inspiration!', 'message' => 'Inspiration is a gift. Capture your ideas and act on them while the spark is alive.'],
            'Curious' => ['icon' => '🤔', 'color' => '#FDCB6E', 'title' => 'Keep exploring', 'message' => 'Curiosity leads to growth. Learn something new or dive deeper into a topic you love.']
        ];
        
        if (isset($tips[$most_common['mood']])) {
            $t = $tips[$most_common['mood']];
            $insights[] = [
                'icon' => $t['icon'],
                'color' => $t['color'],
                'title' => $t['title'],
                'message' => $t['message']
            ];
        }
    }
    
    $stmt = $db->prepare("SELECT AVG(intensity) as avg FROM mood_entries WHERE user_id = ? AND mood IN ('Happy', 'Excited', 'Calm', 'Loved', 'Grateful', 'Hopeful', 'Confident', 'Peaceful', 'Joyful', 'Energetic', 'Inspired')");
    $stmt->execute([$user_id]);
    $positive_avg = $stmt->fetch()['avg'] ?? 0;
    
    $stmt = $db->prepare("SELECT AVG(intensity) as avg FROM mood_entries WHERE user_id = ? AND mood IN ('Sad', 'Angry', 'Anxious', 'Tired', 'Confused', 'Stressed', 'Bored', 'Frustrated', 'Disappointed', 'Embarrassed')");
    $stmt->execute([$user_id]);
    $negative_avg = $stmt->fetch()['avg'] ?? 0;
    
    if ($positive_avg > $negative_avg && $positive_avg > 5) {
        $insights[] = [
            'icon' => '📈',
            'color' => '#68D391',
            'title' => 'Positive Trend',
            'message' => 'You\'ve been experiencing more positive emotions lately. Keep it up!'
        ];
    } elseif ($negative_avg > $positive_avg && $negative_avg > 5) {
        $insights[] = [
            'icon' => '💪',
            'color' => '#F6AD55',
            'title' => 'Stay Strong',
            'message' => 'It\'s been a challenging time. Remember, it\'s okay to seek support.'
        ];
    }
    
    return $insights;
}
?>