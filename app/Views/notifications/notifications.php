<?php
/**
 * Notifications Center Page
 * 
 * @var string $title
 * @var array $notifications
 * @var int $unread_count
 * @var array $filters
 */
?>

<?php $page_title = 'Notifications'; ?>
<?php $active_page = 'notifications'; ?>

<div class="notifications-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-bell me-2 text-primary"></i> Notifications Center</h5>
            <p class="text-muted">View and manage all your notifications</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary mark-all-read-btn">
                <i class="fas fa-check-double me-2"></i> Mark All Read
            </button>
            <button class="btn btn-outline-secondary" id="refreshNotifications">
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
                    <div class="stat-value"><?php echo $stats['total'] ?? 48; ?></div>
                    <div class="stat-label">Total Notifications</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                    <i class="fas fa-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value text-danger"><?php echo $unread_count ?? 12; ?></div>
                    <div class="stat-label">Unread</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['read'] ?? 36; ?></div>
                    <div class="stat-label">Read</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['today'] ?? 8; ?></div>
                    <div class="stat-label">Today</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <select class="form-select" id="filterType">
                    <option value="">All Types</option>
                    <option value="compliance">Compliance</option>
                    <option value="risk">Risk</option>
                    <option value="audit">Audit</option>
                    <option value="policy">Policy</option>
                    <option value="system">System</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterPriority">
                    <option value="">All Priority</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="searchNotification" placeholder="Search notifications...">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notifications List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> All Notifications</span>
            <span class="text-muted small"><?php echo count($notifications ?? []); ?> notifications</span>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo !$notification->is_read ? 'unread' : ''; ?>" 
                         data-type="<?php echo $notification->type; ?>"
                         data-priority="<?php echo $notification->priority; ?>">
                        <div class="notification-check">
                            <input type="checkbox" class="form-check-input notification-select" 
                                   data-id="<?php echo $notification->id; ?>">
                        </div>
                        <div class="notification-icon <?php echo $notification->type; ?>">
                            <i class="fas fa-<?php echo $notification->icon ?? 'bell'; ?>"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-header">
                                <div class="notification-title">
                                    <?php echo htmlspecialchars($notification->title); ?>
                                    <?php if (!$notification->is_read): ?>
                                        <span class="badge bg-primary ms-2">New</span>
                                    <?php endif; ?>
                                    <?php if ($notification->priority === 'high'): ?>
                                        <span class="badge bg-danger ms-1">High Priority</span>
                                    <?php endif; ?>
                                </div>
                                <div class="notification-actions">
                                    <?php if (!$notification->is_read): ?>
                                        <button class="btn btn-sm btn-link mark-read-btn" 
                                                data-id="<?php echo $notification->id; ?>">
                                            Mark as read
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-link text-danger delete-notification" 
                                            data-id="<?php echo $notification->id; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="notification-message">
                                <?php echo htmlspecialchars($notification->message); ?>
                            </div>
                            <div class="notification-meta">
                                <span class="notification-time">
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo time_ago($notification->created_at); ?>
                                </span>
                                <span class="notification-type">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo ucfirst($notification->type); ?>
                                </span>
                                <?php if ($notification->action_url): ?>
                                    <a href="<?php echo $notification->action_url; ?>" class="notification-action">
                                        <?php echo htmlspecialchars($notification->action_label ?? 'View Details'); ?>
                                        <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!$notification->is_read): ?>
                            <div class="notification-unread-dot"></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5>No Notifications</h5>
                    <p class="text-muted">You're all caught up! No new notifications.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bulk Actions -->
    <?php if (!empty($notifications)): ?>
    <div class="bulk-actions mt-3">
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                <i class="fas fa-check-square me-1"></i> Select All
            </button>
            <button class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                <i class="fas fa-square me-1"></i> Deselect All
            </button>
            <button class="btn btn-sm btn-outline-success" id="markSelectedRead">
                <i class="fas fa-check me-1"></i> Mark Selected Read
            </button>
            <button class="btn btn-sm btn-outline-danger" id="deleteSelected">
                <i class="fas fa-trash me-1"></i> Delete Selected
            </button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Pagination -->
    <?php if (isset($total_pages) && $total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/notifications?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/notifications?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/notifications?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<style>
.notifications-container {
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

.filter-section {
    background: #fff;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
}

.search-box .form-control {
    padding-left: 40px;
    border-radius: 8px;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 16px 20px;
    border-bottom: 1px solid #F1F5F9;
    transition: background 0.2s;
    position: relative;
}

.notification-item:hover {
    background: #F8FAFC;
}

.notification-item.unread {
    background: #F0F7FF;
}

.notification-item.unread:hover {
    background: #E8F0FE;
}

.notification-check {
    margin-right: 12px;
    padding-top: 4px;
}

.notification-check .form-check-input {
    cursor: pointer;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 14px;
    flex-shrink: 0;
    font-size: 16px;
}

.notification-icon.compliance { background: #DBEAFE; color: #3B82F6; }
.notification-icon.risk { background: #FEF3C7; color: #F59E0B; }
.notification-icon.audit { background: #D1FAE5; color: #10B981; }
.notification-icon.policy { background: #FCE7F3; color: #EC4899; }
.notification-icon.system { background: #F1F5F9; color: #64748B; }
.notification-icon.success { background: #D1FAE5; color: #22C55E; }
.notification-icon.warning { background: #FEF3C7; color: #F59E0B; }
.notification-icon.error { background: #FEE2E2; color: #EF4444; }
.notification-icon.info { background: #DBEAFE; color: #3B82F6; }

.notification-content {
    flex: 1;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 4px;
}

.notification-title {
    font-weight: 600;
    color: #1E293B;
    font-size: 15px;
}

.notification-actions {
    display: flex;
    gap: 8px;
}

.mark-read-btn {
    font-size: 13px;
    color: #94A3B8;
    text-decoration: none;
    padding: 0;
}

.mark-read-btn:hover {
    color: #2563EB;
}

.delete-notification {
    padding: 0;
    line-height: 1;
}

.notification-message {
    color: #64748B;
    font-size: 14px;
    margin-bottom: 6px;
}

.notification-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 13px;
    color: #94A3B8;
}

.notification-meta i {
    width: 14px;
}

.notification-action {
    color: #2563EB;
    text-decoration: none;
    font-weight: 500;
}

.notification-action:hover {
    text-decoration: underline;
}

.notification-unread-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #2563EB;
    margin-left: 12px;
    flex-shrink: 0;
    margin-top: 8px;
}

.bulk-actions {
    padding: 12px 0;
}

.empty-state i {
    display: block;
}

@media (max-width: 768px) {
    .notification-item {
        flex-wrap: wrap;
    }
    
    .notification-check {
        width: 100%;
        margin-bottom: 8px;
    }
    
    .notification-header {
        flex-direction: column;
    }
    
    .notification-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .notification-meta {
        flex-direction: column;
        gap: 4px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Filter functionality
    $('#filterType, #filterStatus, #filterPriority').on('change', applyFilters);
    $('#searchNotification').on('keyup', applyFilters);
    
    function applyFilters() {
        const type = $('#filterType').val();
        const status = $('#filterStatus').val();
        const priority = $('#filterPriority').val();
        const search = $('#searchNotification').val().toLowerCase();
        
        $('.notification-item').each(function() {
            const item = $(this);
            let show = true;
            
            if (type && item.data('type') !== type) show = false;
            if (status === 'unread' && !item.hasClass('unread')) show = false;
            if (status === 'read' && item.hasClass('unread')) show = false;
            if (priority && item.data('priority') !== priority) show = false;
            if (search) {
                const text = item.text().toLowerCase();
                if (!text.includes(search)) show = false;
            }
            
            item.toggle(show);
        });
    }
    
    // Mark as read
    $('.mark-read-btn').on('click', function() {
        const id = $(this).data('id');
        const item = $(this).closest('.notification-item');
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/notifications/' + id + '/read',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>'
            },
            success: function(response) {
                if (response.success) {
                    item.removeClass('unread');
                    item.find('.notification-unread-dot').remove();
                    item.find('.badge.bg-primary').remove();
                    $(this).remove();
                    
                    // Update stats
                    updateStats();
                }
            }.bind(this)
        });
    });
    
    // Mark all as read
    $('.mark-all-read-btn').on('click', function() {
        if (!confirm('Mark all notifications as read?')) return;
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/notifications/read-all',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('.notification-item').removeClass('unread');
                    $('.notification-unread-dot').remove();
                    $('.badge.bg-primary').remove();
                    $('.mark-read-btn').remove();
                    updateStats();
                }
            }
        });
    });
    
    // Delete notification
    $('.delete-notification').on('click', function() {
        const id = $(this).data('id');
        const item = $(this).closest('.notification-item');
        
        if (confirm('Delete this notification?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/notifications/' + id,
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
    
    // Select all
    $('#selectAllBtn').on('click', function() {
        $('.notification-select').prop('checked', true);
    });
    
    $('#deselectAllBtn').on('click', function() {
        $('.notification-select').prop('checked', false);
    });
    
    // Mark selected read
    $('#markSelectedRead').on('click', function() {
        const selected = $('.notification-select:checked');
        if (selected.length === 0) {
            alert('Please select at least one notification.');
            return;
        }
        
        const ids = [];
        selected.each(function() {
            ids.push($(this).data('id'));
        });
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/notifications/mark-read-bulk',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>',
                ids: ids
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
    
    // Delete selected
    $('#deleteSelected').on('click', function() {
        const selected = $('.notification-select:checked');
        if (selected.length === 0) {
            alert('Please select at least one notification.');
            return;
        }
        
        if (!confirm('Delete selected notifications?')) return;
        
        const ids = [];
        selected.each(function() {
            ids.push($(this).data('id'));
        });
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/notifications/delete-bulk',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>',
                ids: ids
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
    
    // Update stats
    function updateStats() {
        const unread = $('.notification-item.unread').length;
        const total = $('.notification-item').length;
        
        $('.stat-value.text-danger').text(unread);
        $('.stat-value:first').text(total);
        $('.stat-value:eq(2)').text(total - unread);
        
        // Update badge in topbar
        $('#notificationBadge').text(unread).toggle(unread > 0);
    }
});

// Helper function for time ago
function time_ago($timestamp) {
    // This would be implemented in PHP or JavaScript
    return 'Just now';
}
</script>