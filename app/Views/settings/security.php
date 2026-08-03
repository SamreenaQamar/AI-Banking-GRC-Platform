<?php
/**
 * Security Settings Page
 * 
 * @var string $title
 * @var object $user
 * @var array $security_settings
 */
?>

<?php $page_title = 'Security Settings'; ?>
<?php $active_page = 'settings'; ?>

<div class="security-settings-container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5><i class="fas fa-shield-alt me-2 text-primary"></i> Security Settings</h5>
                    <p class="text-muted">Manage your account security and authentication</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/settings" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Settings
                </a>
            </div>
            
            <!-- Change Password -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-key me-2"></i> Change Password
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/security/password">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" required minlength="8">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="new_password_confirmation" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Update Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Two-Factor Authentication -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-shield-alt me-2"></i> Two-Factor Authentication</span>
                    <span class="badge bg-<?php echo ($user->two_factor_enabled ?? false) ? 'success' : 'secondary'; ?>">
                        <?php echo ($user->two_factor_enabled ?? false) ? 'Enabled' : 'Disabled'; ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if ($user->two_factor_enabled ?? false): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Two-factor authentication is enabled for your account.
                        </div>
                        <button class="btn btn-danger" id="disable2faBtn">
                            <i class="fas fa-times me-2"></i> Disable 2FA
                        </button>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Two-factor authentication adds an extra layer of security to your account.
                        </div>
                        <button class="btn btn-primary" id="enable2faBtn">
                            <i class="fas fa-shield-alt me-2"></i> Enable 2FA
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Security Settings -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cog me-2"></i> Security Preferences
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/security/preferences">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="session_timeout" id="sessionTimeout" 
                                           <?php echo ($security_settings['session_timeout'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="sessionTimeout">
                                        Auto-logout after inactivity
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Session Timeout (minutes)</label>
                                    <input type="number" class="form-control" name="timeout_minutes" 
                                           value="<?php echo $security_settings['timeout_minutes'] ?? 30; ?>" min="5" max="120">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="login_alerts" id="loginAlerts" 
                                           <?php echo ($security_settings['login_alerts'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="loginAlerts">
                                        Email alerts for new logins
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="device_management" id="deviceManagement" 
                                           <?php echo ($security_settings['device_management'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="deviceManagement">
                                        Device management and tracking
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Save Preferences
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Active Sessions -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-desktop me-2"></i> Active Sessions</span>
                    <button class="btn btn-sm btn-outline-danger" id="terminateAllSessions">
                        <i class="fas fa-sign-out-alt me-1"></i> Terminate All
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover enterprise-table mb-0">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>IP Address</th>
                                    <th>Location</th>
                                    <th>Last Activity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($active_sessions)): ?>
                                    <?php foreach ($active_sessions as $session): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-<?php echo $session->device_type === 'mobile' ? 'mobile-alt' : ($session->device_type === 'tablet' ? 'tablet-alt' : 'desktop'); ?> me-2"></i>
                                                <?php echo htmlspecialchars($session->device_name ?? 'Unknown Device'); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($session->ip_address); ?></td>
                                            <td><?php echo htmlspecialchars($session->location ?? 'Unknown'); ?></td>
                                            <td><?php echo date('d M Y h:i A', strtotime($session->last_activity)); ?></td>
                                            <td>
                                                <?php if ($session->current): ?>
                                                    <span class="badge bg-success">Current</span>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-danger terminate-session" data-id="<?php echo $session->id; ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fas fa-desktop fa-2x d-block mb-2"></i>
                                            No active sessions
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2FA Setup Modal -->
<div class="modal fade" id="setup2faModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-shield-alt me-2"></i> Setup Two-Factor Authentication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qrCodeContainer" class="text-center">
                    <p class="text-muted">Scan the QR code with your authenticator app</p>
                    <div class="qr-code-placeholder">
                        <i class="fas fa-qrcode fa-5x text-primary"></i>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label class="form-label">Enter Verification Code</label>
                    <input type="text" class="form-control" id="verificationCode" placeholder="6-digit code" maxlength="6">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="verify2faBtn">Verify & Enable</button>
            </div>
        </div>
    </div>
</div>

<style>
.security-settings-container {
    padding: 0;
}

.enterprise-table thead th {
    font-weight: 600;
    font-size: 12px;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    background: #F8FAFC;
}

.enterprise-table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #F1F5F9;
}

.qr-code-placeholder {
    padding: 20px;
    background: #F8FAFC;
    border-radius: 8px;
    margin: 12px 0;
}
</style>

<script>
$(document).ready(function() {
    // Enable 2FA
    $('#enable2faBtn').on('click', function() {
        // Load QR code and show modal
        $('#setup2faModal').modal('show');
    });
    
    // Verify 2FA
    $('#verify2faBtn').on('click', function() {
        const code = $('#verificationCode').val();
        if (code.length !== 6) {
            alert('Please enter a valid 6-digit code.');
            return;
        }
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/settings/security/2fa/enable',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>',
                code: code
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Invalid verification code.');
                }
            }
        });
    });
    
    // Disable 2FA
    $('#disable2faBtn').on('click', function() {
        if (confirm('Disable two-factor authentication?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/settings/security/2fa/disable',
                method: 'POST',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
    
    // Terminate session
    $('.terminate-session').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Terminate this session?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/settings/security/session/' + id + '/terminate',
                method: 'POST',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
    
    // Terminate all sessions
    $('#terminateAllSessions').on('click', function() {
        if (confirm('Terminate all other sessions?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/settings/security/sessions/terminate-all',
                method: 'POST',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
});
</script>