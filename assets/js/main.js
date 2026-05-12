// Main JavaScript for MoodTrail

// Toast notifications
function showToast(message, type = 'info', title = '') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    const titles = {
        success: 'Success',
        error: 'Error',
        warning: 'Warning',
        info: 'Notice'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${icons[type]}</span>
        <div class="toast-content">
            <div class="toast-title">${title || titles[type]}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (sidebar) sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            closeModals();
        }
    });
        
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'slideDown 0.3s ease reverse';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Close all modals
function closeModals() {
    document.querySelectorAll('.modal.active').forEach(modal => {
        modal.classList.remove('active');
    });
    document.getElementById('overlay')?.classList.remove('active');
}

// Format relative time
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    const intervals = [
        { label: 'year', seconds: 31536000 },
        { label: 'month', seconds: 2592000 },
        { label: 'week', seconds: 604800 },
        { label: 'day', seconds: 86400 },
        { label: 'hour', seconds: 3600 },
        { label: 'minute', seconds: 60 }
    ];
    
    for (const interval of intervals) {
        const count = Math.floor(seconds / interval.seconds);
        if (count >= 1) {
            return `${count} ${interval.label}${count > 1 ? 's' : ''} ago`;
        }
    }
    
    return 'Just now';
}

// Validate email
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Local storage helpers
const storage = {
    set(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {}
    },
    get(key, defaultValue = null) {
        try {
            const value = localStorage.getItem(key);
            return value ? JSON.parse(value) : defaultValue;
        } catch (e) {
            return defaultValue;
        }
    },
    remove(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {}
    }
};

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Reminder system
const REMINDER_STORAGE_KEY = 'moodtrail_reminder';

function getReminderConfig() {
    return storage.get(REMINDER_STORAGE_KEY, { enabled: false, time: '21:00', lastShown: null });
}

function saveReminderConfig(config) {
    storage.set(REMINDER_STORAGE_KEY, config);
}

async function syncReminderFromServer() {
    const local = storage.get(REMINDER_STORAGE_KEY);
    if (local?.time) return;
    try {
        const res = await fetch('api/reminder.php');
        const data = await res.json();
        if (data.success) {
            saveReminderConfig({ enabled: data.enabled, time: data.time, lastShown: null });
        }
    } catch (e) {}
}

function checkReminder() {
    const config = getReminderConfig();
    if (!config.enabled) return;

    const now = new Date();
    const today = now.toISOString().split('T')[0];
    if (config.lastShown === today) return;

    const [hours, minutes] = config.time.split(':').map(Number);
    const reminderTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hours, minutes, 0);
    const windowStart = new Date(reminderTime.getTime() - 5 * 60000);
    const windowEnd = new Date(reminderTime.getTime() + 60 * 60000);

    if (now >= windowStart && now <= windowEnd) {
        config.lastShown = today;
        saveReminderConfig(config);
        showToast('Time to log your mood! How are you feeling today?', 'info', '📝 Reminder');
        fetch('api/send-reminder.php').catch(() => {});
    }
}

function triggerReminder() {
    const config = getReminderConfig();
    if (!config.enabled) {
        showToast('Enable reminders in Profile settings', 'warning');
        return;
    }
    showToast('Time to log your mood! How are you feeling today?', 'info', '📝 Reminder');
    fetch('api/send-reminder.php').then(r => r.json()).then(data => {
        if (data.sent) showToast('Email reminder sent!', 'success');
    }).catch(() => {});
}

document.addEventListener('DOMContentLoaded', function() {
    syncReminderFromServer().then(checkReminder);
    setInterval(checkReminder, 60000);

    document.querySelectorAll('.header-btn').forEach(btn => {
        if (btn.querySelector('.fa-bell')) {
            btn.addEventListener('click', triggerReminder);
        }
    });
});

// Export for use in other scripts
window.MoodTrail = {
    showToast,
    closeModals,
    timeAgo,
    isValidEmail,
    storage,
    debounce,
    reminder: { getReminderConfig, saveReminderConfig, checkReminder, triggerReminder }
};