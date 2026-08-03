/**
 * AI Banking GRC Platform - Main Application JavaScript
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains the main application logic including:
 * - Global event handlers
 * - UI interactions
 * - AJAX setup
 * - Utility functions
 * - Component initialization
 */

// ============================================================
// STRICT MODE
// ============================================================

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initApp();
});

/**
 * Initialize Application
 */
function initApp() {
    // Initialize sidebar
    initSidebar();
    
    // Initialize topbar
    initTopbar();
    
    // Initialize tooltips
    initTooltips();
    
    // Initialize popovers
    initPopovers();
    
    // Initialize notifications
    initNotifications();
    
    // Initialize search
    initSearch();
    
    // Initialize dark mode
    initDarkMode();
    
    // Initialize CSRF protection
    initCSRF();
    
    // Initialize auto-refresh
    initAutoRefresh();
}

// ============================================================
// SIDEBAR
// ============================================================

/**
 * Initialize sidebar
 */
function initSidebar() {
    const sidebar = document.getElementById('appSidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const appContent = document.querySelector('.app-content');
    
    if (!sidebar || !toggleBtn) return;
    
    // Toggle sidebar
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        appContent.classList.toggle('expanded');
        
        // Save state
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });
    
    // Restore state
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
        appContent.classList.add('expanded');
    }
    
    // Handle responsive
    handleSidebarResponsive();
    window.addEventListener('resize', handleSidebarResponsive);
}

/**
 * Handle sidebar responsive
 */
function handleSidebarResponsive() {
    const sidebar = document.getElementById('appSidebar');
    const appContent = document.querySelector('.app-content');
    const width = window.innerWidth;
    
    if (width < 992) {
        sidebar.classList.add('collapsed');
        appContent.classList.add('expanded');
    } else {
        if (localStorage.getItem('sidebarCollapsed') !== 'true') {
            sidebar.classList.remove('collapsed');
            appContent.classList.remove('expanded');
        }
    }
}

// ============================================================
// TOPBAR
// ============================================================

/**
 * Initialize topbar
 */
function initTopbar() {
    // Topbar functionality
    const searchInput = document.getElementById('globalSearch');
    if (searchInput) {
        // Keyboard shortcut: Ctrl + K
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
            if (e.key === 'Escape') {
                searchInput.blur();
            }
        });
        
        // Search on enter
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query.length > 0) {
                    window.location.href = '/search?q=' + encodeURIComponent(query);
                }
            }
        });
    }
}

// ============================================================
// TOOLTIPS
// ============================================================

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            container: 'body',
            trigger: 'hover'
        });
    });
}

// ============================================================
// POPOVERS
// ============================================================

/**
 * Initialize Bootstrap popovers
 */
function initPopovers() {
    const popoverTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="popover"]')
    );
    
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// ============================================================
// NOTIFICATIONS
// ============================================================

/**
 * Initialize notifications
 */
function initNotifications() {
    // Load notifications
    loadNotifications();
    
    // Mark all as read
    const markAllBtn = document.querySelector('.mark-all-read');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllNotificationsRead();
        });
    }
}

/**
 * Load notifications via AJAX
 */
function loadNotifications() {
    // This would fetch notifications from server
    // For now, we'll use the existing HTML
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsRead() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch('/api/notifications/read-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            document.querySelectorAll('.notification-badge').forEach(badge => {
                badge.textContent = '0';
                badge.style.display = 'none';
            });
        }
    })
    .catch(error => console.error('Error marking notifications:', error));
}

// ============================================================
// SEARCH
// ============================================================

/**
 * Initialize global search
 */
function initSearch() {
    const searchInput = document.getElementById('globalSearch');
    if (!searchInput) return;
    
    // Debounced search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        if (query.length >= 3) {
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        }
    });
}

/**
 * Perform search
 */
function performSearch(query) {
    // Implement search functionality
    console.log('Searching for:', query);
}

// ============================================================
// DARK MODE
// ============================================================

/**
 * Initialize dark mode
 */
function initDarkMode() {
    const toggleBtn = document.getElementById('darkModeToggle');
    if (!toggleBtn) return;
    
    // Check saved preference
    const isDark = localStorage.getItem('darkMode') === 'true';
    if (isDark) {
        document.body.classList.add('dark-mode');
        toggleBtn.querySelector('i').classList.replace('fa-moon', 'fa-sun');
    }
    
    toggleBtn.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const icon = this.querySelector('i');
        const isDark = document.body.classList.contains('dark-mode');
        
        icon.classList.toggle('fa-moon', !isDark);
        icon.classList.toggle('fa-sun', isDark);
        localStorage.setItem('darkMode', isDark);
    });
}

// ============================================================
// CSRF PROTECTION
// ============================================================

/**
 * Initialize CSRF protection for AJAX requests
 */
function initCSRF() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    // Add CSRF token to all AJAX requests
    document.addEventListener('ajax:beforeSend', function(event) {
        if (csrfToken) {
            event.detail.xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        }
    });
    
    // Fetch API interceptor
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        if (!options.headers) options.headers = {};
        if (csrfToken && !options.headers['X-CSRF-TOKEN']) {
            options.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        return originalFetch.call(this, url, options);
    };
}

// ============================================================
// AUTO REFRESH
// ============================================================

/**
 * Initialize auto-refresh for dashboard
 */
function initAutoRefresh() {
    const refreshInterval = document.querySelector('meta[name="refresh-interval"]')?.content;
    if (!refreshInterval) return;
    
    const interval = parseInt(refreshInterval) * 1000;
    if (interval < 10000) return; // Minimum 10 seconds
    
    let refreshCountdown = interval / 1000;
    
    // Update countdown display
    const countdownEl = document.getElementById('refreshCountdown');
    if (countdownEl) {
        setInterval(() => {
            refreshCountdown--;
            if (refreshCountdown <= 0) {
                refreshCountdown = interval / 1000;
            }
            countdownEl.textContent = refreshCountdown;
        }, 1000);
    }
    
    // Auto refresh
    setInterval(() => {
        // Only refresh if page is visible
        if (!document.hidden) {
            location.reload();
        }
    }, interval);
}

// ============================================================
// UTILITY FUNCTIONS
// ============================================================

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

/**
 * Format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-PK', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

/**
 * Format datetime
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-PK', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Format currency (PKR)
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

/**
 * Debounce function
 */
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

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ============================================================
// EXPOSE FUNCTIONS GLOBALLY
// ============================================================

window.showToast = showToast;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
window.formatCurrency = formatCurrency;
window.debounce = debounce;
window.throttle = throttle;