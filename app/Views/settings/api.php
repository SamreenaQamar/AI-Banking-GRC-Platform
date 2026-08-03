<?php
/**
 * API Settings Page
 * 
 * @var string $title
 * @var array $api_keys
 * @var array $integrations
 */
?>

<?php $page_title = 'API Settings'; ?>
<?php $active_page = 'settings'; ?>

<div class="api-settings-container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5><i class="fas fa-code me-2 text-primary"></i> API Settings</h5>
                    <p class="text-muted">Manage API keys and integration settings</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/settings" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Settings
                </a>
            </div>
            
            <!-- API Keys -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-key me-2"></i> API Keys</span>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateKeyModal">
                        <i class="fas fa-plus me-1"></i> Generate Key
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover enterprise-table mb-0">
                            <thead>
                                <tr>
                                    <th>Key Name</th>
                                    <th>API Key</th>
                                    <th>Permissions</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($api_keys)): ?>
                                    <?php foreach ($api_keys as $key): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($key->name); ?></td>
                                            <td>
                                                <code class="api-key"><?php echo substr($key->key, 0, 12) . '...' . substr($key->key, -4); ?></code>
                                                <button class="btn btn-sm btn-link copy-key" data-key="<?php echo $key->key; ?>">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <?php if (!empty($key->permissions)): ?>
                                                    <?php foreach (explode(',', $key->permissions) as $perm): ?>
                                                        <span class="permission-tag"><?php echo ucfirst($perm); ?></span>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No permissions</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $key->status; ?>">
                                                    <?php echo ucfirst($key->status); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($key->created_at)); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-secondary revoke-key" data-id="<?php echo $key->id; ?>" title="Revoke">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger delete-key" data-id="<?php echo $key->id; ?>" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-key fa-2x d-block mb-2"></i>
                                            No API keys generated
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Integrations -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plug me-2"></i> Integrations
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- SBP Integration -->
                        <div class="col-md-4">
                            <div class="integration-card <?php echo ($integrations['sbp'] ?? false) ? 'active' : ''; ?>">
                                <div class="integration-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                                    <i class="fas fa-university"></i>
                                </div>
                                <h6>SBP Integration</h6>
                                <p class="text-muted small">Connect to SBP APIs for circulars</p>
                                <div class="integration-status">
                                    <span class="badge bg-<?php echo ($integrations['sbp'] ?? false) ? 'success' : 'secondary'; ?>">
                                        <?php echo ($integrations['sbp'] ?? false) ? 'Connected' : 'Disconnected'; ?>
                                    </span>
                                </div>
                                <button class="btn btn-sm <?php echo ($integrations['sbp'] ?? false) ? 'btn-outline-danger' : 'btn-outline-primary'; ?> configure-integration" data-type="sbp">
                                    <?php echo ($integrations['sbp'] ?? false) ? 'Disconnect' : 'Configure'; ?>
                                </button>
                            </div>
                        </div>
                        
                        <!-- AI Integration -->
                        <div class="col-md-4">
                            <div class="integration-card <?php echo ($integrations['ai'] ?? false) ? 'active' : ''; ?>">
                                <div class="integration-icon" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <h6>AI Integration</h6>
                                <p class="text-muted small">OpenAI and language models</p>
                                <div class="integration-status">
                                    <span class="badge bg-<?php echo ($integrations['ai'] ?? false) ? 'success' : 'secondary'; ?>">
                                        <?php echo ($integrations['ai'] ?? false) ? 'Connected' : 'Disconnected'; ?>
                                    </span>
                                </div>
                                <button class="btn btn-sm <?php echo ($integrations['ai'] ?? false) ? 'btn-outline-danger' : 'btn-outline-primary'; ?> configure-integration" data-type="ai">
                                    <?php echo ($integrations['ai'] ?? false) ? 'Disconnect' : 'Configure'; ?>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Email Integration -->
                        <div class="col-md-4">
                            <div class="integration-card <?php echo ($integrations['email'] ?? false) ? 'active' : ''; ?>">
                                <div class="integration-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h6>Email Integration</h6>
                                <p class="text-muted small">SMTP and email services</p>
                                <div class="integration-status">
                                    <span class="badge bg-<?php echo ($integrations['email'] ?? false) ? 'success' : 'secondary'; ?>">
                                        <?php echo ($integrations['email'] ?? false) ? 'Connected' : 'Disconnected'; ?>
                                    </span>
                                </div>
                                <button class="btn btn-sm <?php echo ($integrations['email'] ?? false) ? 'btn-outline-danger' : 'btn-outline-primary'; ?> configure-integration" data-type="email">
                                    <?php echo ($integrations['email'] ?? false) ? 'Disconnect' : 'Configure'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Key Modal -->
<div class="modal fade" id="generateKeyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i> Generate API Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/settings/api/generate">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Key Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g., Production API Key" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="permissions-select">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="read" checked>
                                <label class="form-check-label">Read</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="write">
                                <label class="form-check-label">Write</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="delete">
                                <label class="form-check-label">Delete</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="admin">
                                <label class="form-check-label">Admin</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Expires</label>
                        <select class="form-select" name="expires">
                            <option value="never">Never</option>
                            <option value="30">30 days</option>
                            <option value="90">90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Key</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.api-settings-container {
    padding: 0;
}

.api-key {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    background: #F1F5F9;
    padding: 2px 8px;
    border-radius: 4px;
}

.permission-tag {
    padding: 1px 8px;
    border-radius: 8px;
    background: #DBEAFE;
    color: #2563EB;
    font-size: 11px;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.active { background: #D1FAE5; color: #10B981; }
.status-badge.revoked { background: #FEE2E2; color: #DC2626; }
.status-badge.expired { background: #FEF3C7; color: #F59E0B; }

.integration-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #E2E8F0;
    text-align: center;
    transition: all 0.3s;
    height: 100%;
}

.integration-card:hover {
    border-color: #2563EB;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.integration-card.active {
    border-color: #22C55E;
    background: #F0FDF4;
}

.integration-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 20px;
}

.integration-card h6 {
    margin: 0 0 4px;
}

.integration-card p {
    margin: 0 0 12px;
}

.integration-status {
    margin-bottom: 12px;
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

.permissions-select {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
</style>

<script>
$(document).ready(function() {
    // Copy API key
    $('.copy-key').on('click', function() {
        const key = $(this).data('key');
        navigator.clipboard.writeText(key).then(function() {
            showToast('API key copied to clipboard!', 'success');
        });
    });
    
    // Revoke key
    $('.revoke-key').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Revoke this API key? This cannot be undone.')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/settings/api/' + id + '/revoke',
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
    
    // Delete key
    $('.delete-key').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Delete this API key?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/settings/api/' + id,
                method: 'DELETE',
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
    
    // Toast notification
    function showToast(message, type) {
        const toast = $(`
            <div class="toast-notification ${type}">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(function() {
            toast.fadeOut(300, function() { $(this).remove(); });
        }, 3000);
    }
    
    // Toast styles
    const toastStyles = `
    <style>
        .toast-notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 12px 24px;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .toast-notification.success { background: #22C55E; }
        .toast-notification.warning { background: #F59E0B; }
        .toast-notification.error { background: #EF4444; }
        .toast-notification i { margin-right: 8px; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
    `;
    $('head').append(toastStyles);
});
</script>