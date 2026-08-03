<?php
/**
 * Recent Activities Page
 * 
 * @var string $title
 * @var array $activities
 * @var array $filters
 */
?>

<?php $page_title = 'Recent Activities'; ?>
<?php $active_page = 'dashboard'; ?>

<div class="activities-container">
    <!-- Filters -->
    <div class="filters-section mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Module</label>
                <select class="form-select" id="filterModule">
                    <option value="">All Modules</option>
                    <option value="compliance">Compliance</option>
                    <option value="risk">Risk</option>
                    <option value="audit">Audit</option>
                    <option value="user">User</option>
                    <option value="system">System</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Action</label>
                <select class="form-select" id="filterAction">
                    <option value="">All Actions</option>
                    <option value="create">Created</option>
                    <option value="update">Updated</option>
                    <option value="delete">Deleted</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                    <option value="export">Export</option>
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
                <label class="form-label">&nbsp;</label>
                <button class="btn btn-primary w-100" id="applyFilters">
                    <i class="fas fa-filter me-2"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>
    
    <!-- Activities Timeline -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-clock me-2"></i> Activity Timeline</span>
            <span class="text-muted small">Showing <?php echo count($activities ?? []); ?> activities</span>
        </div>
        <div class="card-body p-0">
            <div class="activity-timeline">
                <?php if (!empty($activities)): ?>
                    <?php foreach ($activities as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-badge">
                                <div class="timeline-icon <?php echo $activity->type ?? 'info'; ?>">
                                    <i class="fas fa-<?php echo $activity->icon ?? 'circle'; ?>"></i>
                                </div>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="timeline-title">
                                            <?php echo htmlspecialchars($activity->action); ?>
                                            <span class="timeline-module badge bg-secondary ms-2">
                                                <?php echo ucfirst($activity->module); ?>
                                            </span>
                                        </div>
                                        <div class="timeline-description">
                                            <?php echo htmlspecialchars($activity->description); ?>
                                        </div>
                                        <div class="timeline-meta">
                                            <span class="timeline-user">
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($activity->username ?? 'System'); ?>
                                            </span>
                                            <span class="timeline-time">
                                                <i class="far fa-clock me-1"></i>
                                                <?php echo date('d M Y h:i A', strtotime($activity->created_at)); ?>
                                            </span>
                                            <?php if ($activity->ip_address): ?>
                                                <span class="timeline-ip">
                                                    <i class="fas fa-network-wired me-1"></i>
                                                    <?php echo htmlspecialchars($activity->ip_address); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($activity->target_url): ?>
                                        <a href="<?php echo $activity->target_url; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <h5>No Activities Found</h5>
                        <p>No activities match your current filters.</p>
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
                <a class="page-link" href="<?php echo BASE_URL; ?>/activities?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/activities?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/activities?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<style>
.activities-container {
    padding: 0;
}

.filters-section {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.filters-section .form-label {
    font-size: 13px;
    font-weight: 500;
    color: #64748B;
    margin-bottom: 4px;
}

.filters-section .form-select {
    border-radius: 8px;
    border-color: #E2E8F0;
}

.activity-timeline {
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

.timeline-title {
    font-weight: 600;
    color: #1E293B;
    font-size: 15px;
    margin-bottom: 4px;
}

.timeline-module {
    font-size: 11px;
    font-weight: 500;
    padding: 2px 10px;
}

.timeline-description {
    color: #64748B;
    font-size: 14px;
    margin-bottom: 8px;
}

.timeline-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    font-size: 13px;
    color: #94A3B8;
}

.timeline-meta i {
    width: 16px;
    font-size: 12px;
}

.timeline-ip {
    font-family: 'Courier New', monospace;
    font-size: 12px;
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
    .activity-timeline {
        padding: 12px 16px;
    }
    
    .timeline-item {
        flex-direction: column;
        gap: 12px;
    }
    
    .timeline-item::before {
        display: none;
    }
    
    .timeline-meta {
        flex-direction: column;
        gap: 4px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Apply filters
    $('#applyFilters').on('click', function() {
        const module = $('#filterModule').val();
        const action = $('#filterAction').val();
        const user = $('#filterUser').val();
        
        // Build URL with filters
        let url = '<?php echo BASE_URL; ?>/activities?';
        if (module) url += 'module=' + module + '&';
        if (action) url += 'action=' + action + '&';
        if (user) url += 'user=' + user + '&';
        
        window.location.href = url;
    });
    
    // Auto-submit on enter
    $('#filterModule, #filterAction, #filterUser').on('change', function() {
        $('#applyFilters').click();
    });
});
</script>