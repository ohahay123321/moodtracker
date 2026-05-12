<?php
session_start();
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
$db = getDB();
$user_id = $_SESSION['user_id'];

$range = $_GET['range'] ?? '30';
$days = (int)$range;

$custom_from = $_GET['from'] ?? '';
$custom_to = $_GET['to'] ?? '';

if ($custom_from && $custom_to) {
    $start_date = $custom_from;
    $end_date = $custom_to;
} else {
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime("-{$days} days"));
}

$stmt = $db->prepare("SELECT * FROM mood_entries WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ? ORDER BY created_at ASC");
$stmt->execute([$user_id, $start_date, $end_date]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($entries);

$stmt = $db->prepare("SELECT mood, COUNT(*) as count FROM mood_entries WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ? GROUP BY mood ORDER BY count DESC");
$stmt->execute([$user_id, $start_date, $end_date]);
$mood_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $db->prepare("SELECT AVG(intensity) as avg FROM mood_entries WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$user_id, $start_date, $end_date]);
$avg_intensity = round($stmt->fetch()['avg'] ?? 0, 1);

$stmt = $db->prepare("SELECT COUNT(DISTINCT DATE(created_at)) as days FROM mood_entries WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$user_id, $start_date, $end_date]);
$active_days = $stmt->fetch()['days'] ?? 0;

if ($days > 0) {
    $consistency = $active_days > 0 ? round(($active_days / min($days, $active_days + 30)) * 100) : 0;
} else {
    $consistency = 0;
}

$stmt = $db->prepare("SELECT MAX(intensity) as max_intensity, DATE(created_at) as date FROM mood_entries WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY max_intensity DESC LIMIT 1");
$stmt->execute([$user_id, $start_date, $end_date]);
$highest_day = $stmt->fetch();

$stmt = $db->prepare("SELECT MIN(intensity) as min_intensity, DATE(created_at) as date FROM mood_entries WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY min_intensity ASC LIMIT 1");
$stmt->execute([$user_id, $start_date, $end_date]);
$lowest_day = $stmt->fetch();

$positive_moods = ['Happy', 'Calm', 'Excited', 'Loved', 'Grateful', 'Hopeful', 'Confident', 'Peaceful', 'Joyful', 'Energetic', 'Inspired'];
$negative_moods = ['Sad', 'Angry', 'Anxious', 'Tired', 'Confused', 'Stressed', 'Bored', 'Frustrated', 'Disappointed', 'Embarrassed'];

$positive_count = 0;
$negative_count = 0;
$neutral_count = 0;

foreach ($entries as $e) {
    if (in_array($e['mood'], $positive_moods)) $positive_count++;
    elseif (in_array($e['mood'], $negative_moods)) $negative_count++;
    else $neutral_count++;
}

$positive_pct = $total > 0 ? round(($positive_count / $total) * 100) : 0;
$negative_pct = $total > 0 ? round(($negative_count / $total) * 100) : 0;
$neutral_pct = $total > 0 ? round(($neutral_count / $total) * 100) : 0;

$streak = 0;
$check_date = date('Y-m-d');
while (true) {
    $stmt = $db->prepare("SELECT id FROM mood_entries WHERE user_id = ? AND DATE(created_at) = ? LIMIT 1");
    $stmt->execute([$user_id, $check_date]);
    if ($stmt->fetch()) {
        $streak++;
        $check_date = date('Y-m-d', strtotime($check_date . ' -1 day'));
    } else {
        break;
    }
}

$daily_data = [];
$period_days = $custom_from && $custom_to ? abs((strtotime($end_date) - strtotime($start_date)) / 86400) + 1 : $days;
for ($i = (int)$period_days - 1; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days", strtotime($end_date)));
    $daily_data[$date] = ['count' => 0, 'total_intensity' => 0, 'moods' => []];
}

foreach ($entries as $e) {
    $date = date('Y-m-d', strtotime($e['created_at']));
    if (isset($daily_data[$date])) {
        $daily_data[$date]['count']++;
        $daily_data[$date]['total_intensity'] += $e['intensity'];
        $daily_data[$date]['moods'][] = $e['mood'];
    }
}

foreach ($daily_data as $date => &$data) {
    $data['avg_intensity'] = $data['count'] > 0 ? round($data['total_intensity'] / $data['count'], 1) : 0;
    $data['mood'] = !empty($data['moods']) ? array_keys(array_count_values($data['moods']))[0] : null;
}
unset($data);

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

$mood_colors = [
    'Happy' => '#FFD93D',
    'Sad' => '#74B9FF',
    'Angry' => '#FF7675',
    'Calm' => '#81ECEC',
    'Anxious' => '#DDA0DD',
    'Excited' => '#FF9FF3',
    'Tired' => '#B2BEC3',
    'Loved' => '#FD79A8',
    'Grateful' => '#D4A843',
    'Hopeful' => '#FFB347',
    'Confident' => '#45B7D1',
    'Peaceful' => '#2ECC71',
    'Confused' => '#95A5A6',
    'Stressed' => '#E74C3C',
    'Bored' => '#BDC3C7',
    'Frustrated' => '#E67E22',
    'Joyful' => '#FFEAA7',
    'Surprised' => '#FF9FF3',
    'Nostalgic' => '#A29BFE',
    'Energetic' => '#FF6348',
    'Disappointed' => '#636E72',
    'Embarrassed' => '#FF6B81',
    'Inspired' => '#7BED9F',
    'Curious' => '#FDCB6E'
];

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=moodtrail-report.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($output, ['Date', 'Mood', 'Intensity', 'Notes', 'Time']);
    foreach ($entries as $e) {
        fputcsv($output, [
            date('Y-m-d', strtotime($e['created_at'])),
            $e['mood'],
            $e['intensity'],
            $e['notes'],
            date('h:i A', strtotime($e['created_at']))
        ]);
    }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - MoodTrail</title>
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
                    <a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
                    <a href="add-mood.php" class="nav-link"><i class="fas fa-plus-circle"></i><span>Log Mood</span></a>
                    <a href="history.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>History</span></a>
                    <a href="reports.php" class="nav-link active"><i class="fas fa-file-alt"></i><span>Reports</span></a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Profile</span></a>
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
                    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                    <h1 class="page-title">Reports</h1>
                </div>
                <div class="header-right">
                    <div class="time-range-selector">
                        <a href="?range=7" class="time-range-btn <?php echo $range == '7' && !$custom_from ? 'active' : ''; ?>">7D</a>
                        <a href="?range=30" class="time-range-btn <?php echo $range == '30' && !$custom_from ? 'active' : ''; ?>">30D</a>
                        <a href="?range=90" class="time-range-btn <?php echo $range == '90' && !$custom_from ? 'active' : ''; ?>">90D</a>
                        <a href="?range=365" class="time-range-btn <?php echo $range == '365' && !$custom_from ? 'active' : ''; ?>">1Y</a>
                        <button class="time-range-btn" onclick="document.getElementById('customRange').classList.toggle('hidden')">Custom</button>
                    </div>
                    <a href="?export=csv&range=<?php echo $range . ($custom_from ? "&from=$custom_from&to=$custom_to" : ''); ?>" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;">
                        <i class="fas fa-download"></i> CSV
                    </a>
                    <button class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </header>

            <div class="page-content">
                <div class="card" style="margin-bottom: 24px; <?php echo $custom_from ? '' : 'display:none'; ?>" id="customRange">
                    <form method="GET" style="display: flex; gap: 16px; align-items: end; flex-wrap: wrap;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-size: 13px; margin-bottom: 4px;">From</label>
                            <input type="date" name="from" value="<?php echo $custom_from ?: $start_date; ?>">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-size: 13px; margin-bottom: 4px;">To</label>
                            <input type="date" name="to" value="<?php echo $custom_to ?: $end_date; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary" style="padding: 8px 20px;">Apply</button>
                    </form>
                </div>

                <?php if (empty($entries)): ?>
                <div class="card">
                    <div class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <h3>No data in this period</h3>
                        <p>Start logging your mood to see reports</p>
                        <a href="add-mood.php" class="btn btn-primary">Log Your First Mood</a>
                    </div>
                </div>
                <?php else: ?>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary"><i class="fas fa-database"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $total; ?></h3>
                            <p>Total Entries</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon secondary"><i class="fas fa-tachometer-alt"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $avg_intensity; ?>/10</h3>
                            <p>Avg Intensity</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon success"><i class="fas fa-smile"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $positive_pct; ?>%</h3>
                            <p>Positive</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon accent"><i class="fas fa-frown"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $negative_pct; ?>%</h3>
                            <p>Negative</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon info"><i class="fas fa-calendar-check"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $active_days; ?> days</h3>
                            <p>Active Days</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon warning"><i class="fas fa-fire"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $streak; ?> days</h3>
                            <p>Current Streak</p>
                        </div>
                    </div>
                </div>

                <div class="grid-2" style="margin-bottom: 24px;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie"></i> Mood Distribution</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="distChart"></canvas>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-line"></i> Intensity Trend</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid-2" style="margin-bottom: 24px;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-trophy"></i> Mood Breakdown</h3>
                        </div>
                        <div style="padding: 8px 0;">
                            <?php
                            $mood_sorted = $mood_counts;
                            arsort($mood_sorted);
                            $mood_total = array_sum($mood_sorted);
                            foreach ($mood_sorted as $mood => $count):
                                $pct = $mood_total > 0 ? round(($count / $mood_total) * 100) : 0;
                            ?>
                            <div style="margin-bottom: 14px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                    <span style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 20px;"><?php echo $mood_icons[$mood] ?? ''; ?></span>
                                        <strong><?php echo $mood; ?></strong>
                                    </span>
                                    <span style="color: var(--text-secondary); font-size: 14px;"><?php echo $count; ?> (<?php echo $pct; ?>%)</span>
                                </div>
                                <div style="height: 10px; background: #E2E8F0; border-radius: 5px; overflow: hidden;">
                                    <div style="height: 100%; width: <?php echo $pct; ?>%; background: <?php echo $mood_colors[$mood] ?? '#6C63FF'; ?>; border-radius: 5px; transition: width 0.6s ease;"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-star"></i> Highlights</h3>
                        </div>
                        <div style="padding: 8px 0;">
                            <div class="insight-item">
                                <div class="insight-icon" style="background: rgba(104, 211, 145, 0.15); color: #68D391;">📈</div>
                                <div class="insight-content">
                                    <h4>Highest Intensity Day</h4>
                                    <p><?php echo $highest_day ? date('M d, Y', strtotime($highest_day['date'])) . ' — Intensity: ' . $highest_day['max_intensity'] . '/10' : 'No data'; ?></p>
                                </div>
                            </div>
                            <div class="insight-item">
                                <div class="insight-icon" style="background: rgba(246, 173, 85, 0.15); color: #F6AD55;">📉</div>
                                <div class="insight-content">
                                    <h4>Lowest Intensity Day</h4>
                                    <p><?php echo $lowest_day ? date('M d, Y', strtotime($lowest_day['date'])) . ' — Intensity: ' . $lowest_day['min_intensity'] . '/10' : 'No data'; ?></p>
                                </div>
                            </div>
                            <div class="insight-item">
                                <div class="insight-icon" style="background: rgba(108, 99, 255, 0.15); color: #6C63FF;">📋</div>
                                <div class="insight-content">
                                    <h4>Period Summary</h4>
                                    <p><?php echo date('M d', strtotime($start_date)); ?> – <?php echo date('M d, Y', strtotime($end_date)); ?> &middot; <?php echo $total; ?> entries over <?php echo $active_days; ?> days</p>
                                </div>
                            </div>
                            <div class="insight-item">
                                <div class="insight-icon" style="background: rgba(255, 107, 157, 0.15); color: #FF6B9D;">🔥</div>
                                <div class="insight-content">
                                    <h4>Current Streak</h4>
                                    <p><?php echo $streak; ?> consecutive day<?php echo $streak !== 1 ? 's' : ''; ?> of logging mood</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list"></i> Daily Log</h3>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Mood</th>
                                    <th>Intensity</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($entries) as $e): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($e['created_at'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($e['created_at'])); ?></td>
                                    <td><?php echo ($mood_icons[$e['mood']] ?? '') . ' ' . htmlspecialchars($e['mood']); ?></td>
                                    <td><?php echo $e['intensity']; ?>/10</td>
                                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($e['notes'] ?? ''); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div class="overlay" id="overlay"></div>
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        const moodColors = <?php echo json_encode($mood_colors); ?>;
        const moodIcons = <?php echo json_encode($mood_icons); ?>;

        <?php if (!empty($entries)): ?>
        const moodCounts = <?php echo json_encode($mood_counts); ?>;
        const distCtx = document.getElementById('distChart').getContext('2d');
        new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(moodCounts),
                datasets: [{
                    data: Object.values(moodCounts),
                    backgroundColor: Object.keys(moodCounts).map(m => moodColors[m] || '#6C63FF'),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 16 } } },
                cutout: '60%'
            }
        });

        const dailyData = <?php echo json_encode($daily_data); ?>;
        const labels = Object.keys(dailyData).map(d => {
            const date = new Date(d + 'T00:00:00');
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        const intensityData = Object.values(dailyData).map(d => d.avg_intensity);
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Avg Intensity',
                    data: intensityData,
                    borderColor: '#6C63FF',
                    backgroundColor: 'rgba(108, 99, 255, 0.1)',
                    fill: true, tension: 0.4,
                    pointBackgroundColor: '#6C63FF',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 10, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
