// Mobile Navigation
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const nav = document.querySelector('.nav');

    if (hamburger) {
        hamburger.addEventListener('click', function() {
            nav.classList.toggle('open');
        });
    }

    // Close mobile nav on link click
    document.querySelectorAll('.nav a').forEach(link => {
        link.addEventListener('click', () => nav.classList.remove('open'));
    });

    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Confirm dialogs
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) e.preventDefault();
        });
    });
});

// Toast Notification System
function showToast(message, type = 'info') {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('show'));

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

// Exam Timer
let examTimer;
function startExamTimer(durationSeconds, displayId, formId) {
    const display = document.getElementById(displayId);
    const form = document.getElementById(formId);
    let remaining = durationSeconds;

    function updateDisplay() {
        const mins = Math.floor(remaining / 60);
        const secs = remaining % 60;
        display.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

        if (remaining <= 60) {
            display.style.color = '#EF4444';
            display.style.animation = 'pulse 1s infinite';
        }
    }

    updateDisplay();

    examTimer = setInterval(() => {
        remaining--;
        updateDisplay();

        if (remaining <= 0) {
            clearInterval(examTimer);
            if (form) form.submit();
        }
    }, 1000);
}

// Search Filter
function filterItems(inputId, cardSelector) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll(cardSelector).forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(query) ? '' : 'none';
        });
    });
}

// Tab Filter
function initTabs(containerId, cardSelector) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            container.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;

            document.querySelectorAll(cardSelector).forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}

// Save/Bookmark toggle
function toggleSave(btn, itemType, itemId) {
    const icon = btn.querySelector('.save-icon') || btn;

    fetch('ajax/save_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `item_type=${itemType}&item_id=${itemId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'saved') {
            icon.textContent = '★';
            icon.style.color = '#F59E0B';
            showToast('Saved successfully!', 'success');
        } else if (data.status === 'unsaved') {
            icon.textContent = '☆';
            icon.style.color = '#64748B';
            showToast('Removed from saved items', 'info');
        }
    })
    .catch(() => showToast('Please login to save items', 'error'));
}

// Count up animation for stats
function animateCounters() {
    document.querySelectorAll('.stat-number').forEach(el => {
        const target = parseInt(el.textContent.replace(/[+,]/g, ''));
        if (isNaN(target)) return;

        let current = 0;
        const increment = Math.ceil(target / 60);
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current.toLocaleString() + '+';
        }, 25);
    });
}

// Run on page load for homepage
if (window.location.pathname.endsWith('index.php') || window.location.pathname === '/Job/' || window.location.pathname === '/Job/index.php') {
    document.addEventListener('DOMContentLoaded', animateCounters);
}
