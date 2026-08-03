<?php
/**
 * AI Banking GRC Platform - Custom Styles
 * 
 * This file contains all custom CSS for the enterprise banking UI.
 * Colors: #0B3D91 (Primary), #2563EB (Secondary), #00B894 (Accent)
 * Font: Poppins
 */
?>

/* ============================================================
   ROOT VARIABLES
   ============================================================ */
:root {
    --primary: #0B3D91;
    --primary-light: #1a5bbf;
    --primary-dark: #082b6e;
    --secondary: #2563EB;
    --accent: #00B894;
    --bg-light: #F4F7FC;
    --bg-white: #FFFFFF;
    --text-dark: #1E293B;
    --text-light: #64748B;
    --border: #E2E8F0;
    --success: #22C55E;
    --warning: #F59E0B;
    --danger: #EF4444;
    --info: #3B82F6;
    
    --sidebar-width: 260px;
    --sidebar-collapsed-width: 72px;
    --topbar-height: 70px;
    --footer-height: 60px;
    
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
    --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
    --shadow-xl: 0 12px 36px rgba(0,0,0,0.16);
    
    --radius-sm: 6px;
    --radius-md: 10px;
    --radius-lg: 16px;
    --radius-xl: 20px;
    
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ============================================================
   GLOBAL STYLES
   ============================================================ */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: var(--bg-light);
    color: var(--text-dark);
    overflow-x: hidden;
}

/* ============================================================
   APP WRAPPER
   ============================================================ */
.app-wrapper {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.app-main {
    display: flex;
    flex: 1;
    margin-top: var(--topbar-height);
}

/* ============================================================
   TOPBAR
   ============================================================ */
.topbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--topbar-height);
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(11, 61, 145, 0.3);
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.sidebar-toggle {
    background: none;
    border: none;
    color: rgba(255,255,255,0.8);
    font-size: 20px;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: 6px;
    transition: var(--transition);
}

.sidebar-toggle:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.topbar-logo {
    display: flex;
    align-items: center;
    text-decoration: none;
    gap: 10px;
}

.logo-text {
    color: #fff;
    font-weight: 600;
    font-size: 16px;
    letter-spacing: 0.5px;
}

.topbar-center {
    flex: 1;
    max-width: 520px;
    margin: 0 24px;
}

.search-wrapper {
    position: relative;
    width: 100%;
}

.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.5);
    font-size: 14px;
}

.search-input {
    width: 100%;
    padding: 10px 44px 10px 40px;
    border: none;
    border-radius: 10px;
    background: rgba(255,255,255,0.12);
    color: #fff;
    font-size: 14px;
    transition: var(--transition);
    backdrop-filter: blur(8px);
}

.search-input::placeholder {
    color: rgba(255,255,255,0.5);
}

.search-input:focus {
    outline: none;
    background: rgba(255,255,255,0.2);
    box-shadow: 0 0 0 3px rgba(255,255,255,0.1);
}

.search-shortcut {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.1);
    padding: 2px 10px;
    border-radius: 4px;
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    font-weight: 500;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.topbar-btn {
    position: relative;
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 10px;
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.8);
    font-size: 18px;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
}

.topbar-btn:hover {
    background: rgba(255,255,255,0.15);
    color: #fff;
}

.topbar-btn.ai-btn {
    background: linear-gradient(135deg, var(--accent), #00a67e);
    color: #fff;
}

.topbar-btn.ai-btn:hover {
    transform: scale(1.05);
}

.notification-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: var(--danger);
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px 6px 6px;
    border: none;
    border-radius: 12px;
    background: rgba(255,255,255,0.08);
    cursor: pointer;
    transition: var(--transition);
}

.profile-btn:hover {
    background: rgba(255,255,255,0.15);
}

.profile-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar span {
    color: #fff;
    font-weight: 600;
    font-size: 14px;
}

.profile-info {
    text-align: left;
}

.profile-name {
    display: block;
    color: #fff;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.2;
}

.profile-role {
    display: block;
    color: rgba(255,255,255,0.6);
    font-size: 11px;
}

.profile-arrow {
    color: rgba(255,255,255,0.4);
    font-size: 12px;
    margin-left: 4px;
}

.current-date {
    color: rgba(255,255,255,0.6);
    font-size: 13px;
    padding-left: 12px;
    border-left: 1px solid rgba(255,255,255,0.1);
}

.current-date i {
    margin-right: 6px;
}

/* ============================================================
   SIDEBAR
   ============================================================ */
.sidebar {
    position: fixed;
    top: var(--topbar-height);
    left: 0;
    bottom: 0;
    width: var(--sidebar-width);
    background: #0F172A;
    z-index: 999;
    transition: var(--transition);
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
}

.sidebar-inner {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 16px 0;
}

.sidebar-header {
    padding: 0 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-logo {
    height: 32px;
    width: auto;
}

.brand-text {
    color: #fff;
    font-weight: 700;
    font-size: 18px;
    letter-spacing: 1px;
}

.sidebar-nav {
    flex: 1;
    padding: 16px 12px;
}

.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 2px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 10px 14px;
    border-radius: 10px;
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    transition: var(--transition);
    gap: 12px;
    position: relative;
}

.nav-link:hover {
    background: rgba(255,255,255,0.06);
    color: #fff;
}

.nav-item.active .nav-link {
    background: rgba(37, 99, 235, 0.15);
    color: #fff;
}

.nav-item.active .nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 24px;
    background: var(--secondary);
    border-radius: 0 3px 3px 0;
}

.nav-icon {
    width: 20px;
    font-size: 16px;
    text-align: center;
    flex-shrink: 0;
}

.nav-text {
    font-size: 14px;
    font-weight: 400;
    white-space: nowrap;
}

.sidebar.collapsed .nav-text,
.sidebar.collapsed .nav-badge,
.sidebar.collapsed .brand-text {
    display: none;
}

.nav-badge {
    margin-left: auto;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.bg-success { background: var(--success); }
.bg-warning { background: var(--warning); color: #000; }
.bg-danger { background: var(--danger); }
.bg-info { background: var(--info); }
.bg-primary { background: var(--secondary); }
.bg-gradient { 
    background: linear-gradient(135deg, var(--secondary), var(--accent)); 
    color: #fff;
}

.nav-divider {
    height: 1px;
    background: rgba(255,255,255,0.06);
    margin: 12px 14px;
}

.sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid rgba(255,255,255,0.05);
}

.sidebar-status {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-dot.online {
    background: var(--success);
}

.status-text {
    color: rgba(255,255,255,0.4);
    font-size: 12px;
}

.sidebar-version {
    color: rgba(255,255,255,0.2);
    font-size: 11px;
    margin-top: 4px;
}

/* ============================================================
   APP CONTENT
   ============================================================ */
.app-content {
    flex: 1;
    margin-left: var(--sidebar-width);
    min-height: calc(100vh - var(--topbar-height) - var(--footer-height));
    transition: var(--transition);
    display: flex;
    flex-direction: column;
}

.app-content.expanded {
    margin-left: var(--sidebar-collapsed-width);
}

/* ============================================================
   PAGE HEADER
   ============================================================ */
.page-header {
    padding: 20px 30px 16px;
    background: var(--bg-white);
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
}

.page-header-left {
    flex: 1;
}

.page-title {
    font-size: 22px;
    font-weight: 600;
    margin: 0;
    color: var(--text-dark);
}

.breadcrumb {
    margin: 4px 0 0;
    padding: 0;
    background: transparent;
    font-size: 13px;
}

.breadcrumb-item a {
    color: var(--secondary);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: var(--text-light);
}

.page-header-right {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.quick-actions {
    display: flex;
    gap: 8px;
}

.last-updated {
    color: var(--text-light);
    font-size: 12px;
}

.last-updated i {
    margin-right: 4px;
}

/* ============================================================
   PAGE CONTENT
   ============================================================ */
.page-content {
    flex: 1;
    padding: 24px 30px 30px;
}

/* ============================================================
   FOOTER
   ============================================================ */
.app-footer {
    margin-left: var(--sidebar-width);
    background: var(--bg-white);
    border-top: 1px solid var(--border);
    padding: 12px 30px;
    transition: var(--transition);
}

.app-footer.expanded {
    margin-left: var(--sidebar-collapsed-width);
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.footer-left,
.footer-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.footer-copyright {
    color: var(--text-light);
    font-size: 13px;
}

.footer-divider {
    color: var(--border);
}

.footer-version {
    color: var(--text-light);
    font-size: 13px;
}

.footer-status {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-light);
    font-size: 13px;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--success);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.footer-link {
    color: var(--text-light);
    text-decoration: none;
    font-size: 13px;
    transition: var(--transition);
}

.footer-link:hover {
    color: var(--secondary);
}

.footer-stats {
    display: flex;
    gap: 24px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--border);
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-light);
    font-size: 12px;
}

.stat-value {
    color: var(--text-dark);
    font-weight: 600;
}

/* ============================================================
   CARDS
   ============================================================ */
.card {
    background: var(--bg-white);
    border: none;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
}

.card:hover {
    box-shadow: var(--shadow-md);
}

.card-header {
    background: transparent;
    border-bottom: 1px solid var(--border);
    padding: 16px 20px;
    font-weight: 600;
}

.card-header-gradient {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: #fff;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    padding: 16px 20px;
}

/* ============================================================
   BUTTONS
   ============================================================ */
.btn {
    font-family: 'Poppins', sans-serif;
    border-radius: 8px;
    font-weight: 500;
    padding: 8px 20px;
    transition: var(--transition);
}

.btn-primary {
    background: var(--secondary);
    border-color: var(--secondary);
}

.btn-primary:hover {
    background: var(--primary);
    border-color: var(--primary);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.btn-outline-primary {
    color: var(--secondary);
    border-color: var(--secondary);
}

.btn-outline-primary:hover {
    background: var(--secondary);
    color: #fff;
}

.btn-success {
    background: var(--success);
    border-color: var(--success);
}

.btn-danger {
    background: var(--danger);
    border-color: var(--danger);
}

/* ============================================================
   DASHBOARD WIDGETS
   ============================================================ */
.widget-card {
    padding: 20px;
    border-radius: var(--radius-lg);
    background: var(--bg-white);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    height: 100%;
}

.widget-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.widget-title {
    font-size: 14px;
    color: var(--text-light);
    font-weight: 500;
    margin: 0;
}

.widget-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.widget-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 4px 0;
}

.widget-change {
    font-size: 13px;
    font-weight: 500;
}

.widget-change.positive { color: var(--success); }
.widget-change.negative { color: var(--danger); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .app-content {
        margin-left: 0;
    }
    
    .app-footer {
        margin-left: 0;
    }
    
    .sidebar.collapsed {
        width: var(--sidebar-width);
    }
    
    .app-content.expanded {
        margin-left: 0;
    }
}

@media (max-width: 768px) {
    .topbar {
        padding: 0 12px;
    }
    
    .topbar-center {
        display: none;
    }
    
    .profile-info {
        display: none;
    }
    
    .page-header {
        padding: 16px;
    }
    
    .page-content {
        padding: 16px;
    }
    
    .footer-content {
        flex-direction: column;
        text-align: center;
    }
    
    .footer-stats {
        flex-wrap: wrap;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .topbar-right .topbar-btn:not(.profile-btn):not(.ai-btn) {
        display: none;
    }
    
    .quick-actions .btn {
        font-size: 12px;
        padding: 6px 12px;
    }
    
    .widget-value {
        font-size: 22px;
    }
}