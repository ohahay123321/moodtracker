<?php
session_start();
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
$db = getDB();

$current_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

if ($current_month < 1) { $current_month = 12; $current_year--; }
if ($current_month > 12) { $current_month = 1; $current_year++; }

$month_name = date('F', mktime(0, 0, 0, $current_month, 10));

$start_date = date('Y-m-01', mktime(0, 0, 0, $current_month, 1, $current_year));
$end_date = date('Y-m-t', mktime(0, 0, 0, $current_month, 1, $current_year));

$stmt = $db->prepare("SELECT * FROM mood_entries WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
$month_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$entries_by_date = [];
foreach ($month_entries as $entry) {
    $date = date('Y-m-d', strtotime($entry['created_at']));
    $entries_by_date[$date][] = $entry;
}

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

$first_day = date('w', mktime(0, 0, 0, $current_month, 1, $current_year));
$days_in_month = date('t', mktime(0, 0, 0, $current_month, 1, $current_year));
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - MoodTrail</title>
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
                    <a href="history.php" class="nav-link active">
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
                    <h1 class="page-title">Mood History</h1>
                </div>
                <div class="header-right">
                    <button class="header-btn">
                        <i class="fas fa-bell"></i>
                    </button>
                </div>
            </header>
            
            <div class="page-content">
                <div class="grid-2">
                    <div class="card">
                        <div class="calendar">
                            <div class="calendar-header">
                                <div class="calendar-nav">
                                    <button onclick="changeMonth(-1)">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <span class="calendar-title"><?php echo $month_name . ' ' . $current_year; ?></span>
                                    <button onclick="changeMonth(1)">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="calendar-grid">
                                <div class="calendar-day-header">Sun</div>
                                <div class="calendar-day-header">Mon</div>
                                <div class="calendar-day-header">Tue</div>
                                <div class="calendar-day-header">Wed</div>
                                <div class="calendar-day-header">Thu</div>
                                <div class="calendar-day-header">Fri</div>
                                <div class="calendar-day-header">Sat</div>
                                
                                <?php
                                $mood_colors = [
                                    'Happy' => 'var(--mood-happy)',
                                    'Sad' => 'var(--mood-sad)',
                                    'Angry' => 'var(--mood-angry)',
                                    'Calm' => 'var(--mood-calm)',
                                    'Anxious' => 'var(--mood-anxious)',
                                    'Excited' => 'var(--mood-excited)',
                                    'Tired' => 'var(--mood-tired)',
                                    'Loved' => 'var(--mood-loved)',
                                    'Grateful' => 'var(--mood-grateful)',
                                    'Hopeful' => 'var(--mood-hopeful)',
                                    'Confident' => 'var(--mood-confident)',
                                    'Peaceful' => 'var(--mood-peaceful)',
                                    'Confused' => 'var(--mood-confused)',
                                    'Stressed' => 'var(--mood-stressed)',
                                    'Bored' => 'var(--mood-bored)',
                                    'Frustrated' => 'var(--mood-frustrated)',
                                    'Joyful' => 'var(--mood-joyful)',
                                    'Surprised' => 'var(--mood-surprised)',
                                    'Nostalgic' => 'var(--mood-nostalgic)',
                                    'Energetic' => 'var(--mood-energetic)',
                                    'Disappointed' => 'var(--mood-disappointed)',
                                    'Embarrassed' => 'var(--mood-embarrassed)',
                                    'Inspired' => 'var(--mood-inspired)',
                                    'Curious' => 'var(--mood-curious)'
                                ];
                                
                                for ($i = 0; $i < $first_day; $i++) {
                                    echo '<div class="calendar-day empty"></div>';
                                }
                                
                                for ($day = 1; $day <= $days_in_month; $day++) {
                                    $date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
                                    $is_today = $date === $today;
                                    $day_entries = $entries_by_date[$date] ?? [];
                                    $has_entry = !empty($day_entries);
                                    
                                    $classes = ['calendar-day'];
                                    if ($is_today) $classes[] = 'today';
                                    if ($has_entry) $classes[] = 'has-entry';
                                    
                                    echo '<div class="' . implode(' ', $classes) . '" onclick="' . ($has_entry ? "showDayDetails('$date')" : '') . '">';
                                    echo $day;
                                    if ($has_entry) {
                                        $display_count = min(count($day_entries), 3);
                                        echo '<div class="mood-indicators">';
                                        for ($i = 0; $i < $display_count; $i++) {
                                            $mood = $day_entries[$i]['mood'];
                                            echo '<div class="mood-indicator" style="background: ' . ($mood_colors[$mood] ?? 'var(--primary)') . '"></div>';
                                        }
                                        if (count($day_entries) > 3) {
                                            echo '<div class="mood-indicator mood-indicator-more">+' . (count($day_entries) - 3) . '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center;">
                            <?php foreach ($mood_icons as $name => $emoji): ?>
                            <div style="display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-secondary);">
                                <span><?php echo $emoji; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list"></i>
                                Entries This Month
                            </h3>
                        </div>
                        
                        <?php if (empty($month_entries)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📝</div>
                            <h3>No entries this month</h3>
                            <p>Start logging your mood to see history here</p>
                            <a href="add-mood.php" class="btn btn-primary">Log Mood</a>
                        </div>
                        <?php else: ?>
                        <div class="mood-history-list">
                            <?php foreach ($month_entries as $entry): ?>
                            <div class="mood-history-item">
                                <div class="mood-history-emoji"><?php echo $mood_icons[$entry['mood']] ?? ''; ?></div>
                                <div class="mood-history-details">
                                    <div class="mood-history-mood"><?php echo htmlspecialchars($entry['mood']); ?></div>
                                    <?php if ($entry['notes']): ?>
                                    <div class="mood-history-note"><?php echo htmlspecialchars($entry['notes']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mood-history-meta">
                                    <div class="mood-history-time"><?php echo date('M d, h:i A', strtotime($entry['created_at'])); ?></div>
                                    <div class="mood-history-intensity">Intensity: <?php echo $entry['intensity']; ?>/10</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <div class="modal" id="dayModal">
        <div class="modal-header">
            <h3 id="modalTitle">Day Details</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
        </div>
    </div>
    <div class="overlay" id="overlay" onclick="closeModal()"></div>
    
    <div class="toast-container" id="toastContainer"></div>
    
    <script>
        function changeMonth(delta) {
            const url = new URL(window.location.href);
            let month = parseInt(url.searchParams.get('month')) || <?php echo $current_month; ?>;
            let year = parseInt(url.searchParams.get('year')) || <?php echo $current_year; ?>;
            
            month += delta;
            if (month < 1) { month = 12; year--; }
            if (month > 12) { month = 1; year++; }
            
            url.searchParams.set('month', month);
            url.searchParams.set('year', year);
            window.location.href = url;
        }
        
        const entriesByDate = <?php echo json_encode($entries_by_date); ?>;
        const moodIcons = <?php echo json_encode($mood_icons); ?>;
        
        function escHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
        
        function showDayDetails(date) {
            const entries = entriesByDate[date];
            if (!entries || !entries.length) return;
            
            const plural = entries.length > 1 ? ` (${entries.length} entries)` : '';
            document.getElementById('modalTitle').textContent = new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + plural;
            
            document.getElementById('modalBody').innerHTML = entries.map((entry, i) => `
                <div style="text-align: center; padding: 16px 0; ${i > 0 ? 'border-top: 1px solid #E2E8F0;' : ''}">
                    <div style="font-size: 48px; margin-bottom: 8px;">${moodIcons[entry.mood] || ''}</div>
                    <h3 style="margin-bottom: 4px;">${entry.mood}</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 4px;">Intensity: ${entry.intensity}/10</p>
                    <p style="color: var(--text-secondary); font-size: 13px;">${new Date(entry.created_at).toLocaleTimeString()}</p>
                    ${entry.notes ? `<div style="margin-top: 12px; padding: 12px; background: var(--bg); border-radius: var(--radius-sm); text-align: left; font-size: 14px;">
                        <strong>Notes:</strong><br>${escHtml(entry.notes)}
                    </div>` : ''}
                </div>
            `).join('');
            
            document.getElementById('overlay').classList.add('active');
            document.getElementById('dayModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('overlay').classList.remove('active');
            document.getElementById('dayModal').classList.remove('active');
        }
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>