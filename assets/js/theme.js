/**
 * AI Banking GRC Platform - Theme Management
 * 
 * This file handles theme-related functionality including:
 * - Dark mode toggle
 * - Theme preferences persistence
 * - System theme detection
 * - Custom theme colors
 * - Sidebar theme management
 * - Theme transitions
 * - Accessibility settings
 * - Theme event handling
 */

'use strict';

// ============================================================
// THEME CONFIGURATION
// ============================================================

const THEME_CONFIG = {
    storageKey: 'grc_theme_preferences',
    darkModeClass: 'dark-mode',
    transitionDuration: 300, // milliseconds
    defaultTheme: 'light',
    themes: {
        light: {
            name: 'Light',
            icon: 'fa-sun',
            colors: {
                primary: '#0B3D91',
                secondary: '#2563EB',
                background: '#F4F7FC',
                card: '#FFFFFF',
                text: '#1E293B',
                sidebar: '#0F172A'
            }
        },
        dark: {
            name: 'Dark',
            icon: 'fa-moon',
            colors: {
                primary: '#1a5bbf',
                secondary: '#4b82f7',
                background: '#0F172A',
                card: '#1E293B',
                text: '#F1F5F9',
                sidebar: '#0B1120'
            }
        },
        system: {
            name: 'System',
            icon: 'fa-desktop',
            colors: {}
        }
    }
};

// ============================================================
// THEME CLASS
// ============================================================

class ThemeManager {
    constructor(options = {}) {
        this.config = { ...THEME_CONFIG, ...options };
        this.currentTheme = this.getStoredTheme() || this.config.defaultTheme;
        this.isDarkMode = false;
        this.themeTransitioning = false;
        this.observers = [];
        
        this.init();
    }

    /**
     * Initialize theme manager
     */
    init() {
        // Load saved theme preference
        this.loadTheme();
        
        // Setup theme toggle button
        this.setupThemeToggle();
        
        // Listen for system theme changes
        this.setupSystemThemeDetection();
        
        // Apply theme transitions
        this.setupThemeTransitions();
        
        // Setup accessibility
        this.setupAccessibility();
    }

    /**
     * Load stored theme
     */
    loadTheme() {
        const stored = this.getStoredTheme();
        if (stored) {
            this.currentTheme = stored;
            this.applyTheme(stored);
        } else {
            // Check system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                this.applyTheme('dark');
            } else {
                this.applyTheme('light');
            }
        }
    }

    /**
     * Get stored theme preference
     */
    getStoredTheme() {
        try {
            const stored = localStorage.getItem(this.config.storageKey);
            if (stored) {
                const parsed = JSON.parse(stored);
                return parsed.theme || null;
            }
        } catch (e) {
            console.warn('Error reading theme preference:', e);
        }
        return null;
    }

    /**
     * Store theme preference
     */
    storeTheme(theme) {
        try {
            const data = {
                theme: theme,
                timestamp: Date.now()
            };
            localStorage.setItem(this.config.storageKey, JSON.stringify(data));
        } catch (e) {
            console.warn('Error storing theme preference:', e);
        }
    }

    /**
     * Apply theme
     */
    applyTheme(theme) {
        // Prevent duplicate application
        if (this.currentTheme === theme && !this.themeTransitioning) {
            return;
        }

        this.themeTransitioning = true;
        this.currentTheme = theme;

        // Determine if dark mode
        this.isDarkMode = theme === 'dark' || (theme === 'system' && this.isSystemDark());

        // Apply class to body
        document.body.classList.toggle(this.config.darkModeClass, this.isDarkMode);

        // Update theme colors
        this.updateThemeColors(theme);

        // Update UI elements
        this.updateThemeUI(theme);

        // Store preference
        this.storeTheme(theme);

        // Trigger transition complete
        setTimeout(() => {
            this.themeTransitioning = false;
            this.dispatchEvent('themeChanged', { theme, isDark: this.isDarkMode });
        }, this.config.transitionDuration);
    }

    /**
     * Toggle theme
     */
    toggleTheme() {
        const themes = ['light', 'dark', 'system'];
        const currentIndex = themes.indexOf(this.currentTheme);
        const nextIndex = (currentIndex + 1) % themes.length;
        const nextTheme = themes[nextIndex];
        
        this.applyTheme(nextTheme);
        this.updateToggleButton(nextTheme);
    }

    /**
     * Update theme colors
     */
    updateThemeColors(theme) {
        const themeData = this.config.themes[theme];
        if (!themeData || !themeData.colors) return;

        const root = document.documentElement;
        const colors = themeData.colors;

        Object.entries(colors).forEach(([key, value]) => {
            root.style.setProperty(`--theme-${key}`, value);
            
            // Also update CSS variables if they exist
            const cssVar = this.getCssVariableName(key);
            if (cssVar) {
                root.style.setProperty(cssVar, value);
            }
        });
    }

    /**
     * Get CSS variable name for a color key
     */
    getCssVariableName(key) {
        const mapping = {
            primary: '--primary',
            secondary: '--secondary',
            background: '--bg-light',
            card: '--bg-white',
            text: '--text-dark',
            sidebar: '--sidebar-bg'
        };
        return mapping[key] || null;
    }

    /**
     * Update theme UI elements
     */
    updateThemeUI(theme) {
        // Update toggle button
        this.updateToggleButton(theme);

        // Update meta theme color
        this.updateMetaThemeColor(theme);

        // Update chart colors if charts exist
        this.updateChartColors(theme);

        // Dispatch custom event
        this.dispatchEvent('themeUpdated', { theme, isDark: this.isDarkMode });
    }

    /**
     * Update toggle button
     */
    updateToggleButton(theme) {
        const toggleBtn = document.getElementById('themeToggle');
        if (!toggleBtn) return;

        const themeData = this.config.themes[theme];
        if (!themeData) return;

        // Update icon
        const icon = toggleBtn.querySelector('i');
        if (icon) {
            icon.className = `fas ${themeData.icon}`;
        }

        // Update title
        toggleBtn.title = `Switch to ${this.getNextTheme()} mode`;
        toggleBtn.setAttribute('aria-label', `Switch to ${this.getNextTheme()} mode`);

        // Update active state
        toggleBtn.classList.toggle('active', this.isDarkMode);
    }

    /**
     * Get next theme in cycle
     */
    getNextTheme() {
        const themes = ['light', 'dark', 'system'];
        const currentIndex = themes.indexOf(this.currentTheme);
        const nextIndex = (currentIndex + 1) % themes.length;
        return themes[nextIndex];
    }

    /**
     * Update meta theme color
     */
    updateMetaThemeColor(theme) {
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (!metaThemeColor) return;

        const color = this.isDarkMode ? '#0F172A' : '#0B3D91';
        metaThemeColor.setAttribute('content', color);
    }

    /**
     * Update chart colors
     */
    updateChartColors(theme) {
        // This would update Chart.js default colors
        if (window.Chart) {
            const textColor = this.isDarkMode ? '#F1F5F9' : '#1E293B';
            const gridColor = this.isDarkMode ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';
            
            // Update Chart.js defaults
            window.Chart.defaults.color = textColor;
            window.Chart.defaults.borderColor = gridColor;
        }
    }

    /**
     * Setup theme toggle button
     */
    setupThemeToggle() {
        const toggleBtn = document.getElementById('themeToggle');
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', () => {
            this.toggleTheme();
        });

        // Keyboard support
        toggleBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.toggleTheme();
            }
        });

        // Initialize button
        this.updateToggleButton(this.currentTheme);
    }

    /**
     * Setup system theme detection
     */
    setupSystemThemeDetection() {
        if (!window.matchMedia) return;

        const darkModeMedia = window.matchMedia('(prefers-color-scheme: dark)');
        
        // Listen for system theme changes
        darkModeMedia.addEventListener('change', (e) => {
            if (this.currentTheme === 'system') {
                this.applyTheme('system');
            }
        });
    }

    /**
     * Check if system is in dark mode
     */
    isSystemDark() {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    /**
     * Setup theme transitions
     */
    setupThemeTransitions() {
        // Add transition class to body
        document.body.classList.add('theme-transition');
        
        // Remove transition class after animation
        document.addEventListener('themeChanged', () => {
            document.body.classList.add('theme-transition');
            setTimeout(() => {
                document.body.classList.remove('theme-transition');
            }, this.config.transitionDuration);
        });
    }

    /**
     * Setup accessibility
     */
    setupAccessibility() {
        // Respect reduced motion preference
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (prefersReducedMotion.matches) {
            document.body.classList.add('reduced-motion');
        }

        // High contrast support
        const prefersHighContrast = window.matchMedia('(prefers-contrast: high)');
        if (prefersHighContrast.matches) {
            document.body.classList.add('high-contrast');
        }

        // Listen for preference changes
        prefersReducedMotion.addEventListener('change', (e) => {
            document.body.classList.toggle('reduced-motion', e.matches);
        });

        prefersHighContrast.addEventListener('change', (e) => {
            document.body.classList.toggle('high-contrast', e.matches);
        });
    }

    /**
     * Dispatch custom event
     */
    dispatchEvent(eventName, detail) {
        const event = new CustomEvent(eventName, {
            detail: detail,
            bubbles: true,
            cancelable: true
        });
        document.dispatchEvent(event);
    }

    /**
     * Add event listener
     */
    on(eventName, callback) {
        document.addEventListener(eventName, callback);
        return this;
    }

    /**
     * Remove event listener
     */
    off(eventName, callback) {
        document.removeEventListener(eventName, callback);
        return this;
    }

    /**
     * Get current theme
     */
    getCurrentTheme() {
        return this.currentTheme;
    }

    /**
     * Check if dark mode is active
     */
    isDark() {
        return this.isDarkMode;
    }

    /**
     * Get theme configuration
     */
    getThemeConfig(theme) {
        return this.config.themes[theme] || null;
    }

    /**
     * Get all available themes
     */
    getAvailableThemes() {
        return Object.keys(this.config.themes);
    }

    /**
     * Set custom theme
     */
    setCustomTheme(colors) {
        const root = document.documentElement;
        Object.entries(colors).forEach(([key, value]) => {
            root.style.setProperty(`--${key}`, value);
        });
        this.dispatchEvent('customThemeApplied', { colors });
    }

    /**
     * Reset theme to default
     */
    resetTheme() {
        const root = document.documentElement;
        const defaultTheme = this.config.themes[this.config.defaultTheme];
        if (defaultTheme && defaultTheme.colors) {
            Object.keys(defaultTheme.colors).forEach(key => {
                const cssVar = this.getCssVariableName(key);
                if (cssVar) {
                    root.style.removeProperty(cssVar);
                }
            });
        }
        this.applyTheme(this.config.defaultTheme);
    }
}

// ============================================================
// THEME UTILITIES
// ============================================================

/**
 * Get theme color
 */
function getThemeColor(colorName) {
    const root = document.documentElement;
    const cssVar = `--${colorName}`;
    return getComputedStyle(root).getPropertyValue(cssVar).trim();
}

/**
 * Set theme color
 */
function setThemeColor(colorName, value) {
    const root = document.documentElement;
    const cssVar = `--${colorName}`;
    root.style.setProperty(cssVar, value);
}

// ============================================================
// INITIALIZE THEME
// ============================================================

// Initialize theme manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.themeManager = new ThemeManager();
});

// ============================================================
// EXPOSE THEME MANAGER
// ============================================================

window.ThemeManager = ThemeManager;
window.getThemeColor = getThemeColor;
window.setThemeColor = setThemeColor;