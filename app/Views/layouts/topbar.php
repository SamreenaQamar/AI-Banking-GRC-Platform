<!-- Topbar Start -->
<nav class="topbar">
    <div class="topbar-left">
        <!-- Sidebar Toggle -->
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Logo -->
        <a href="<?php echo BASE_URL; ?>/dashboard" class="topbar-logo">
            <img src="<?php echo ASSETS_URL; ?>/images/logo-white.svg" alt="<?php echo APP_NAME; ?>" height="32">
            <span class="logo-text d-none d-lg-inline"><?php echo APP_SHORT_NAME; ?></span>
        </a>
    </div>
    
    <div class="topbar-center d-none d-md-flex">
        <!-- Search Bar -->
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search GRC Platform..." id="globalSearch">
            <div class="search-shortcut">Ctrl + K</div>
        </div>
    </div>
    
    <div class="topbar-right">
        <!-- AI Quick Action -->
        <button class="topbar-btn ai-btn" data-bs-toggle="tooltip" title="AI Assistant">
            <i class="fas fa-robot"></i>
        </button>
        
        <!-- Dark Mode Toggle -->
        <button class="topbar-btn" id="darkModeToggle" data-bs-toggle="tooltip" title="Toggle Dark Mode">
            <i class="fas fa-moon"></i>
        </button>
        
        <!-- Notifications -->
        <div class="dropdown notification-dropdown">
            <button class="topbar-btn" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge">3</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-menu">
                <div class="notification-header">
                    <h6>Notifications</h6>
                    <a href="#" class="mark-all-read">Mark all read</a>
                </div>
                <div class="notification-list">
                    <!-- Notification items will be dynamically loaded -->
                    <div class="notification-item unread">
                        <div class="notification-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="notification-content">
                            <p>New SBP Circular uploaded</p>
                            <span class="notification-time">2 hours ago</span>
                        </div>
                    </div>
                    <div class="notification-item unread">
                        <div class="notification-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="notification-content">
                            <p>Audit evidence uploaded</p>
                            <span class="notification-time">3 hours ago</span>
                        </div>
                    </div>
                    <div class="notification-item unread">
                        <div class="notification-icon danger">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="notification-content">
                            <p>Compliance task overdue</p>
                            <span class="notification-time">5 hours ago</span>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon info">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="notification-content">
                            <p>New user added: Ali Raza</p>
                            <span class="notification-time">1 day ago</span>
                        </div>
                    </div>
                </div>
                <div class="notification-footer">
                    <a href="<?php echo BASE_URL; ?>/notifications">View All Notifications</a>
                </div>
            </div>
        </div>
        
        <!-- User Profile -->
        <div class="dropdown profile-dropdown">
            <button class="profile-btn" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="profile-avatar">
                    <?php if (isset($current_user) && $current_user->profile_image): ?>
                        <img src="<?php echo UPLOADS_URL; ?>/<?php echo $current_user->profile_image; ?>" alt="Profile">
                    <?php else: ?>
                        <span><?php echo isset($current_user) ? strtoupper(substr($current_user->first_name ?? 'U', 0, 1) . substr($current_user->last_name ?? '', 0, 1)) : 'U'; ?></span>
                    <?php endif; ?>
                </div>
                <div class="profile-info d-none d-sm-block">
                    <span class="profile-name"><?php echo isset($current_user) ? $current_user->full_name ?? $current_user->username : 'Guest'; ?></span>
                    <span class="profile-role"><?php echo isset($current_user) ? ucfirst(str_replace('_', ' ', $current_user->role_display_name ?? 'User')) : 'Guest'; ?></span>
                </div>
                <i class="fas fa-chevron-down profile-arrow"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?php echo BASE_URL; ?>/settings">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile/2fa">
                        <i class="fas fa-shield-alt"></i> Security
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Current Date -->
        <div class="current-date d-none d-lg-block">
            <i class="far fa-calendar-alt"></i>
            <span><?php echo date('d M Y'); ?></span>
        </div>
    </div>
</nav>
<!-- Topbar End -->