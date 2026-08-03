/**
 * AI Banking GRC Platform - Topbar Utilities
 * 
 * This file contains topbar-specific functionality
 */

'use strict';

// ============================================================
// TOPBAR CLASS
// ============================================================

class Topbar {
    constructor(options = {}) {
        this.topbar = document.getElementById(options.topbarId || 'appTopbar');
        this.searchInput = document.getElementById(options.searchId || 'globalSearch');
        this.searchShortcut = document.querySelector('.search-shortcut');
        this.notifications = document.querySelector('.notification-dropdown');
        this.profile = document.querySelector('.profile-dropdown');
        
        this.init();
    }

    init() {
        if (!this.topbar) return;
        
        // Search functionality
        this.initSearch();
        
        // Notifications
        this.initNotifications();
        
        // Profile dropdown
        this.initProfile();
        
        // Keyboard shortcuts
        this.initKeyboardShortcuts();
        
        // Auto-hide on scroll (optional)
        this.initAutoHide();
    }

    initSearch() {
        if (!this.searchInput) return;
        
        // Focus on Ctrl+K
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.searchInput.focus();
            }
            if (e.key === 'Escape') {
                this.searchInput.blur();
            }
        });
        
        // Search on enter
        this.searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                const query = this.searchInput.value.trim();
                if (query.length > 0) {
                    window.location.href = `/search?q=${encodeURIComponent(query)}`;
                }
            }
        });
        
        // Search suggestions (debounced)
        let searchTimeout;
        this.searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const query = this.searchInput.value.trim();
            if (query.length >= 3) {
                searchTimeout = setTimeout(() => {
                    this.fetchSearchSuggestions(query);
                }, 300);
            }
        });
    }

    fetchSearchSuggestions(query) {
        // This would fetch search suggestions from the server
        console.log('Search suggestions for:', query);
    }

    initNotifications() {
        if (!this.notifications) return;
        
        // Load notifications
        this.loadNotifications();
        
        // Refresh notifications periodically
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }

    loadNotifications() {
        const badge = document.querySelector('.notification-badge');
        if (!badge) return;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        fetch('/api/notifications/unread-count', {
            headers: {
                'X-CSRF-TOKEN': csrfToken || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'flex' : 'none';
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
    }

    initProfile() {
        if (!this.profile) return;
        
        // Profile dropdown toggle
        this.profile.addEventListener('show.bs.dropdown', () => {
            // Load user activity
            this.loadUserActivity();
        });
    }

    loadUserActivity() {
        // This would load user activity data
    }

    initKeyboardShortcuts() {
        // Global keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Alt + D: Dashboard
            if (e.altKey && e.key === 'd') {
                e.preventDefault();
                window.location.href = '/dashboard';
            }
            
            // Alt + N: Notifications
            if (e.altKey && e.key === 'n') {
                e.preventDefault();
                const dropdown = document.querySelector('.notification-dropdown .dropdown-toggle');
                if (dropdown) dropdown.click();
            }
            
            // Alt + P: Profile
            if (e.altKey && e.key === 'p') {
                e.preventDefault();
                const dropdown = document.querySelector('.profile-dropdown .dropdown-toggle');
                if (dropdown) dropdown.click();
            }
        });
    }

    initAutoHide() {
        let lastScrollY = window.scrollY;
        let isHidden = false;
        
        window.addEventListener('scroll', () => {
            const currentScrollY = window.scrollY;
            
            if (currentScrollY > 100 && currentScrollY > lastScrollY) {
                // Scrolling down - hide topbar
                if (!isHidden) {
                    this.topbar.style.transform = 'translateY(-100%)';
                    isHidden = true;
                }
            } else if (currentScrollY < lastScrollY || currentScrollY < 100) {
                // Scrolling up or at top - show topbar
                if (isHidden) {
                    this.topbar.style.transform = 'translateY(0)';
                    isHidden = false;
                }
            }
            
            lastScrollY = currentScrollY;
        });
    }

    show() {
        this.topbar.style.transform = 'translateY(0)';
    }

    hide() {
        this.topbar.style.transform = 'translateY(-100%)';
    }

    toggle() {
        const isHidden = this.topbar.style.transform === 'translateY(-100%)';
        isHidden ? this.show() : this.hide();
    }
}

// ============================================================
// INITIALIZE TOPBAR
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    window.topbar = new Topbar();
});

// ============================================================
// EXPOSE TOPBAR
// ============================================================

window.Topbar = Topbar;