<!-- Sidebar Start -->
<aside class="sidebar" id="appSidebar">
    <div class="sidebar-inner">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <img src="<?php echo ASSETS_URL; ?>/images/logo-white.svg" alt="Logo" class="brand-logo">
                <span class="brand-text">GRC</span>
            </div>
        </div>
        
        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <!-- Dashboard -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'dashboard' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="nav-link">
                        <i class="fas fa-chart-pie nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                        <span class="nav-badge bg-success">Live</span>
                    </a>
                </li>
                
                <!-- Compliance -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'compliance' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/compliance" class="nav-link">
                        <i class="fas fa-check-circle nav-icon"></i>
                        <span class="nav-text">Compliance</span>
                        <span class="nav-badge bg-warning">12</span>
                    </a>
                </li>
                
                <!-- Risk -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'risk' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/risk" class="nav-link">
                        <i class="fas fa-shield-alt nav-icon"></i>
                        <span class="nav-text">Risk</span>
                        <span class="nav-badge bg-danger">23</span>
                    </a>
                </li>
                
                <!-- Audit -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'audit' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/audit" class="nav-link">
                        <i class="fas fa-clipboard-check nav-icon"></i>
                        <span class="nav-text">Audit</span>
                        <span class="nav-badge bg-info">5</span>
                    </a>
                </li>
                
                <!-- Policies -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'policies' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/policies" class="nav-link">
                        <i class="fas fa-file-contract nav-icon"></i>
                        <span class="nav-text">Policies</span>
                    </a>
                </li>
                
                <!-- SBP Circulars -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'sbp' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/sbp-circulars" class="nav-link">
                        <i class="fas fa-newspaper nav-icon"></i>
                        <span class="nav-text">SBP Circulars</span>
                        <span class="nav-badge bg-primary">8</span>
                    </a>
                </li>
                
                <!-- AI Assistant -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'ai' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/ai" class="nav-link">
                        <i class="fas fa-robot nav-icon"></i>
                        <span class="nav-text">AI Assistant</span>
                        <span class="nav-badge bg-gradient">New</span>
                    </a>
                </li>
                
                <li class="nav-divider"></li>
                
                <!-- Reports -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'reports' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/reports" class="nav-link">
                        <i class="fas fa-file-alt nav-icon"></i>
                        <span class="nav-text">Reports</span>
                    </a>
                </li>
                
                <!-- Notifications -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'notifications' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/notifications" class="nav-link">
                        <i class="fas fa-bell nav-icon"></i>
                        <span class="nav-text">Notifications</span>
                        <span class="nav-badge bg-danger">3</span>
                    </a>
                </li>
                
                <!-- Users -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'users' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/users" class="nav-link">
                        <i class="fas fa-users nav-icon"></i>
                        <span class="nav-text">Users</span>
                    </a>
                </li>
                
                <!-- Settings -->
                <li class="nav-item <?php echo isset($active_page) && $active_page === 'settings' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/settings" class="nav-link">
                        <i class="fas fa-cogs nav-icon"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-status">
                <span class="status-dot online"></span>
                <span class="status-text">System Online</span>
            </div>
            <div class="sidebar-version">
                <span>v<?php echo APP_VERSION; ?></span>
            </div>
        </div>
    </div>
</aside>
<!-- Sidebar End -->