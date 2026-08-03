<?php
/**
 * Audit History Page
 * 
 * @var string $title
 * @var array $history_items
 * @var array $filters
 */
?>

<?php $page_title = 'Audit History'; ?>
<?php $active_page = 'audit'; ?>

<div class="audit-history-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-history me-2 text-primary"></i> Audit History</h5>
            <p class="text-muted">Complete audit trail of all activities and changes</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-outline-primary" id="exportHistory">
                <i class="fas fa-download me-2"></i> Export History
            </button>
            <button class="btn btn-outline-secondary" id="refreshHistory">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Module</label>
                <select class="form-select" id="filterModule">
                    <option value="">All Modules</option>
                    <option value="audit">Audit</option>
                    <option value="finding">Finding</option>
                    <option value="evidence">Evidence</option>
                    <option value="report">Report</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Action</label>
                <select class="form-select" id="filterAction">
                    <option value="">All Actions</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                    <option value="completed">Completed</option>
                    <option value="started">Started</option>
                    <option value="assigned">Assigned</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select class="form-select" id="filterUser">
                    <option value="">All Users</option>
                    <?php foreach ($users ?? [] as $user): ?>
                        <option value="<?php echo $user->id; ?>"><?php echo htmlspecialchars($user->full_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date Range</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="dateFrom">
                    <span class="text-muted">to</span>
                    <input type="date" class="form-control" id="dateTo">
                </div>
            </div>
        </div>
    </div>
    
    <!-- History Timeline -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-clock me-2"></i> Activity Timeline</span>
            <span class="text-muted small"><?php echo count($history_items ?? []); ?> records</span>
        </div>
        <div class="card-body p-0">
            <div class="history-timeline">
                <?php if (!empty($history_items)): ?>
                    <?php foreach ($history_items as $item): ?>
                        <div class="timeline-item" data-module="<?php echo $item->module; ?>" data-action="<?php echo $item->action; ?>">
                            <div class="timeline-badge">
                                <div class="timeline-icon <?php echo $item->action_type ?? 'info'; ?>">
                                    <i class="fas fa-<?php echo $item->icon ?? 'circle'; ?>"></i>
                                </div>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <div class="timeline-title">
                                        <?php echo htmlspecialchars($item->title); ?>
                                        <span class="badge bg-secondary ms-2"><?php echo ucfirst($item->module); ?></span>
                                    </div>
                                    <div class="timeline-user">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo htmlspecialchars($item->user_name ?? 'System'); ?>
                                    </div>
                                </div>
                                <div class="timeline-description">
                                    <?php echo nl2br(htmlspecialchars($item->description)); ?>
                                </div>
                                <?php if ($item->changes): ?>
                                    <div class="timeline-changes">
                                        <small class="text-muted">Changes:</small>
                                        <div class="changes-list">
                                            <?php foreach ($item->changes as $field => $change): ?>
                                                <span class="change-item">
                                                    <span class="change-field"><?php echo ucfirst($field); ?>:</span>
                                                    <span class="change-old"><?php echo htmlspecialchars($change['old'] ?? 'N/A'); ?></span>
                                                    <i class="fas fa-arrow-right text-muted mx-1"></i>
                                                    <span class="change-new"><?php echo htmlspecialchars($change['new'] ?? 'N/A'); ?></span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="timeline-meta">
                                    <span class="timeline-time">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo date('d M Y h:i A', strtotime($item->created_at)); ?>
                                    </span>
                                    <?php if ($item->ip_address): ?>
                                        <span class="timeline-ip">
                                            <i class="fas fa-network-wired me-1"></i>
                                            <?php echo htmlspecialchars($item->ip_address); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($item->user_agent): ?>
                                        <span class="timeline-user-agent">
                                            <i class="fas fa-desktop me-1"></i>
                                            <?php echo htmlspecialchars(substr($item->user_agent, 0, 50)) . '...'; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <h5>No History Found</h5>
                        <p>No audit history records available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($total_pages) && $total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/audit/history?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/audit/history?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/audit/history?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<style>
.audit-history-container {
    padding: 0;
}

.filter-section {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.filter-section .form-label {
    font-size: 13px;
    font-weight: 500;
    color: #64748B;
    margin-bottom: 4px;
}

.filter-section .form-select,
.filter-section .form-control {
    border-radius: 8px;
    border-color: #E2E8F0;
}

.history-timeline {
    padding: 20px 24px;
}

.timeline-item {
    display: flex;
    gap: 20px;
    padding: 16px 0;
    border-bottom: 1px solid #F1F5F9;
    position: relative;
}

.timeline-item:last-child {
    border-bottom: none;
}

.timeline-badge {
    flex-shrink: 0;
    padding-top: 4px;
}

.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.timeline-icon.success { background: #D1FAE5; color: #10B981; }
.timeline-icon.warning { background: #FEF3C7; color: #F59E0B; }
.timeline-icon.danger { background: #FEE2E2; color: #EF4444; }
.timeline-icon.info { background: #DBEAFE; color: #3B82F6; }
.timeline-icon.primary { background: #DBEAFE; color: #2563EB; }

.timeline-content {
    flex: 1;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 4px;
}

.timeline-title {
    font-weight: 600;
    color: #1E293B;
    font-size: 15px;
}

.timeline-user {
    font-size: 13px;
    color: #64748B;
}

.timeline-description {
    color: #64748B;
    font-size: 14px;
    margin-bottom: 8px;
}

.timeline-changes {
    background: #F8FAFC;
    padding: 8px 12px;
    border-radius: 6px;
    margin-bottom: 8px;
}

.changes-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
}

.change-item {
    font-size: 13px;
    background: #fff;
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px solid #E2E8F0;
}

.change-field {
    font-weight: 500;
    color: #1E293B;
}

.change-old {
    color: #EF4444;
    text-decoration: line-through;
}

.change-new {
    color: #22C55E;
}

.timeline-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    font-size: 12px;
    color: #94A3B8;
}

.timeline-meta i {
    width: 14px;
}

/* Timeline connector */
.timeline-item::before {
    content: '';
    position: absolute;
    left: 40px;
    top: 60px;
    bottom: 0;
    width: 2px;
    background: #F1F5F9;
}

.timeline-item:last-child::before {
    display: none;
}

@media (max-width: 768px) {
    .history-timeline {
        padding: 12px 16px;
    }
    
    .timeline-item {
        flex-direction: column;
        gap: 12px;
    }
    
    .timeline-item::before {
        display: none;
    }
    
    .timeline-header {
        flex-direction: column;
    }
    
    .changes-list {
        flex-direction: column;
    }
}
</style>

<script>
$(document).ready(function() {
    // Filter functionality
    $('#filterModule, #filterAction, #filterUser, #dateFrom, #dateTo').on('change', applyFilters);
    
    function applyFilters() {
        const module = $('#filterModule').val();
        const action = $('#filterAction').val();
        const user = $('#filterUser').val();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        $('.timeline-item').each(function() {
            const item = $(this);
            let show = true;
            
            if (module && item.data('module') !== module) show = false;
            if (action && item.data('action') !== action) show = false;
            // User and date filters would need server-side processing
            item.toggle(show);
        });
    }
    
    // Export history
    $('#exportHistory').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/audit/history/export';
    });
    
    // Refresh history
    $('#refreshHistory').on('click', function() {
        const btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin"></i>');
        setTimeout(function() {
            location.reload();
        }, 1000);
    });
});
</script>