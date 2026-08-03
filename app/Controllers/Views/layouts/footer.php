<!-- Footer Start -->
<footer class="app-footer">
    <div class="footer-content">
        <div class="footer-left">
            <span class="footer-copyright">
                &copy; <?php echo date('Y'); ?> 
                <strong><?php echo COMPANY_NAME; ?></strong>. 
                All rights reserved.
            </span>
            <span class="footer-divider">|</span>
            <span class="footer-version">
                Version <?php echo APP_VERSION; ?>
            </span>
        </div>
        
        <div class="footer-center d-none d-md-block">
            <span class="footer-status">
                <span class="status-indicator"></span>
                System Status: <strong>Operational</strong>
            </span>
        </div>
        
        <div class="footer-right">
            <a href="<?php echo BASE_URL; ?>/docs" class="footer-link">
                <i class="fas fa-book"></i> Documentation
            </a>
            <span class="footer-divider">|</span>
            <a href="<?php echo BASE_URL; ?>/support" class="footer-link">
                <i class="fas fa-headset"></i> Support
            </a>
            <span class="footer-divider">|</span>
            <a href="<?php echo BASE_URL; ?>/privacy" class="footer-link">
                Privacy
            </a>
        </div>
    </div>
    
    <!-- Footer Stats -->
    <div class="footer-stats">
        <div class="stat-item">
            <i class="fas fa-database"></i>
            <span class="stat-value">1,234</span>
            <span class="stat-label">Records</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-users"></i>
            <span class="stat-value">42</span>
            <span class="stat-label">Active Users</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-clock"></i>
            <span class="stat-value">99.9%</span>
            <span class="stat-label">Uptime</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-robot"></i>
            <span class="stat-value">156</span>
            <span class="stat-label">AI Queries</span>
        </div>
    </div>
</footer>
<!-- Footer End -->