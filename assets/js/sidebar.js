/**
 * AI Banking GRC Platform - Sidebar Utilities
 * 
 * This file contains sidebar-specific functionality
 */

'use strict';

// ============================================================
// SIDEBAR CLASS
// ============================================================

class Sidebar {
    constructor(options = {}) {
        this.sidebar = document.getElementById(options.sidebarId || 'appSidebar');
        this.toggleBtn = document.getElementById(options.toggleId || 'sidebarToggle');
        this.appContent = document.querySelector('.app-content');
        this.backdrop = document.querySelector('.sidebar-backdrop');
        
        this.isCollapsed = false;
        this.isMobile = window.innerWidth < 992;
        
        this.init();
    }

    init() {
        if (!this.sidebar) return;
        
        // Load saved state
        this.loadState();
        
        // Toggle button
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => this.toggle());
        }
        
        // Backdrop click (mobile)
        if (this.backdrop) {
            this.backdrop.addEventListener('click', () => this.hide());
        }
        
        // Window resize
        window.addEventListener('resize', () => this.handleResize());
        
        // Keyboard shortcut: Ctrl + B
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                this.toggle();
            }
        });
    }

    toggle() {
        if (this.isMobile) {
            this.isCollapsed ? this.hide() : this.show();
        } else {
            this.isCollapsed ? this.expand() : this.collapse();
        }
    }

    show() {
        this.sidebar.classList.add('show');
        if (this.backdrop) this.backdrop.classList.add('show');
        this.isCollapsed = false;
    }

    hide() {
        this.sidebar.classList.remove('show');
        if (this.backdrop) this.backdrop.classList.remove('show');
        this.isCollapsed = true;
    }

    collapse() {
        this.sidebar.classList.add('collapsed');
        if (this.appContent) this.appContent.classList.add('expanded');
        this.isCollapsed = true;
        this.saveState();
    }

    expand() {
        this.sidebar.classList.remove('collapsed');
        if (this.appContent) this.appContent.classList.remove('expanded');
        this.isCollapsed = false;
        this.saveState();
    }

    handleResize() {
        const wasMobile = this.isMobile;
        this.isMobile = window.innerWidth < 992;
        
        if (wasMobile !== this.isMobile) {
            if (this.isMobile) {
                // Switch to mobile mode
                this.sidebar.classList.remove('collapsed');
                if (this.appContent) this.appContent.classList.remove('expanded');
            } else {
                // Switch to desktop mode
                this.sidebar.classList.remove('show');
                if (this.backdrop) this.backdrop.classList.remove('show');
                this.loadState();
            }
        }
    }

    loadState() {
        if (this.isMobile) {
            this.hide();
            return;
        }
        
        const saved = localStorage.getItem('sidebarCollapsed');
        if (saved === 'true') {
            this.collapse();
        } else {
            this.expand();
        }
    }

    saveState() {
        if (!this.isMobile) {
            localStorage.setItem('sidebarCollapsed', this.isCollapsed ? 'true' : 'false');
        }
    }

    getState() {
        return {
            collapsed: this.isCollapsed,
            mobile: this.isMobile,
            visible: this.sidebar.classList.contains('show')
        };
    }
}

// ============================================================
// INITIALIZE SIDEBAR
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    window.sidebar = new Sidebar();
});

// ============================================================
// EXPOSE SIDEBAR
// ============================================================

window.Sidebar = Sidebar;