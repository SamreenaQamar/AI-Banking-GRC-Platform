<?php
/**
 * Alerts Page
 * 
 * @var string $title
 * @var array $alerts
 * @var array $stats
 */
?>

<?php $page_title = 'System Alerts'; ?>
<?php $active_page = 'notifications'; ?>

<div class="alerts-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-exclamation-triangle me-2 text-primary"></i> System Alerts</h5>
            <p class="text-muted">Critical system alerts and notifications</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-outline-primary" id="dismissAllAlerts">
                <i class="fas fa-check-double me-2"></i> Dismiss All
            </button>
            <button class="btn btn-outline-secondary" id="refreshAlerts">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['total'] ?? 18; ?></div>
                    <div class="stat-label">Total Alerts</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value text-danger"><?php echo $stats['critical'] ?? 3; ?></div>
                    <div class="stat-label">Critical</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['warning'] ?? 8; ?></div>
                    <div class="stat-label">Warnings</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['resolved'] ?? 7; ?></div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alerts List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> All Alerts</span>
            <div>
                <select class="form-select form-select-sm" id="filterAlertSeverity" style="width: auto; display: inline-block;">
                    <option value="all">All Severity</option>
                    <option value="critical">Critical</option>
                    <option value="warning">Warning</option>
                    <option value="info">Info</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($alerts)): ?>
                <?php foreach ($alerts as $alert): ?>
                    <div class="alert-item <?php echo $alert->severity; ?>" data-severity="<?php echo $alert->severity; ?>">
                        <div class="alert-icon <?php echo $alert->severity; ?>">
                            <i class="fas fa-<?php echo $alert->icon ?? 'bell'; ?>"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-header">
                                <div class="alert-title">
                                    <?php echo htmlspecialchars($alert->title); ?>
                                    <span class="badge bg-<?php echo $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning' : 'info'); ?> ms-2">
                                        <?php echo ucfirst($alert->severity); ?>
                                    </span>
                                </div>
                                <div class="alert-time">
                                    <?php echo time_ago($alert->created_at); ?>
                                </div>
                            </div>
                            <div class="alert-message">
                                <?php echo htmlspecialchars($alert->message); ?>
                            </div>
                            <?php if ($alert->source): ?>
                                <div class="alert-source">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($alert->source); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($alert->action_url): ?>
                                <a href="<?php echo $alert->action_url; ?>" class="alert-action">
                                    <?php echo htmlspecialchars($alert->action_label ?? 'View Details'); ?>
                                    <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php if ($alert->severity !== 'resolved'): ?>
                            <div class="alert-actions">
                                <button class="btn btn-sm btn-outline-success resolve-alert" 
                                        data-id="<?php echo $alert->id; ?>">
                                    <i class="fas fa-check me-1"></i> Resolve
                                </button>
                                <button class="btn btn-sm btn-outline-secondary dismiss-alert" 
                                        data-id="<?php echo $alert->id; ?>">
                                    <i class="fas fa-times me-1"></i> Dismiss
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="alert-actions">
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Resolved</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>All Clear!</h5>
                    <p>No active alerts at this time</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.alerts-container {
    padding: 0;
}

.stat-card {
    background: #fff;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    gap: 16px;
    height: 100%;
    transition: all 0.3s;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #1E293B;
}

.stat-value.text-danger { color: #EF4444; }

.stat-label {
    font-size: 14px;
    color: #64748B;
}

.alert-item {
    display: flex;
    align-items: flex-start;
    padding: 16px 20px;
    border-bottom: 1px solid #F1F5F9;
    transition: background 0.2s;
    position: relative;
}

.alert-item:hover {
    background: #F8FAFC;
}

.alert-item.critical {
    border-left: 4px solid #DC2626;
}

.alert-item.warning {
    border-left: 4px solid #F59E0B;
}

.alert-item.info {
    border-left: 4px solid #3B82F6;
}

.alert-item.resolved {
    border-left: 4px solid #22C55E;
    background: #F0FDF4;
}

.alert-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 14px;
    flex-shrink: 0;
    font-size: 18px;
}

.alert-icon.critical { background: #FEE2E2; color: #DC2626; }
.alert-icon.warning { background: #FEF3C7; color: #F59E0B; }
.alert-icon.info { background: #DBEAFE; color: #3B82F6; }
.alert-icon.resolved { background: #D1FAE5; color: #10B981; }

.alert-content {
    flex: 1;
}

.alert-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 4px;
}

.alert-title {
    font-weight: 600;
    color: #1E293B;
    font-size: 15px;
}

.alert-time {
    font-size: 13px;
    color: #94A3B8;
}

.alert-message {
    color: #64748B;
    font-size: 14px;
    margin-bottom: 4px;
}

.alert-source {
    font-size: 12px;
    color: #94A3B8;
}

.alert-action {
    display: inline-block;
    margin-top: 4px;
    color: #2563EB;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.alert-action:hover {
    text-decoration: underline;
}

.alert-actions {
    display: flex;
    gap: 4px;
    margin-left: 12px;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .alert-item {
        flex-wrap: wrap;
    }
    
    .alert-actions {
        margin-left: 0;
        width: 100%;
        margin-top: 8px;
    }
    
    .alert-header {
        flex-direction: column;
    }
}
</style>

<script>
$(document).ready(function() {
    // Filter
    $('#filterAlertSeverity').on('change', function() {
        const severity = $(this).val();
        $('.alert-item').each(function() {
            if (severity === 'all' || $(this).data('severity') === severity) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Resolve alert
    $('.resolve-alert').on('click', function() {
        const id = $(this).data('id');
        const item = $(this).closest('.alert-item');
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/alerts/' + id + '/resolve',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>'
            },
            success: function(response) {
                if (response.success) {
                    item.removeClass('critical warning info');
                    item.addClass('resolved');
                    item.data('severity', 'resolved');
                    item.find('.alert-icon').removeClass('critical warning info').addClass('resolved');
                    item.find('.alert-icon i').removeClass().addClass('fas fa-check-circle');
                    item.find('.badge').removeClass('bg-danger bg-warning bg-info').addClass('bg-success').text('Resolved');
                    item.find('.alert-actions').html('<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Resolved</span>');
                    updateStats();
                }
            }
        });
    });
    
    // Dismiss alert
    $('.dismiss-alert').on('click', function() {
        const id = $(this).data('id');
        const item = $(this).closest('.alert-item');
        
        if (confirm('Dismiss this alert?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/alerts/' + id,
                method: 'DELETE',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        item.fadeOut(300, function() {
                            $(this).remove();
                            updateStats();
                        });
                    }
                }
            });
        }
    });
    
    // Dismiss all
    $('#dismissAllAlerts').on('click', function() {
        if (!confirm('Dismiss all alerts?')) return;
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/alerts/dismiss-all',
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
    });
    
    function updateStats() {
        const total = $('.alert-item').length;
        const critical = $('.alert-item.critical').length;
        const warning = $('.alert-item.warning').length;
        const resolved = $('.alert-item.resolved').length;
        
        $('.stat-value:first').text(total);
        $('.stat-value.text-danger').text(critical);
        $('.stat-value:eq(2)').text(warning);
        $('.stat-value:eq(3)').text(resolved);
    }
});

// Helper function for time ago
function time_ago($timestamp) {
    // This would be implemented in PHP or JavaScript
    return 'Just now';
}
</script>