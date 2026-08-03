<?php
/**
 * Settings Dashboard Page
 * 
 * @var string $title
 * @var array $settings_data
 */
?>

<?php $page_title = 'Settings'; ?>
<?php $active_page = 'settings'; ?>

<div class="settings-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h5><i class="fas fa-cogs me-2 text-primary"></i> System Settings</h5>
            <p class="text-muted">Manage system configuration and preferences</p>
        </div>
    </div>
    
    <!-- Settings Cards -->
    <div class="row g-4">
        <!-- Company Settings -->
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/settings/company" class="settings-card">
                <div class="settings-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                    <i class="fas fa-building"></i>
                </div>
                <h6>Company Settings</h6>
                <p class="text-muted small">Manage company profile and branding</p>
                <span class="settings-status">Configured</span>
            </a>
        </div>
        
        <!-- Profile Settings -->
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/settings/profile" class="settings-card">
                <div class="settings-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h6>Profile Settings</h6>
                <p class="text-muted small">Update your personal information</p>
                <span class="settings-status">Personal</span>
            </a>
        </div>
        
        <!-- Security Settings -->
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/settings/security" class="settings-card">
                <div class="settings-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h6>Security Settings</h6>
                <p class="text-muted small">Password, 2FA, and security policies</p>
                <span class="settings-status">Protected</span>
            </a>
        </div>
        
        <!-- API Settings -->
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/settings/api" class="settings-card">
                <div class="settings-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                    <i class="fas fa-code"></i>
                </div>
                <h6>API Settings</h6>
                <p class="text-muted small">API keys and integration settings</p>
                <span class="settings-status">Configured</span>
            </a>
        </div>
        
        <!-- Backup Settings -->
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/settings/backup" class="settings-card">
                <div class="settings-icon" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                    <i class="fas fa-database"></i>
                </div>
                <h6>Backup Settings</h6>
                <p class="text-muted small">Backup and restore configuration</p>
                <span class="settings-status">Active</span>
            </a>
        </div>
        
        <!-- Notification Settings -->
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/settings/notifications" class="settings-card">
                <div class="settings-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                    <i class="fas fa-bell"></i>
                </div>
                <h6>Notification Settings</h6>
                <p class="text-muted small">Configure notification preferences</p>
                <span class="settings-status">Configured</span>
            </a>
        </div>
        
        <!-- Email Settings -->
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/settings/email" class="settings-card">
                <div class="settings-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                    <i class="fas fa-envelope"></i>
                </div>
                <h6>Email Settings</h6>
                <p class="text-muted small">SMTP and email configuration</p>
                <span class="settings-status">Configured</span>
            </a>
        </div>
        
        <!-- System Status -->
        <div class="col-xl-3 col-lg-6">
            <div class="settings-card system-status">
                <div class="settings-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h6>System Status</h6>
                <p class="text-muted small">System health and performance</p>
                <span class="settings-status online"><i class="fas fa-circle me-1"></i> Online</span>
            </div>
        </div>
    </div>
    
    <!-- System Info -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-2"></i> System Information
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="info-item">
                        <label>Application Name</label>
                        <div class="info-value"><?php echo APP_NAME; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-item">
                        <label>Version</label>
                        <div class="info-value"><?php echo APP_VERSION; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-item">
                        <label>Environment</label>
                        <div class="info-value">
                            <span class="badge bg-<?php echo APP_ENV === 'production' ? 'success' : (APP_ENV === 'staging' ? 'warning' : 'secondary'); ?>">
                                <?php echo ucfirst(APP_ENV); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-item">
                        <label>Timezone</label>
                        <div class="info-value"><?php echo APP_TIMEZONE; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-item">
                        <label>PHP Version</label>
                        <div class="info-value"><?php echo phpversion(); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-item">
                        <label>Server</label>
                        <div class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-item">
                        <label>Database</label>
                        <div class="info-value">MySQL <?php echo defined('DB_VERSION') ? DB_VERSION : 'N/A'; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-item">
                        <label>Memory Usage</label>
                        <div class="info-value"><?php echo formatMemory(memory_get_usage()); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-container {
    padding: 0;
}

.settings-card {
    background: #fff;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    text-align: center;
    transition: all 0.3s;
    text-decoration: none;
    display: block;
    height: 100%;
    border: 1px solid transparent;
}

.settings-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-4px);
    border-color: #2563EB;
    text-decoration: none;
}

.settings-card.system-status {
    cursor: default;
}

.settings-card.system-status:hover {
    transform: none;
    border-color: transparent;
}

.settings-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin: 0 auto 12px;
}

.settings-card h6 {
    color: #1E293B;
    margin: 0 0 4px;
    font-weight: 600;
}

.settings-card p {
    margin: 0 0 12px;
    font-size: 13px;
}

.settings-status {
    font-size: 12px;
    padding: 2px 12px;
    border-radius: 12px;
    background: #F1F5F9;
    color: #64748B;
}

.settings-status.online {
    background: #D1FAE5;
    color: #10B981;
}

.info-item {
    padding: 8px 0;
}

.info-item label {
    display: block;
    font-size: 12px;
    color: #94A3B8;
    font-weight: 500;
    margin-bottom: 2px;
}

.info-item .info-value {
    font-size: 14px;
    color: #1E293B;
}

@media (max-width: 768px) {
    .settings-card {
        padding: 16px;
    }
}
</style>

<?php
/**
 * Helper function to format memory
 */
function formatMemory($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>