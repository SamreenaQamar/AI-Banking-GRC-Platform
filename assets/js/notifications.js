/**
 * AI Banking GRC Platform - Notifications Module JavaScript
 * 
 * This file contains notifications-specific functionality including:
 * - Notification management
 * - Reminders
 * - Calendar
 * - Alerts
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initNotificationsModule();
});

/**
 * Initialize notifications functionality
 */
function initNotificationsModule() {
    // Notification filters
    initNotificationFilters();
    
    // Mark as read
    initMarkAsRead();
    
    // Reminders
    initReminders();
    
    // Calendar
    initNotificationsCalendar();
}

// ============================================================
// NOTIFICATION FILTERS
// ============================================================

/**
 * Initialize notification filters
 */
function initNotificationFilters() {
    const filters = {
        type: document.getElementById('filterType'),
        status: document.getElementById('filterStatus'),
        priority: document.getElementById('filterPriority')
    };
    
    Object.values(filters).forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyNotificationFilters);
        }
    });
    
    const searchInput = document.getElementById('searchNotification');
    if (searchInput) {
        searchInput.addEventListener('keyup', applyNotificationFilters);
    }
}

/**
 * Apply notification filters
 */
function applyNotificationFilters() {
    const type = document.getElementById('filterType')?.value;
    const status = document.getElementById('filterStatus')?.value;
    const priority = document.getElementById('filterPriority')?.value;
    const search = document.getElementById('searchNotification')?.value.toLowerCase() || '';
    
    const items = document.querySelectorAll('.notification-item');
    
    items.forEach(item => {
        let show = true;
        
        if (type && item.dataset.type !== type) show = false;
        if (status === 'unread' && !item.classList.contains('unread')) show = false;
        if (status === 'read' && item.classList.contains('unread')) show = false;
        if (priority && item.dataset.priority !== priority) show = false;
        if (search) {
            const text = item.textContent.toLowerCase();
            if (!text.includes(search)) show = false;
        }
        
        item.style.display = show ? '' : 'none';
    });
}

// ============================================================
// MARK AS READ
// ============================================================

/**
 * Initialize mark as read functionality
 */
function initMarkAsRead() {
    // Individual mark as read
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const item = this.closest('.notification-item');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch(`/api/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.classList.remove('unread');
                    item.querySelector('.notification-unread-dot')?.remove();
                    item.querySelector('.badge.bg-primary')?.remove();
                    this.remove();
                    updateNotificationStats();
                }
            })
            .catch(error => {
                showToast('Failed to mark as read', 'error');
            });
        });
    });
    
    // Mark all as read
    document.querySelectorAll('.mark-all-read').forEach(btn => {
        btn.addEventListener('click', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Mark all notifications as read?')) return;
            
            fetch('/api/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.classList.remove('unread');
                        item.querySelector('.notification-unread-dot')?.remove();
                        item.querySelector('.badge.bg-primary')?.remove();
                    });
                    document.querySelectorAll('.mark-read-btn').forEach(btn => btn.remove());
                    updateNotificationStats();
                    showToast('All notifications marked as read', 'success');
                }
            })
            .catch(error => {
                showToast('Failed to mark all as read', 'error');
            });
        });
    });
    
    // Select all
    document.getElementById('selectAllBtn')?.addEventListener('click', function() {
        document.querySelectorAll('.notification-select').forEach(cb => cb.checked = true);
    });
    
    document.getElementById('deselectAllBtn')?.addEventListener('click', function() {
        document.querySelectorAll('.notification-select').forEach(cb => cb.checked = false);
    });
    
    // Bulk actions
    document.getElementById('markSelectedRead')?.addEventListener('click', function() {
        const selected = document.querySelectorAll('.notification-select:checked');
        if (!selected.length) {
            showToast('Please select at least one notification', 'warning');
            return;
        }
        
        const ids = Array.from(selected).map(cb => cb.dataset.id);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        fetch('/api/notifications/mark-read-bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Selected notifications marked as read', 'success');
                location.reload();
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
        });
    });
    
    document.getElementById('deleteSelected')?.addEventListener('click', function() {
        const selected = document.querySelectorAll('.notification-select:checked');
        if (!selected.length) {
            showToast('Please select at least one notification', 'warning');
            return;
        }
        
        if (!confirm('Delete selected notifications?')) return;
        
        const ids = Array.from(selected).map(cb => cb.dataset.id);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        fetch('/api/notifications/delete-bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Selected notifications deleted', 'success');
                location.reload();
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
        });
    });
}

/**
 * Update notification statistics
 */
function updateNotificationStats() {
    const unread = document.querySelectorAll('.notification-item.unread').length;
    const total = document.querySelectorAll('.notification-item').length;
    
    const stats = {
        total: document.querySelector('.stat-value:first-child'),
        unread: document.querySelector('.stat-value.text-danger'),
        read: document.querySelector('.stat-value:nth-child(3)')
    };
    
    if (stats.total) stats.total.textContent = total;
    if (stats.unread) stats.unread.textContent = unread;
    if (stats.read) stats.read.textContent = total - unread;
    
    // Update badge
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        badge.textContent = unread;
        badge.style.display = unread > 0 ? 'flex' : 'none';
    }
}

// ============================================================
// REMINDERS
// ============================================================

/**
 * Initialize reminders
 */
function initReminders() {
    // Create reminder
    const form = document.getElementById('createReminderForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = getFormData(this);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating...';
            btn.disabled = true;
            
            fetch('/api/notifications/reminders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Reminder created successfully', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to create reminder', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    }
    
    // Complete reminder
    document.querySelectorAll('.reminder-complete').forEach(cb => {
        cb.addEventListener('change', function() {
            const id = this.dataset.id;
            const checked = this.checked;
            const item = this.closest('.reminder-item');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch(`/api/notifications/reminders/${id}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({ completed: checked })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.classList.toggle('completed', checked);
                    item.querySelector('.reminder-title')?.classList.toggle('completed-text', checked);
                } else {
                    this.checked = !checked;
                    showToast('Failed to update reminder', 'error');
                }
            })
            .catch(error => {
                this.checked = !checked;
                showToast('An error occurred', 'error');
            });
        });
    });
}

// ============================================================
// NOTIFICATIONS CALENDAR
// ============================================================

/**
 * Initialize notifications calendar
 */
function initNotificationsCalendar() {
    const prevBtn = document.getElementById('calendarPrev');
    const nextBtn = document.getElementById('calendarNext');
    const monthDisplay = document.getElementById('calendarMonth');
    
    if (!prevBtn || !nextBtn || !monthDisplay) return;
    
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    
    prevBtn.addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        loadNotificationsCalendar(currentMonth, currentYear);
    });
    
    nextBtn.addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        loadNotificationsCalendar(currentMonth, currentYear);
    });
    
    loadNotificationsCalendar(currentMonth, currentYear);
}

/**
 * Load notifications calendar
 */
function loadNotificationsCalendar(month, year) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/api/notifications/calendar?month=${month + 1}&year=${year}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderNotificationsCalendar(data.data, month, year);
        }
    })
    .catch(error => {
        showToast('Failed to load calendar', 'error');
    });
}

/**
 * Render notifications calendar
 */
function renderNotificationsCalendar(events, month, year) {
    const container = document.querySelector('.calendar-days');
    if (!container) return;
    
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('calendarMonth').textContent = `${monthNames[month]} ${year}`;
    
    container.innerHTML = '';
    
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    
    // Empty days
    for (let i = 0; i < firstDay; i++) {
        const empty = document.createElement('div');
        empty.className = 'calendar-day empty';
        container.appendChild(empty);
    }
    
    // Days
    for (let day = 1; day <= daysInMonth; day++) {
        const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = date === today.toISOString().split('T')[0];
        const hasEvent = events && events[date] && events[date].length > 0;
        
        const dayEl = document.createElement('div');
        dayEl.className = `calendar-day${isToday ? ' today' : ''}${hasEvent ? ' has-events' : ''}`;
        dayEl.dataset.date = date;
        dayEl.innerHTML = `
            <span class="day-number">${day}</span>
            ${hasEvent ? '<span class="event-dot"></span>' : ''}
            ${isToday ? '<span class="today-badge">Today</span>' : ''}
        `;
        
        if (hasEvent) {
            dayEl.addEventListener('click', function() {
                showDayEvents(events[date]);
            });
        }
        
        container.appendChild(dayEl);
    }
}

/**
 * Show day events
 */
function showDayEvents(events) {
    const modal = document.getElementById('eventModal');
    if (!modal) return;
    
    const list = modal.querySelector('.event-list');
    if (!list) return;
    
    list.innerHTML = '';
    events.forEach(event => {
        const item = document.createElement('div');
        item.className = 'event-item';
        item.innerHTML = `
            <div class="event-time">${event.time || 'All Day'}</div>
            <div class="event-content">
                <div class="event-title">${event.title}</div>
                <div class="event-description">${event.message || ''}</div>
            </div>
            ${event.url ? `<a href="${event.url}" class="event-link">View</a>` : ''}
        `;
        list.appendChild(item);
    });
    
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}